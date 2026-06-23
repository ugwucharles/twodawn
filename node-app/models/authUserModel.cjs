const { query } = require('../db/client.cjs');

const AUTH_USER_SELECT = `
  id,
  name,
  email,
  username,
  email_verified_at,
  password,
  remember_token,
  is_admin,
  is_organizer,
  instagram_handle,
  whatsapp_number,
  twitter_handle,
  avatar_url,
  profile_picture,
  created_at,
  updated_at
`;

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

function normalizeEmail(value) {
  return String(value || '').trim().toLowerCase();
}

function normalizeName(value) {
  return String(value || '').trim();
}

function mapAuthUser(row) {
  if (!row) return null;

  return {
    ...row,
    is_admin: Boolean(row.is_admin),
    is_organizer: Boolean(row.is_organizer),
  };
}

function toSessionUser(user) {
  if (!user) return null;

  return {
    id: user.id,
    name: user.name,
    email: user.email,
    username: user.username || null,
    email_verified_at: user.email_verified_at || null,
    is_admin: Boolean(user.is_admin),
    is_organizer: Boolean(user.is_organizer),
  };
}

async function findAuthUserById(userId) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  const rows = await query(`SELECT ${AUTH_USER_SELECT} FROM users WHERE id = ? LIMIT 1`, [id]);
  return mapAuthUser(rows[0]);
}

async function findAuthUserByEmail(email) {
  const normalizedEmail = normalizeEmail(email);
  if (!normalizedEmail) return null;

  const rows = await query(`SELECT ${AUTH_USER_SELECT} FROM users WHERE email = ? LIMIT 1`, [normalizedEmail]);
  return mapAuthUser(rows[0]);
}

async function createOrganizerUser({ name, email, passwordHash }) {
  const normalizedName = normalizeName(name);
  const normalizedEmail = normalizeEmail(email);
  const normalizedPasswordHash = String(passwordHash || '').trim();
  if (!normalizedName || !normalizedEmail || !normalizedPasswordHash) {
    throw new Error('name, email, and passwordHash are required');
  }

  const result = await query(
    `INSERT INTO users (name, email, password, is_admin, is_organizer, created_at, updated_at)
     VALUES (?, ?, ?, 0, 1, datetime('now'), datetime('now'))`,
    [normalizedName, normalizedEmail, normalizedPasswordHash]
  );

  return findAuthUserById(result.insertId);
}

async function updateAuthUserProfile(userId, { name, email }) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  const existing = await findAuthUserById(id);
  if (!existing) return null;

  const nextName = normalizeName(name) || existing.name;
  const nextEmail = normalizeEmail(email) || existing.email;
  const emailChanged = normalizeEmail(existing.email) !== nextEmail;

  const updates = ['name = ?', 'email = ?'];
  const params = [nextName, nextEmail];

  if (emailChanged) {
    updates.push('email_verified_at = NULL');
  }

  updates.push("updated_at = datetime('now')");

  await query(`UPDATE users SET ${updates.join(', ')} WHERE id = ?`, [...params, id]);
  return findAuthUserById(id);
}

async function setPasswordForUser(userId, passwordHash, rememberToken = null) {
  const id = asPositiveInt(userId);
  const normalizedPasswordHash = String(passwordHash || '').trim();
  if (!id || !normalizedPasswordHash) return null;

  await query(
    `UPDATE users
     SET password = ?, remember_token = ?, updated_at = datetime('now')
     WHERE id = ?`,
    [normalizedPasswordHash, rememberToken, id]
  );

  return findAuthUserById(id);
}

async function markAuthUserEmailVerified(userId) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  await query(
    `UPDATE users
     SET email_verified_at = COALESCE(email_verified_at, datetime('now')), updated_at = datetime('now')
     WHERE id = ?`,
    [id]
  );

  return findAuthUserById(id);
}

async function deleteAuthUserById(userId) {
  const id = asPositiveInt(userId);
  if (!id) return false;

  const result = await query(`DELETE FROM users WHERE id = ?`, [id]);
  return Number(result.affectedRows || 0) > 0;
}

async function updateAuthUserUsername(userId, username) {
  const id = asPositiveInt(userId);
  const normalizedUsername = String(username || '').trim().toLowerCase();
  if (!id || !normalizedUsername) return null;

  await query(
    `UPDATE users
     SET username = ?, updated_at = datetime('now')
     WHERE id = ?`,
    [normalizedUsername, id]
  );

  return findAuthUserById(id);
}

async function updateOrganizerSettings(userId, { name, instagramHandle, twitterHandle, whatsappNumber, avatarUrl }) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  const updates = ["updated_at = datetime('now')"];
  const params = [];

  if (name !== undefined) { updates.push('name = ?'); params.push(name ? String(name).trim() : null); }
  if (instagramHandle !== undefined) { updates.push('instagram_handle = ?'); params.push(instagramHandle ? String(instagramHandle).trim() : null); }
  if (twitterHandle !== undefined) { updates.push('twitter_handle = ?'); params.push(twitterHandle ? String(twitterHandle).trim() : null); }
  if (whatsappNumber !== undefined) { updates.push('whatsapp_number = ?'); params.push(whatsappNumber ? String(whatsappNumber).trim() : null); }
  if (avatarUrl !== undefined) { updates.push('avatar_url = ?'); params.push(avatarUrl ? String(avatarUrl).trim() : null); }

  await query(`UPDATE users SET ${updates.join(', ')} WHERE id = ?`, [...params, id]);
  return findAuthUserById(id);
}

module.exports = {
  normalizeEmail,
  toSessionUser,
  findAuthUserById,
  findAuthUserByEmail,
  createOrganizerUser,
  updateAuthUserProfile,
  setPasswordForUser,
  markAuthUserEmailVerified,
  updateAuthUserUsername,
  updateOrganizerSettings,
  deleteAuthUserById,
};
