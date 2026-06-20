const { query } = require('../db/client.cjs');

function normalizeEmail(value) {
  return String(value || '').trim().toLowerCase();
}

async function upsertPasswordResetToken(email, tokenHash) {
  const normalizedEmail = normalizeEmail(email);
  const normalizedTokenHash = String(tokenHash || '').trim();
  if (!normalizedEmail || !normalizedTokenHash) {
    throw new Error('email and tokenHash are required');
  }

  await query(
    `INSERT INTO password_reset_tokens (email, token, created_at)
     VALUES (?, ?, datetime('now'))
     ON CONFLICT(email) DO UPDATE SET token = excluded.token, created_at = excluded.created_at`,
    [normalizedEmail, normalizedTokenHash]
  );
}

async function findPasswordResetToken(email) {
  const normalizedEmail = normalizeEmail(email);
  if (!normalizedEmail) return null;

  const rows = await query(
    `SELECT email, token, created_at
     FROM password_reset_tokens
     WHERE email = ?
     LIMIT 1`,
    [normalizedEmail]
  );

  return rows[0] || null;
}

async function deletePasswordResetToken(email) {
  const normalizedEmail = normalizeEmail(email);
  if (!normalizedEmail) return;

  await query(`DELETE FROM password_reset_tokens WHERE email = ? LIMIT 1`, [normalizedEmail]);
}

module.exports = {
  upsertPasswordResetToken,
  findPasswordResetToken,
  deletePasswordResetToken,
};
