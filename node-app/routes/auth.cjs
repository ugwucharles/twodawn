const express = require('express');
const { attachSessionUser, requireAuthenticated, requireAdmin } = require('../services/sessionAuth.cjs');
const { sendAuthResult, unauthenticatedResult, forbiddenResult } = require('../lib/authHttp.cjs');
const {
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
} = require('../services/authHandlers.cjs');

function asyncRoute(handler) {
  return function routeHandler(req, res, next) {
    Promise.resolve(handler(req, res, next)).catch(next);
  };
}

function requireAuthenticatedFlexible(req, res, next) {
  if (req.auth && req.auth.isAuthenticated) return next();

  const result = unauthenticatedResult(req);
  return sendAuthResult(req, res, result);
}

function requireAdminFlexible(req, res, next) {
  if (!req.auth || !req.auth.isAuthenticated) {
    return sendAuthResult(req, res, unauthenticatedResult(req, '/xyz/login'));
  }

  if (!req.auth.user?.is_admin) {
    return sendAuthResult(req, res, forbiddenResult(req, 'Admin access required.', '/xyz/login'));
  }

  return next();
}

function createAuthRouter() {
  const router = express.Router();

  router.use(express.json({ limit: '1mb' }));
  router.use(express.urlencoded({ extended: false }));
  router.use(attachSessionUser);

  router.get('/health', (req, res) => sendAuthResult(req, res, authHealthResult()));
  router.get('/session', (req, res) => sendAuthResult(req, res, sessionResult(req)));

  router.post(
    '/admin/login',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await adminLoginResult(req)))
  );
  router.post(
    '/organizer/login',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await organizerLoginResult(req)))
  );
  router.post(
    '/organizer/register',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await organizerRegisterResult(req)))
  );
  router.post('/logout', (req, res) => sendAuthResult(req, res, logoutResult()));
  router.post('/organizer/logout', (req, res) => sendAuthResult(req, res, organizerLogoutResult()));

  router.get('/admin/session', requireAuthenticated, requireAdmin, (req, res) =>
    sendAuthResult(req, res, adminSessionResult(req))
  );
  router.get('/profile', requireAuthenticated, (req, res) => sendAuthResult(req, res, profileShowResult(req)));
  router.patch(
    '/profile',
    requireAuthenticated,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await profileUpdateResult(req)))
  );
  router.delete(
    '/profile',
    requireAuthenticated,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await profileDeleteResult(req)))
  );
  router.post(
    '/password/update',
    requireAuthenticated,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await passwordUpdateResult(req)))
  );
  router.post(
    '/password/forgot',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await passwordForgotResult(req)))
  );
  router.post(
    '/password/reset',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await passwordResetResult(req)))
  );

  router.get('/email/verification-notice', requireAuthenticated, (req, res) =>
    sendAuthResult(req, res, emailVerificationNoticeResult(req))
  );
  router.post(
    '/email/verification-notification',
    requireAuthenticated,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await emailVerificationNotificationResult(req)))
  );
  router.get(
    '/email/verify/:id/:hash',
    requireAuthenticated,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await emailVerifyResult(req)))
  );

  router.get('/confirm-password', requireAuthenticated, (req, res) =>
    sendAuthResult(req, res, confirmPasswordNoticeResult())
  );
  router.post(
    '/confirm-password',
    requireAuthenticated,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await confirmPasswordResult(req)))
  );

  router.use((error, _req, res, _next) => {
    return res.status(500).json({
      ok: false,
      error: 'internal_error',
      message: error.message,
    });
  });

  return router;
}

