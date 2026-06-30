const { query } = require('./client.cjs');

const WITHDRAWALS_TABLE_SQL = `
  CREATE TABLE IF NOT EXISTS withdrawals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    bank_name TEXT NOT NULL,
    account_number TEXT NOT NULL,
    account_name TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  )
`;

let schemaReady = null;

async function ensureWithdrawalsSchema() {
  if (schemaReady) return schemaReady;

  schemaReady = (async () => {
    await query(WITHDRAWALS_TABLE_SQL);
    console.log('✅ Withdrawals table verified/created');
  })();

  return schemaReady;
}

module.exports = { ensureWithdrawalsSchema };
