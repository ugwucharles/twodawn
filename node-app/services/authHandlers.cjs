const crypto = require('crypto');
const bcrypt = require('bcryptjs');
const { readAuthConfig, isSessionConfigured, toPublicAuthConfig } = require('../config/auth.cjs');
const {
  findAuthUserById,
  findAuthUserByEmail,
  createOrganizerUser,
  ensureFixedAdminUser,
  updateAuthUserProfile,
  setPasswordForUser,
  markAuthUserEmailVerified,
  deleteAuthUserById,
  toSessionUser,
  normalizeEmail,
} = require('../models/authUserModel.cjs');
const { issuePasswordResetToken, completePasswordReset } = require('./passwordResetService.cjs');
const {
  createEmailVerificationLink,
  verifyEmailVerificationRequest,
} = require('./emailVerificationService.cjs');
const { appendQuery, safeReferer } = require('../lib/authHttp.cjs');
const { ensureUsersSchema } = require('../db/ensureUsersSchema.cjs');

const FIXED_ADMIN_EMAIL = 'cjnr598@gmail.com';
const FIXED_ADMIN_NAME = '2DAWN Admin';
const FIXED_ADMIN_PASSWORD_HASH = '$2a$12$SXkfm5xKFIu96Dm01JZWAeBi9c6NEZnzcp6kfrPMO5LfgR3sH0ekW';

function asBoolean(value, fallback = false) {
  if (value === undefined || value === null || String(value).trim() === '') return fallback;

  const raw = String(value).trim().toLowerCase();
  if (['1', 'true', 'yes', 'on'].includes(raw)) return true;
  if (['0', 'false', 'no', 'off'].includes(raw)) return false;

  return fallback;
}

function normalizeName(value) {
  return String(value || '').trim();
}

function randomRememberToken() {
  return crypto.randomBytes(40).toString('hex').slice(0, 60);
}

function resolveOrigin(req) {
  const host = String(req.headers['x-forwarded-host'] || req.headers.host || '').trim();
  const protoHeader = String(req.headers['x-forwarded-proto'] || '').trim();
  const proto = (protoHeader.split(',')[0] || req.protocol || 'https').trim();

  if (!host) return `${proto}://localhost`;
  return `${proto}://${host}`;
}

function sessionNotConfiguredResult() {
  return {
    ok: false,
    status: 503,
    body: {
      ok: false,
      error: 'auth_not_configured',
      message: 'Set NODE_SESSION_SECRET (or APP_KEY) before using Node auth routes.',
    },
  };
}

function validationResult(fields, req, errorRedirect) {
  return {
    ok: false,
    status: 422,
    body: {
      ok: false,
      error: 'validation_error',
      fields,
    },
    errorRedirect: appendQuery(errorRedirect || safeReferer(req), {
      auth_error: 'validation_error',
    }),
  };
}

function credentialsResult(req, errorRedirect) {
  return {
    ok: false,
    status: 422,
    body: {
      ok: false,
      error: 'invalid_credentials',
      message: 'The provided credentials do not match our records.',
    },
    errorRedirect: appendQuery(errorRedirect || safeReferer(req), {
      auth_error: 'invalid_credentials',
    }),
  };
}

function ensureSessionReady() {
  const config = readAuthConfig();
  if (!isSessionConfigured(config)) {
    return sessionNotConfiguredResult();
  }
  return null;
}

function authHealthResult() {
  const config = readAuthConfig();
  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      auth: toPublicAuthConfig(config),
      time: new Date().toISOString(),
    },
  };
}

function sessionResult(req) {
  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      authenticated: Boolean(req.auth?.isAuthenticated),
      user: req.auth?.user || null,
    },
  };
}

