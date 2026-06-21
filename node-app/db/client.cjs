const path = require('path');
const Database = require('better-sqlite3');
const { createClient } = require('@libsql/client/web');

let localDb = null;
let tursoClient = null;
let isTurso = false;

function getDatabase() {
  if (process.env.TURSO_DATABASE_URL && process.env.TURSO_AUTH_TOKEN) {
    if (tursoClient) return tursoClient;
    tursoClient = createClient({
      url: process.env.TURSO_DATABASE_URL,
      authToken: process.env.TURSO_AUTH_TOKEN,
    });
    isTurso = true;
    return tursoClient;
  }

  // Fallback to local SQLite
  if (localDb) return localDb;
  const dbPath = process.env.DB_PATH || '/tmp/database.sqlite';
  localDb = new Database(dbPath);
  localDb.pragma('journal_mode = WAL');
  isTurso = false;
  return localDb;
}

async function query(sql, params = []) {
  const database = getDatabase();
  
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
      // Local SQLite
      const stmt = database.prepare(sql);
      const sqlUpper = sql.trim().toUpperCase();
      
      if (sqlUpper.startsWith('INSERT') || sqlUpper.startsWith('UPDATE') || sqlUpper.startsWith('DELETE')) {
        const result = stmt.run(...params);
        return {
          insertId: result.lastInsertRowid,
          affectedRows: result.changes,
        };
      }
      
      return stmt.all(...params);
    }
  } catch (error) {
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
        path: isTurso ? process.env.TURSO_DATABASE_URL : (process.env.DB_PATH || path.join(__dirname, '../../database.sqlite')),
      },
    };
  } catch (error) {
    return {
      ok: false,
      status: 'error',
      configured: true,
      config: {
        type: isTurso ? 'turso' : 'sqlite',
        path: isTurso ? process.env.TURSO_DATABASE_URL : (process.env.DB_PATH || path.join(__dirname, '../../database.sqlite')),
      },
      error: {
        code: error.code || null,
        message: error.message,
      },
    };
  }
}

module.exports = {
  getDatabase,
  query,
  pingDatabase,
};
