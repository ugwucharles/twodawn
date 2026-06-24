const { createClient } = require('@libsql/client/web');

let tursoClient = null;

function getDatabase() {
  // Always require Turso - no SQLite fallback anywhere
  if (!process.env.TURSO_DATABASE_URL || !process.env.TURSO_AUTH_TOKEN) {
    throw new Error('Turso is required. Set TURSO_DATABASE_URL and TURSO_AUTH_TOKEN environment variables.');
  }

  if (tursoClient) return tursoClient;
  
  console.log('🔌 Using Turso database');
  console.log('TURSO_DATABASE_URL:', process.env.TURSO_DATABASE_URL ? 'SET' : 'NOT SET');
  console.log('TURSO_AUTH_TOKEN:', process.env.TURSO_AUTH_TOKEN ? 'SET' : 'NOT SET');
  
  tursoClient = createClient({
    url: process.env.TURSO_DATABASE_URL,
    authToken: process.env.TURSO_AUTH_TOKEN,
  });
  return tursoClient;
}

async function query(sql, params = []) {
  const database = getDatabase();
  console.log('🔍 Query type: Turso, SQL:', sql.substring(0, 50));
  try {
    const result = await database.execute({ sql, args: params });
    const sqlUpper = sql.trim().toUpperCase();
    if (sqlUpper.startsWith('INSERT') || sqlUpper.startsWith('UPDATE') || sqlUpper.startsWith('DELETE')) {
      return {
        insertId: result.lastInsertRowid !== undefined && result.lastInsertRowid !== null ? Number(result.lastInsertRowid) : null,
        affectedRows: result.rowsAffected,
      };
    }
    return result.rows.map((row) => (typeof row.toJSON === 'function' ? row.toJSON() : { ...row }));
  } catch (error) {
    console.log('❌ Query error:', error.message);
    throw error;
  }
}

async function pingDatabase() {
  try {
    const database = getDatabase();
    await database.execute('SELECT 1');
    return {
      ok: true,
      status: 'connected',
      configured: true,
      config: {
        type: 'turso',
        path: process.env.TURSO_DATABASE_URL,
      },
    };
  } catch (error) {
    return {
      ok: false,
      status: 'error',
      configured: true,
      config: {
        type: 'turso',
        path: process.env.TURSO_DATABASE_URL,
      },
      error: { code: error.code || null, message: error.message },
    };
  }
}

module.exports = { getDatabase, query, pingDatabase };