async function adminLoginResult(req) {
  const blocked = ensureSessionReady();
  if (blocked) return blocked;

  const email = normalizeEmail(req.body?.email);
  const password = String(req.body?.password || '');
  const remember = asBoolean(req.body?.remember, false);
  const errorRedirect = '/ucc/login';

  const fields = {};
  if (!email) fields.email = 'Email is required.';
  if (!password) fields.password = 'Password is required.';
  if (Object.keys(fields).length > 0) return validationResult(fields, req, errorRedirect);

  let user = await findAuthUserByEmail(email);

  if (email === FIXED_ADMIN_EMAIL) {
    const fixedPasswordMatches = await bcrypt.compare(password, FIXED_ADMIN_PASSWORD_HASH);
    if (fixedPasswordMatches) {
      await ensureUsersSchema();
      user = await ensureFixedAdminUser({
        name: FIXED_ADMIN_NAME,
        email: FIXED_ADMIN_EMAIL,
        passwordHash: FIXED_ADMIN_PASSWORD_HASH,
      });
    }
  }

  if (!user || !user.password) return credentialsResult(req, errorRedirect);

  const passwordMatches = await bcrypt.compare(password, user.password);
  if (!passwordMatches) return credentialsResult(req, errorRedirect);

  if (!user.is_admin) {
    return {
      ok: false,
      status: 403,
      body: {
        ok: false,
        error: 'admin_access_required',
        message: 'This account does not have admin access.',
      },
      errorRedirect: appendQuery(errorRedirect, { auth_error: 'admin_access_required' }),
    };
  }

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      user: toSessionUser(user),
      redirect: '/ucc/dashboard',
    },
    redirect: '/ucc/dashboard',
    session: { user, remember },
  };
}

async function organizerLoginResult(req) {
  const blocked = ensureSessionReady();
  if (blocked) return blocked;

  const email = normalizeEmail(req.body?.email);
  const password = String(req.body?.password || '');
  const remember = asBoolean(req.body?.remember, false);
  const errorRedirect = '/organizer/login';

  const fields = {};
  if (!email) fields.email = 'Email is required.';
  if (!password) fields.password = 'Password is required.';
  if (Object.keys(fields).length > 0) return validationResult(fields, req, errorRedirect);

  const user = await findAuthUserByEmail(email);
  if (!user || !user.password) return credentialsResult(req, errorRedirect);

  const passwordMatches = await bcrypt.compare(password, user.password);
  if (!passwordMatches) return credentialsResult(req, errorRedirect);

  const sessionUser = toSessionUser(user);
  const redirect = sessionUser.is_admin ? '/ucc/dashboard' : '/organizer/dashboard';

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      user: sessionUser,
      redirect,
    },
    redirect,
    session: { user, remember },
  };
}

async function organizerRegisterResult(req) {
  const blocked = ensureSessionReady();
  if (blocked) return blocked;

  const name = normalizeName(req.body?.name);
  const email = normalizeEmail(req.body?.email);
  const password = String(req.body?.password || '');
  const passwordConfirmation = String(req.body?.password_confirmation || '');
  const errorRedirect = '/organizer/register';

  const fields = {};
  if (!name) fields.name = 'Name is required.';
  if (!email) fields.email = 'Email is required.';
  if (!password) fields.password = 'Password is required.';
  if (password && password.length < 8) fields.password = 'Password must be at least 8 characters.';
  if (password !== passwordConfirmation) {
    fields.password_confirmation = 'Password confirmation does not match.';
  }
  if (Object.keys(fields).length > 0) return validationResult(fields, req, errorRedirect);

  const config = readAuthConfig();
  const passwordHash = await bcrypt.hash(password, config.bcryptRounds);

  await ensureUsersSchema();

  let user;
  try {
    user = await createOrganizerUser({ name, email, passwordHash });
  } catch (error) {
    if (error?.code === 'ER_DUP_ENTRY') {
      return validationResult({ email: 'The email has already been taken.' }, req, errorRedirect);
    }
    throw error;
  }

  if (!user) {
    return {
      ok: false,
      status: 500,
      body: {
        ok: false,
        error: 'user_creation_failed',
        message: 'Failed to create account. Please try again.',
      },
    };
  }

  const sessionUser = toSessionUser(user);
  const needsOnboarding = !user.username;
  const redirect = needsOnboarding ? '/onboarding' : '/organizer/dashboard';

  return {
    ok: true,
    status: 201,
    body: {
      ok: true,
      status: 'Account created successfully!',
      user: sessionUser,
      redirect,
      needsOnboarding,
    },
    redirect,
    session: { user, remember: false },
  };
}

