// node-app/scripts/seed.cjs
require('../config/envLoader.cjs');
const { query } = require('../db/client.cjs');

async function seedDatabase() {
  console.log('🌱 Starting database seed...');

  const createUsersTable = `
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT,
      email TEXT,
      email_verified_at TEXT,
      password TEXT,
      is_admin INTEGER DEFAULT 0,
      is_organizer INTEGER DEFAULT 0,
      instagram_handle TEXT,
      whatsapp_number TEXT,
      twitter_handle TEXT,
      username TEXT,
      profile_picture TEXT,
      created_at TEXT,
      updated_at TEXT
    );
  `;

  const createEventsTable = `
    CREATE TABLE IF NOT EXISTS events (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER,
      title TEXT,
      description TEXT,
      must_know TEXT,
      venue TEXT,
      state TEXT,
      starts_at TEXT,
      ends_at TEXT,
      price REAL,
      capacity INTEGER,
      is_published INTEGER,
      pass_fees_to_buyer INTEGER,
      image_path TEXT,
      slug TEXT,
      ticket_types TEXT,
      created_at TEXT,
      updated_at TEXT
    );
  `;

  try {
    // Create tables
    await query(createUsersTable);
    await query(createEventsTable);
    console.log('✅ Tables created/verified');

    // Add profile_picture column if it doesn't exist
    try {
      await query(`ALTER TABLE users ADD COLUMN profile_picture TEXT`);
      console.log('✅ Added profile_picture column');
    } catch (err) {
      console.log('ℹ️ profile_picture column already exists or error:', err.message);
    }

    // Drop avatar_url column if it exists (legacy column)
    try {
      await query(`ALTER TABLE users DROP COLUMN avatar_url`);
      console.log('✅ Dropped avatar_url column');
    } catch (err) {
      console.log('ℹ️ avatar_url column does not exist or error:', err.message);
    }

    // Insert demo user
    await query(`
      INSERT OR IGNORE INTO users (name, email, password, is_admin, is_organizer, username, created_at, updated_at)
      VALUES ('Demo Organizer', 'demo@twodawn.com', 'demo_password_hash', 0, 1, 'demoorganizer', datetime('now'), datetime('now'))
    `);
    console.log('✅ Demo user inserted');

    // Insert demo events
    await query(`
      INSERT INTO events (user_id, title, venue, state, starts_at, is_published, slug, created_at, updated_at, price, capacity, description)
      VALUES 
        (1, 'Demo Event', 'Demo Venue', 'Lagos', datetime('now'), 1,
         'demo-event-' || lower(hex(randomblob(4))),
         datetime('now'), datetime('now'), 5000, 100, 'This is a demo event for testing'),
        (1, 'Music Festival', 'National Stadium', 'Lagos', datetime('now', '+7 days'), 1,
         'music-festival-' || lower(hex(randomblob(4))),
         datetime('now'), datetime('now'), 10000, 500, 'Annual music festival with top artists'),
        (1, 'Tech Conference', 'Convention Center', 'Abuja', datetime('now', '+14 days'), 1,
         'tech-conference-' || lower(hex(randomblob(4))),
         datetime('now'), datetime('now'), 15000, 200, 'Technology conference for developers')
    `);
    console.log('✅ Demo events inserted');

    console.log('🎉 Database seed complete!');
  } catch (error) {
    console.error('❌ Seed error:', error);
    process.exit(1);
  }
}

seedDatabase();