function createPublicAuthRouter() {
  const router = express.Router();
  const { readAuthConfig } = require('../config/auth.cjs');
  const { proxyToLaravel } = require('../services/proxyRequest.cjs');

  router.use(express.json({ limit: '1mb' }));
  router.use(express.urlencoded({ extended: false }));
  router.use(attachSessionUser);

  router.use((req, res, next) => {
    const config = readAuthConfig();
    if (!config.publicAuthEnabled) return next('router');
    return next();
  });

  function proxyAuthPage(req, res) {
    const config = readAuthConfig();
    if (config.proxyAuthGetPages) {
      return proxyToLaravel(req, res);
    }

    return res.status(501).json({
      ok: false,
      error: 'auth_page_not_implemented',
      message: 'Auth HTML pages are proxied to Laravel by default.',
    });
  }

  router.get('/login', (_req, res) => res.redirect(302, '/organizer/login'));
  router.post('/login', (_req, res) => res.redirect(302, '/organizer/login'));

  router.get('/xyz/login', proxyAuthPage);
  router.post(
    '/xyz/login',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await adminLoginResult(req)))
  );

  router.get('/organizer/login', proxyAuthPage);
  router.post(
    '/organizer/login',
    asyncRoute(async (req, res) => {
      console.log('POST /organizer/login received by Node.js backend');
      console.log('Request body:', req.body);
      try {
        const result = await organizerLoginResult(req);
        console.log('Login result:', result);
        return sendAuthResult(req, res, result);
      } catch (error) {
        console.error('Login error:', error);
        throw error;
      }
    })
  );

  router.get('/organizer/register', proxyAuthPage);
  router.post(
    '/organizer/register',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await organizerRegisterResult(req)))
  );

  router.post(
    '/organizer/google-auth',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await organizerGoogleLoginResult(req)))
  );

  router.post(
    '/organizer/onboarding',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => {
      const { username, name, instagramHandle, twitterHandle, whatsappNumber } = req.body;

      if (!username || !username.trim()) {
        return res.status(400).json({ ok: false, error: 'missing_username', message: 'Username is required.' });
      }

      const cleanUsername = String(username).trim().toLowerCase();

      // Check format (alphanumeric and dashes, matching tix.africa style usernames)
      if (!/^[a-z0-9-]+$/.test(cleanUsername)) {
        return res.status(400).json({ ok: false, error: 'invalid_format', message: 'Username can only contain lowercase letters, numbers, and dashes.' });
      }

      try {
        // Check if username is already taken
        const { query } = require('../db/client.cjs');
        const existing = await query('SELECT id FROM users WHERE username = ? LIMIT 1', [cleanUsername]);
        if (existing.length > 0) {
          return res.status(400).json({ ok: false, error: 'username_taken', message: 'Username is already taken.' });
        }

        // Update the username and optional branding details
        await query(
          `UPDATE users 
           SET username = ?, 
               name = COALESCE(?, name), 
               instagram_handle = COALESCE(?, instagram_handle), 
               twitter_handle = COALESCE(?, twitter_handle), 
               whatsapp_number = COALESCE(?, whatsapp_number),
               updated_at = datetime('now')
           WHERE id = ?`,
          [
            cleanUsername,
            name ? String(name).trim() : null,
            instagramHandle ? String(instagramHandle).trim() : null,
            twitterHandle ? String(twitterHandle).trim() : null,
            whatsappNumber ? String(whatsappNumber).trim() : null,
            req.auth.user.id
          ]
        );

        const { findAuthUserById } = require('../models/authUserModel.cjs');
        const updatedUser = await findAuthUserById(req.auth.user.id);
        
        return res.json({
          ok: true,
          user: {
            id: updatedUser.id,
            name: updatedUser.name,
            email: updatedUser.email,
            username: updatedUser.username,
            email_verified_at: updatedUser.email_verified_at,
            is_admin: Boolean(updatedUser.is_admin),
            is_organizer: Boolean(updatedUser.is_organizer)
          },
          redirect: '/organizer/dashboard'
        });
      } catch (err) {
        console.error('Onboarding error:', err);
        return res.status(500).json({ ok: false, error: 'onboarding_failed', message: 'Failed to complete onboarding.' });
      }
    })
  );

  router.post('/organizer/logout', requireAuthenticatedFlexible, (req, res) =>
    sendAuthResult(req, res, organizerLogoutResult())
  );

  router.get('/profile', requireAuthenticatedFlexible, (req, res) => {
    const { wantsJson } = require('../lib/authHttp.cjs');
    if (wantsJson(req)) {
      return sendAuthResult(req, res, profileShowResult(req));
    }
    return proxyAuthPage(req, res);
  });
  router.patch(
    '/profile',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await profileUpdateResult(req)))
  );
  router.delete(
    '/profile',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await profileDeleteResult(req)))
  );

  router.get('/forgot-password', proxyAuthPage);
  router.post(
    '/forgot-password',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await passwordForgotResult(req)))
  );

  router.get('/reset-password/:token', proxyAuthPage);
  router.post(
    '/reset-password',
    asyncRoute(async (req, res) => sendAuthResult(req, res, await passwordResetResult(req)))
  );

  router.get('/verify-email', requireAuthenticatedFlexible, (req, res) => {
    const { wantsJson } = require('../lib/authHttp.cjs');
    if (wantsJson(req)) {
      return sendAuthResult(req, res, emailVerificationNoticeResult(req));
    }
    return proxyAuthPage(req, res);
  });
  router.get(
    '/verify-email/:id/:hash',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await emailVerifyResult(req)))
  );
  router.post(
    '/email/verification-notification',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await emailVerificationNotificationResult(req)))
  );

  router.get('/confirm-password', requireAuthenticatedFlexible, (req, res) => {
    const { wantsJson } = require('../lib/authHttp.cjs');
    if (wantsJson(req)) {
      return sendAuthResult(req, res, confirmPasswordNoticeResult());
    }
    return proxyAuthPage(req, res);
  });
  router.post(
    '/confirm-password',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await confirmPasswordResult(req)))
  );

  router.put(
    '/password',
    requireAuthenticatedFlexible,
    asyncRoute(async (req, res) => sendAuthResult(req, res, await passwordUpdateResult(req)))
  );
  router.post('/logout', requireAuthenticatedFlexible, (req, res) =>
    sendAuthResult(req, res, logoutResult())
  );

  router.use((error, _req, res, _next) => {
    return res.status(500).json({
      ok: false,
      error: 'internal_error',
      message: error.message,
    });
  });

  return router;
}

module.exports = {
  createAuthRouter,
  createPublicAuthRouter,
};