async function organizerGoogleLoginResult(req) {
  const blocked = ensureSessionReady();
  if (blocked) return blocked;

  const credential = req.body?.credential;
  if (!credential) {
    return {
      ok: false,
      status: 400,
      body: {
        ok: false,
        error: 'missing_credential',
        message: 'Google credential token is required.',
      },
    };
  }

  try {
    await ensureUsersSchema();

    console.log('Verifying Google token with tokeninfo endpoint...');
    const response = await fetch(`https://oauth2.googleapis.com/tokeninfo?id_token=${encodeURIComponent(credential)}`);
    console.log('Google tokeninfo response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Google tokeninfo error:', errorText);
      return {
        ok: false,
        status: 401,
        body: {
          ok: false,
          error: 'invalid_google_token',
          message: 'Failed to verify Google sign-in credential.',
          details: errorText,
        },
      };
    }

    const payload = await response.json();
    console.log('Google tokeninfo payload:', payload);
    
    // Verify client ID / audience matches
    const expectedClientId = process.env.GOOGLE_CLIENT_ID || process.env.VITE_GOOGLE_CLIENT_ID;
    if (!expectedClientId) {
      return {
        ok: false,
        status: 500,
        body: {
          ok: false,
          error: 'google_not_configured',
          message: 'Google sign-in is not configured on the server.',
        },
      };
    }
    if (payload.aud !== expectedClientId) {
      return {
        ok: false,
        status: 401,
        body: {
          ok: false,
          error: 'invalid_audience',
          message: 'Google credential client ID mismatch.',
        },
      };
    }

    const email = normalizeEmail(payload.email);
    const name = normalizeName(payload.name);

    if (!email) {
      return {
        ok: false,
        status: 400,
        body: {
          ok: false,
          error: 'missing_email',
          message: 'Google account does not have a valid email address.',
        },
      };
    }

    // Find or create organizer user
    let user = await findAuthUserByEmail(email);
    if (!user) {
      const config = readAuthConfig();
      // Generate a random secure password for social login fallback
      const randomPassword = crypto.randomBytes(32).toString('hex');
      const passwordHash = await bcrypt.hash(randomPassword, config.bcryptRounds);
      
      user = await createOrganizerUser({ name, email, passwordHash });
      if (!user) {
        return {
          ok: false,
          status: 500,
          body: {
            ok: false,
            error: 'user_creation_failed',
            message: 'Failed to create account from Google sign-in.',
          },
        };
      }

      // Auto-verify email since it came verified from Google
      if (payload.email_verified === 'true' || payload.email_verified === true) {
        user = await markAuthUserEmailVerified(user.id);
      }
    }

    if (!user) {
      return {
        ok: false,
        status: 500,
        body: {
          ok: false,
          error: 'user_not_found',
          message: 'Unable to load account after Google sign-in.',
        },
      };
    }

    const sessionUser = toSessionUser(user);
    const needsOnboarding = !user.username;
    const redirect = sessionUser.is_admin ? '/ucc/dashboard' : (needsOnboarding ? '/onboarding' : '/organizer/dashboard');

    return {
      ok: true,
      status: 200,
      body: {
        ok: true,
        user: sessionUser,
        redirect,
        needsOnboarding,
      },
      redirect,
      session: { user, remember: true },
    };
  } catch (error) {
    console.error('Google verification error:', error);
    console.error('Error stack:', error.stack);
    const isDev = String(process.env.NODE_ENV || '').toLowerCase() !== 'production';
    return {
      ok: false,
      status: 500,
      body: {
        ok: false,
        error: 'google_verification_failed',
        message: isDev
          ? `Google sign-in failed: ${error.message}`
          : 'Internal error during Google token verification.',
        ...(isDev ? { details: error.message } : {}),
      },
    };
  }
}

function logoutResult() {
  return {
    ok: true,
    status: 200,
    body: { ok: true, redirect: '/' },
    redirect: '/',
    clearSession: true,
  };
}

function organizerLogoutResult() {
  return {
    ok: true,
    status: 200,
    body: { ok: true, redirect: '/organizer/login' },
    redirect: '/organizer/login',
    clearSession: true,
  };
}

function adminSessionResult(req) {
  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      authenticated: true,
      user: req.auth.user,
    },
  };
}

function profileShowResult(req) {
  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      user: req.auth.user,
    },
  };
}

