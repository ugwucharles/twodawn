const path = require('path');
const Database = require('better-sqlite3');

// Resolve the SQLite file path (project root)
const dbPath = path.resolve(__dirname, '..', '..', 'database.sqlite');
const db = new Database(dbPath);

// Ensure the events table exists – schema matches the model expectations
db.exec(`
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
`);

// Insert a demo event (helps the UI show something after a fresh deploy)
const insert = db.prepare(`
  INSERT INTO events (
    user_id, title, description, venue, state, starts_at, is_published, slug, created_at, updated_at
  ) VALUES (?, ?, ?, ?, ?, datetime('now'), 1, ?, datetime('now'), datetime('now'))
`);

insert.run(
  1,
  'Demo Event',
  'Demo event created by seed script.',
  'Demo Venue',
  'Demo State',
  'demo-event-' + Math.random().toString(36).substring(2, 8)
);

console.log('✅ SQLite seed completed – database at', dbPath);
