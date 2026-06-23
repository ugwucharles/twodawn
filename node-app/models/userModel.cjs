const { query } = require('../db/client.cjs');

const USER_SELECT = `
  id,
  name,
  email,
  email_verified_at,
  is_admin,
  is_organizer,
  instagram_handle,
  whatsapp_number,
  twitter_handle,
  avatar_url,
  username,
  profile_picture,
  created_at,
  updated_at
`;

function mapUser(row) {
  if (!row) return null;

  return {
    ...row,
    is_admin: Boolean(row.is_admin),
    is_organizer: Boolean(row.is_organizer),
  };
}

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

function normalizePage({ limit = 20, offset = 0 } = {}) {
  const normalizedLimit = Math.min(Math.max(Number(limit) || 20, 1), 100);
  const normalizedOffset = Math.max(Number(offset) || 0, 0);
  return {
    limit: normalizedLimit,
    offset: normalizedOffset,
  };
}

async function findUserById(userId) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  const rows = await query(`SELECT ${USER_SELECT} FROM users WHERE id = ? LIMIT 1`, [id]);
  return mapUser(rows[0]);
}

async function findUserByEmail(email) {
  const normalizedEmail = String(email || '').trim().toLowerCase();
  if (!normalizedEmail) return null;

  const rows = await query(`SELECT ${USER_SELECT} FROM users WHERE email = ? LIMIT 1`, [normalizedEmail]);
  return mapUser(rows[0]);
}

async function listOrganizers(page = {}) {
  const { limit, offset } = normalizePage(page);

  const rows = await query(
    `SELECT ${USER_SELECT}
     FROM users
     WHERE is_organizer = 1
     ORDER BY id DESC
     LIMIT ? OFFSET ?`,
    [limit, offset]
  );

  return rows.map(mapUser);
}

module.exports = {
  findUserById,
  findUserByEmail,
  listOrganizers,
};