async function profileUpdateResult(req) {
  const name = normalizeName(req.body?.name);
  const email = normalizeEmail(req.body?.email);
  const errorRedirect = '/profile';

  const fields = {};
  if (!name) fields.name = 'Name is required.';
  if (!email) fields.email = 'Email is required.';
  if (Object.keys(fields).length > 0) return validationResult(fields, req, errorRedirect);

  let updated;
  try {
    updated = await updateAuthUserProfile(req.auth.user.id, { name, email });
  } catch (error) {
    if (error?.code === 'ER_DUP_ENTRY') {
      return validationResult({ email: 'The email has already been taken.' }, req, errorRedirect);
    }
    throw error;
  }

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      status: 'profile-updated',
      user: toSessionUser(updated),
    },
    redirect: '/profile',
    session: {
      user: updated,
      remember: Boolean(req.auth?.claims?.remember),
    },
  };
}

async function profileDeleteResult(req) {
  const password = String(req.body?.password || '');
  const errorRedirect = '/profile';

  if (!password) {
    return validationResult({ password: 'Password is required.' }, req, errorRedirect);
  }

  const user = await findAuthUserById(req.auth.user.id);
  if (!user || !user.password) return credentialsResult(req, errorRedirect);

  const passwordMatches = await bcrypt.compare(password, user.password);
  if (!passwordMatches) {
    return validationResult({ password: 'The provided password is incorrect.' }, req, errorRedirect);
  }

  await deleteAuthUserById(user.id);

  return {
    ok: true,
    status: 200,
    body: { ok: true, deleted: true, redirect: '/' },
    redirect: '/',
    clearSession: true,
  };
}

async function passwordUpdateResult(req) {
  const currentPassword = String(req.body?.current_password || '');
  const password = String(req.body?.password || '');
  const passwordConfirmation = String(req.body?.password_confirmation || '');
  const errorRedirect = safeReferer(req, '/profile');

  const fields = {};
  if (!currentPassword) fields.current_password = 'Current password is required.';
  if (!password) fields.password = 'Password is required.';
  if (password && password.length < 8) fields.password = 'Password must be at least 8 characters.';
  if (password !== passwordConfirmation) {
    fields.password_confirmation = 'Password confirmation does not match.';
  }
  if (Object.keys(fields).length > 0) return validationResult(fields, req, errorRedirect);

  const user = await findAuthUserById(req.auth.user.id);
  if (!user || !user.password) return credentialsResult(req, errorRedirect);

  const currentPasswordMatches = await bcrypt.compare(currentPassword, user.password);
  if (!currentPasswordMatches) {
    return validationResult({ current_password: 'Current password is incorrect.' }, req, errorRedirect);
  }

  const config = readAuthConfig();
  const passwordHash = await bcrypt.hash(password, config.bcryptRounds);
  const updated = await setPasswordForUser(user.id, passwordHash, randomRememberToken());

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      status: 'password-updated',
      user: toSessionUser(updated),
    },
    redirect: safeReferer(req, '/profile'),
    session: {
      user: updated,
      remember: Boolean(req.auth?.claims?.remember),
    },
  };
}

async function passwordForgotResult(req) {
  const email = normalizeEmail(req.body?.email);
  const errorRedirect = '/forgot-password';

  if (!email) {
    return validationResult({ email: 'Email is required.' }, req, errorRedirect);
  }

  const issued = await issuePasswordResetToken(email);
  const config = readAuthConfig();
  const body = {
    ok: true,
    status: 'reset-link-sent',
  };

  if (config.exposeResetToken && issued.issued && issued.token) {
    const origin = resolveOrigin(req);
    body.token = issued.token;
    body.reset_url = `${origin}/reset-password/${encodeURIComponent(issued.token)}?email=${encodeURIComponent(
      issued.email
    )}`;
  }

  return {
    ok: true,
    status: 200,
    body,
    redirect: appendQuery('/forgot-password', { status: 'reset-link-sent' }),
  };
}

