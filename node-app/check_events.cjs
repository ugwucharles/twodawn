'use strict';
process.chdir(__dirname);
require('dotenv').config({ path: './.env' });

const { createClient } = require('@libsql/client/web');

async function main() {
  const db = createClient({
    url: process.env.TURSO_DATABASE_URL,
    authToken: process.env.TURSO_AUTH_TOKEN,
  });

  // Get schema for events table
  const schema = await db.execute({
    sql: `PRAGMA table_info(events)`,
    args: [],
  });
  console.log('\nEvents table columns:');
  schema.rows.forEach(r => console.log(`  ${r.cid}: ${r.name} (${r.type}) default=${r.dflt_value} notnull=${r.notnull}`));

  // Get all events
  const result = await db.execute({
    sql: `SELECT * FROM events ORDER BY id ASC`,
    args: [],
  });
  console.log(`\nAll events (${result.rows.length}):\n`);
  result.rows.forEach((e, i) => {
    console.log(JSON.stringify({ ...e }, null, 2));
  });

  await db.close();
}

main().catch(console.error);
