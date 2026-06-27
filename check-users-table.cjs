require('./node-app/config/envLoader.cjs');
const { createClient } = require('@libsql/client/web');

const client = createClient({
  url: process.env.TURSO_DATABASE_URL,
  authToken: process.env.TURSO_AUTH_TOKEN,
});

async function checkUsersTable() {
  try {
    console.log('1. Querying users table...');
    const r1 = await client.execute('SELECT * FROM users LIMIT 1');
    console.log('   Success! Columns:', Object.keys(r1.rows[0] || {}));

    console.log('2. Creating users table...');
    await client.execute(`
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
    `);
    console.log('   Success!');

    console.log('3. PRAGMA table_info(users)...');
    const r3 = await client.execute('PRAGMA table_info(users)');
    console.log('   Success! Rows found:', r3.rows.length);

    console.log('4. Creating orders table...');
    await client.execute(`
      CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        ticket_type TEXT,
        buyer_name TEXT NOT NULL,
        buyer_email TEXT NOT NULL,
        buyer_phone TEXT,
        coupon_code TEXT,
        quantity INTEGER NOT NULL,
        amount INTEGER NOT NULL,
        paystack_reference TEXT NOT NULL UNIQUE,
        ticket_code TEXT,
        status TEXT DEFAULT 'pending',
        created_ip TEXT,
        created_at TEXT,
        updated_at TEXT,
        last_checkin_at TEXT
      )
    `);
    console.log('   Success!');

    console.log('5. Creating events table...');
    await client.execute(`
      CREATE TABLE IF NOT EXISTS events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        slug TEXT UNIQUE,
        description TEXT,
        venue TEXT,
        state TEXT,
        starts_at TEXT,
        ends_at TEXT,
        price REAL DEFAULT 0,
        early_bird_price REAL,
        early_bird_ends_at TEXT,
        capacity INTEGER,
        pass_fees_to_buyer INTEGER DEFAULT 0,
        free_tickets_count INTEGER DEFAULT 0,
        ticket_types TEXT,
        image_url TEXT,
        is_published INTEGER DEFAULT 0,
        created_at TEXT,
        updated_at TEXT
      )
    `);
    console.log('   Success!');

    console.log('6. Creating activity_logs table...');
    await client.execute(`
      CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        action TEXT NOT NULL,
        entity_type TEXT NOT NULL,
        entity_id INTEGER,
        details TEXT,
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      )
    `);
    console.log('   Success!');

  } catch (error) {
    console.error('❌ Error during query sequence:', error);
  }
}

checkUsersTable();
