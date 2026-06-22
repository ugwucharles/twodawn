require('./node-app/config/envLoader.cjs');
const { createClient } = require('@libsql/client/web');

const client = createClient({
  url: process.env.TURSO_DATABASE_URL,
  authToken: process.env.TURSO_AUTH_TOKEN,
});

async function checkUsersTable() {
  try {
    const result = await client.execute('SELECT * FROM users LIMIT 1');
    console.log('Users table columns:', Object.keys(result.rows[0] || {}));
  } catch (error) {
    console.error('Error:', error.message);
  }
}

checkUsersTable();