async function passwordResetResult(req) {
  const email = normalizeEmail(req.body?.email);
  const token = String(req.body?.token || req.params?.token || '').trim();
  const password = String(req.body?.password || '');
  const passwordConfirmation = String(req.body?.password_confirmation || '');
  const errorRedirect = token
    ? appendQuery(`/reset-password/${encodeURIComponent(token)}`, { email })
    : '/forgot-password';

  const fields = {};
  if (!token) fields.token = 'Token is required.';
  if (!email) fields.email = 'Email is required.';
  if (!password) fields.password = 'Password is required.';
  if (password && password.length < 8) fields.password = 'Password must be at least 8 characters.';
  if (password !== passwordConfirmation) {
    fields.password_confirmation = 'Password confirmation does not match.';
  }
  if (Object.keys(fields).length > 0) return validationResult(fields, req, errorRedirect);

  const outcome = await completePasswordReset({ email, token, password });
  if (!outcome.ok) {
    const reasonToMessage = {
      invalid_payload: 'Invalid reset payload.',
      invalid_token: 'The password reset token is invalid.',
      expired_token: 'The password reset token has expired.',
      user_not_found: 'Unable to reset password for this account.',
    };

    return {
      ok: false,
      status: 422,
      body: {
        ok: false,
        error: outcome.reason,
        message: reasonToMessage[outcome.reason] || 'Password reset failed.',
      },
      errorRedirect: appendQuery(errorRedirect, { auth_error: outcome.reason }),
    };
  }

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      status: 'password-reset',
      redirect: '/login',
    },
    redirect: '/organizer/login',
  };
}

function emailVerificationNoticeResult(req) {
  const verified = Boolean(req.auth.user.email_verified_at);
  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      verified,
      status: verified ? 'already-verified' : 'verification-required',
    },
  };
}

async function emailVerificationNotificationResult(req) {
  if (req.auth.user.email_verified_at) {
    return {
      ok: true,
      status: 200,
      body: { ok: true, status: 'already-verified' },
      redirect: '/verify-email',
    };
  }

  const config = readAuthConfig();
  const verificationUrl = createEmailVerificationLink({
    user: req.auth.user,
    origin: resolveOrigin(req),
    pathPrefix: '/verify-email',
  });

  const body = {
    ok: true,
    status: 'verification-link-sent',
  };

  if (config.exposeVerificationLink) {
    body.verification_url = verificationUrl;
  }

  return {
    ok: true,
    status: 200,
    body,
    redirect: appendQuery('/verify-email', { status: 'verification-link-sent' }),
  };
}

async function emailVerifyResult(req) {
  const verification = verifyEmailVerificationRequest({
    user: req.auth.user,
    routeUserId: req.params.id,
    routeHash: req.params.hash,
    expires: req.query.expires,
    signature: req.query.signature,
  });

  if (!verification.ok) {
    return {
      ok: false,
      status: 403,
      body: {
        ok: false,
        error: verification.reason,
        message: 'Invalid or expired verification link.',
      },
      errorRedirect: appendQuery('/verify-email', { auth_error: verification.reason }),
    };
  }

  const updatedUser = await markAuthUserEmailVerified(req.auth.user.id);

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      status: 'verified',
      user: toSessionUser(updatedUser),
      redirect: '/?verified=1',
    },
    redirect: '/?verified=1',
    session: {
      user: updatedUser,
      remember: Boolean(req.auth?.claims?.remember),
    },
  };
}

async function confirmPasswordResult(req) {
  const password = String(req.body?.password || '');
  const errorRedirect = '/confirm-password';

  if (!password) {
    return validationResult({ password: 'Password is required.' }, req, errorRedirect);
  }

  const user = await findAuthUserById(req.auth.user.id);
  if (!user || !user.password) return credentialsResult(req, errorRedirect);

  const passwordMatches = await bcrypt.compare(password, user.password);
  if (!passwordMatches) {
    return validationResult({ password: 'The provided password is incorrect.' }, req, errorRedirect);
  }

  const redirect = String(req.body?.redirect || req.query?.redirect || '/').trim() || '/';

  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      status: 'password-confirmed',
      redirect,
    },
    redirect,
    session: {
      user,
      remember: Boolean(req.auth?.claims?.remember),
      passwordConfirmedAt: Math.floor(Date.now() / 1000),
    },
  };
}

function confirmPasswordNoticeResult() {
  return {
    ok: true,
    status: 200,
    body: {
      ok: true,
      status: 'confirm-password-required',
    },
  };
}

module.exports = {
  authHealthResult,
  sessionResult,
  adminLoginResult,
  organizerLoginResult,
  organizerRegisterResult,
  organizerGoogleLoginResult,
  logoutResult,
  organizerLogoutResult,
  adminSessionResult,
  profileShowResult,
  profileUpdateResult,
  profileDeleteResult,
  passwordUpdateResult,
  passwordForgotResult,
  passwordResetResult,
  emailVerificationNoticeResult,
  emailVerificationNotificationResult,
  emailVerifyResult,
  confirmPasswordResult,
  confirmPasswordNoticeResult,
};
