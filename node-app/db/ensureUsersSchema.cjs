const { query } = require('./client.cjs');

const USERS_TABLE_SQL = `
  CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    email TEXT,
    username TEXT UNIQUE,
    email_verified_at TEXT,
    password TEXT,
    remember_token TEXT,
    is_admin INTEGER DEFAULT 0,
    is_organizer INTEGER DEFAULT 0,
    instagram_handle TEXT,
    whatsapp_number TEXT,
    twitter_handle TEXT,
    avatar_url TEXT,
    profile_picture TEXT,
    created_at TEXT,
    updated_at TEXT
  )
`;

const COLUMN_MIGRATIONS = [
  { column: 'username', sql: 'ALTER TABLE users ADD COLUMN username TEXT UNIQUE' },
  { column: 'email_verified_at', sql: 'ALTER TABLE users ADD COLUMN email_verified_at TEXT' },
  { column: 'remember_token', sql: 'ALTER TABLE users ADD COLUMN remember_token TEXT' },
  { column: 'is_admin', sql: 'ALTER TABLE users ADD COLUMN is_admin INTEGER DEFAULT 0' },
  { column: 'is_organizer', sql: 'ALTER TABLE users ADD COLUMN is_organizer INTEGER DEFAULT 1' },
  { column: 'instagram_handle', sql: 'ALTER TABLE users ADD COLUMN instagram_handle TEXT' },
  { column: 'whatsapp_number', sql: 'ALTER TABLE users ADD COLUMN whatsapp_number TEXT' },
  { column: 'twitter_handle', sql: 'ALTER TABLE users ADD COLUMN twitter_handle TEXT' },
  { column: 'avatar_url', sql: 'ALTER TABLE users ADD COLUMN avatar_url TEXT' },
  { column: 'profile_picture', sql: 'ALTER TABLE users ADD COLUMN profile_picture TEXT' },
  { column: 'created_at', sql: 'ALTER TABLE users ADD COLUMN created_at TEXT' },
  { column: 'updated_at', sql: 'ALTER TABLE users ADD COLUMN updated_at TEXT' },
];

let schemaReady = null;

async function columnExists(columnName) {
  const rows = await query('PRAGMA table_info(users)');
  return rows.some((row) => String(row.name).toLowerCase() === columnName.toLowerCase());
}

async function ensureUsersSchema() {
  if (schemaReady) return schemaReady;

  schemaReady = (async () => {
    await query(USERS_TABLE_SQL);

    for (const migration of COLUMN_MIGRATIONS) {
      const exists = await columnExists(migration.column);
      if (exists) continue;

      try {
        await query(migration.sql);
        console.log(`✅ Added users.${migration.column} column`);
      } catch (error) {
        if (!String(error.message).includes('duplicate column')) {
          console.error(`❌ Failed to add users.${migration.column}:`, error.message);
          throw error;
        }
      }
    }
  })();

  return schemaReady;
}

module.exports = { ensureUsersSchema };
