require('./node-app/config/envLoader.cjs');
const { createClient } = require('@libsql/client/web');

const client = createClient({
  url: process.env.TURSO_DATABASE_URL,
  authToken: process.env.TURSO_AUTH_TOKEN,
});

async function createUsersTable() {
  try {
    // Drop existing table if it exists
    await client.execute('DROP TABLE IF EXISTS users');
    console.log('Dropped existing users table');
    
    // Create table with correct schema
    await client.execute(`
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        username TEXT UNIQUE,
        email_verified_at TEXT,
        password TEXT NOT NULL,
        remember_token TEXT,
        is_admin INTEGER DEFAULT 0,
        is_organizer INTEGER DEFAULT 0,
        instagram_handle TEXT,
        whatsapp_number TEXT,
        twitter_handle TEXT,
        avatar_url TEXT,
        profile_picture TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    `);
    console.log('✅ Users table created successfully in Turso');
  } catch (error) {
    console.error('❌ Error creating users table:', error);
  }
}

createUsersTable();
