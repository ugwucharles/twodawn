// node-app/scripts/seed.cjs
const path = require('path');
const fs = require('fs');
const sqlite3 = require('sqlite3').verbose();   // <-- pure‑js driver

// Use project root database for local development
const dbPath = path.join(__dirname, '../../database.sqlite');

// Ensure directory exists before creating database
const dbDir = path.dirname(dbPath);
if (!fs.existsSync(dbDir)) {
  fs.mkdirSync(dbDir, { recursive: true });
}

// Open (or create) the DB
const db = new sqlite3.Database(dbPath, err => {
  if (err) {
    console.error('❌ Failed to open SQLite DB:', err);
    process.exit(1);
  }
});

const createTableSQL = `
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
    avatar_url TEXT,
    username TEXT,
    profile_picture TEXT,
    created_at TEXT,
    updated_at TEXT
  );

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

db.exec(createTableSQL, err => {
  if (err) {
    console.error('❌ Failed to create table:', err);
    process.exit(1);
  }

  const demoSQL = `
    -- Insert demo user first
    INSERT OR IGNORE INTO users (name, email, password, is_admin, is_organizer, username, created_at, updated_at)
    VALUES ('Demo Organizer', 'demo@twodawn.com', 'demo_password_hash', 0, 1, 'demoorganizer', datetime('now'), datetime('now'));

    -- Insert demo events
    INSERT INTO events (user_id, title, venue, state, starts_at, is_published, slug, created_at, updated_at, price, capacity, description)
    VALUES 
      (1, 'Demo Event', 'Demo Venue', 'Lagos', datetime('now'), 1,
       'demo-event-' || substr(hex(randomblob(4)), 1, 8),
       datetime('now'), datetime('now'), 5000, 100, 'This is a demo event for testing'),
      (1, 'Music Festival', 'National Stadium', 'Lagos', datetime('now', '+7 days'), 1,
       'music-festival-' || substr(hex(randomblob(4)), 1, 8),
       datetime('now'), datetime('now'), 10000, 500, 'Annual music festival with top artists'),
      (1, 'Tech Conference', 'Convention Center', 'Abuja', datetime('now', '+14 days'), 1,
       'tech-conference-' || substr(hex(randomblob(4)), 1, 8),
       datetime('now'), datetime('now'), 15000, 200, 'Technology conference for developers');
  `;

  db.run(demoSQL, err => {
    if (err) console.warn('⚠️ Could not insert demo event (maybe already exists):', err);
    console.log('✅ SQLite seed complete – database at', dbPath);
    db.close();
  });
});
