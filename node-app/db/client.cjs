const path = require('path');
const fs = require('fs');
const Database = require('better-sqlite3');
const { createClient } = require('@libsql/client/web');

let localDb = null;
let tursoClient = null;
let isTurso = false;

function initDatabase(db) {
  const createTableSQL = `
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
  db.exec(createTableSQL);
  const demoSQL = `
    INSERT INTO events (user_id, title, venue, state, starts_at, is_published, slug, created_at, updated_at)
    VALUES (1, 'Demo Event', 'Demo Venue', 'Demo State', datetime('now'), 1,
            'demo-event-' || substr(hex(randomblob(4)), 1, 8),
            datetime('now'), datetime('now'));
  `;
  try { db.exec(demoSQL); } catch (_) {}
}

function getDatabase() {
  // Prefer Turso if env vars are provided
  if (process.env.TURSO_DATABASE_URL && process.env.TURSO_AUTH_TOKEN) {
    if (tursoClient) return tursoClient;
    console.log('🔌 Using Turso database');
    console.log('TURSO_DATABASE_URL:', process.env.TURSO_DATABASE_URL ? 'SET' : 'NOT SET');
    console.log('TURSO_AUTH_TOKEN:', process.env.TURSO_AUTH_TOKEN ? 'SET' : 'NOT SET');
    tursoClient = createClient({
      url: process.env.TURSO_DATABASE_URL,
      authToken: process.env.TURSO_AUTH_TOKEN,
    });
    isTurso = true;
    return tursoClient;
  }

  // Fallback to local SQLite stored in project root for local development
  if (localDb) return localDb;
  const dbPath = process.env.DB_PATH || path.join(__dirname, '../../database.sqlite');
  const needInit = !fs.existsSync(dbPath);
  
  // Ensure directory exists before creating database
  const dbDir = path.dirname(dbPath);
  if (!fs.existsSync(dbDir)) {
    fs.mkdirSync(dbDir, { recursive: true });
  }
  
  console.log('⚠️ Falling back to SQLite database at:', dbPath);
  localDb = new Database(dbPath);
  if (needInit) initDatabase(localDb);
  localDb.pragma('journal_mode = WAL');
  isTurso = false;
  return localDb;
}

async function query(sql, params = []) {
  const database = getDatabase();
  console.log('🔍 Query type:', isTurso ? 'Turso' : 'SQLite', 'SQL:', sql.substring(0, 50));
  try {
    if (isTurso) {
      const result = await database.execute({ sql, args: params });
      const sqlUpper = sql.trim().toUpperCase();
      if (sqlUpper.startsWith('INSERT') || sqlUpper.startsWith('UPDATE') || sqlUpper.startsWith('DELETE')) {
        return {
          insertId: result.lastInsertRowid !== undefined && result.lastInsertRowid !== null ? Number(result.lastInsertRowid) : null,
          affectedRows: result.rowsAffected,
        };
      }
      return result.rows;
    } else {
      console.log('⚠️ Using SQLite for query:', sql.substring(0, 50));
      const stmt = database.prepare(sql);
      const sqlUpper = sql.trim().toUpperCase();
      if (sqlUpper.startsWith('INSERT') || sqlUpper.startsWith('UPDATE') || sqlUpper.startsWith('DELETE')) {
        const result = stmt.run(...params);
        return { insertId: result.lastInsertRowid, affectedRows: result.changes };
      }
      return stmt.all(...params);
    }
  } catch (error) {
    console.log('❌ Query error:', error.message);
    throw error;
  }
}

async function pingDatabase() {
  try {
    const database = getDatabase();
    if (isTurso) {
      await database.execute('SELECT 1');
    } else {
      database.prepare('SELECT 1').get();
    }
    return {
      ok: true,
      status: 'connected',
      configured: true,
      config: {
        type: isTurso ? 'turso' : 'sqlite',
        path: isTurso ? process.env.TURSO_DATABASE_URL : (process.env.DB_PATH || '/tmp/database.sqlite'),
      },
    };
  } catch (error) {
    return {
      ok: false,
      status: 'error',
      configured: true,
      config: {
        type: isTurso ? 'turso' : 'sqlite',
        path: isTurso ? process.env.TURSO_DATABASE_URL : (process.env.DB_PATH || '/tmp/database.sqlite'),
      },
      error: { code: error.code || null, message: error.message },
    };
  }
}

module.exports = { getDatabase, query, pingDatabase };
