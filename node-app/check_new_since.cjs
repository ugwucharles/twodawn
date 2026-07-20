'use strict';
process.chdir(__dirname);
require('dotenv').config({ path: './.env' });

const { createClient } = require('@libsql/client/web');

async function main() {
  const db = createClient({
    url: process.env.TURSO_DATABASE_URL,
    authToken: process.env.TURSO_AUTH_TOKEN,
  });

  const lastKnown = '2026-07-17 08:32:03'; // timestamp of last known order

  const result = await db.execute({
    sql: `SELECT o.id, o.buyer_name, o.buyer_email, o.amount, o.status, o.created_at, e.title AS event_title
          FROM orders o
          LEFT JOIN events e ON e.id = o.event_id
          WHERE o.created_at > ?
          ORDER BY o.id ASC`,
    args: [lastKnown],
  });

  const rows = result.rows.map(r => ({ ...r }));
  console.log(`\nNew purchases since ${lastKnown}: ${rows.length}\n`);
  rows.forEach((o, i) => {
    const amount = o.amount > 0 ? `₦${(o.amount/100).toFixed(2)}` : 'FREE';
    console.log(`[${i+1}] ID:${o.id} | ${o.buyer_name} <${o.buyer_email}>`);
    console.log(`    Event: ${o.event_title}`);
    console.log(`    Amount: ${amount} | Status: ${o.status}`);
    console.log(`    Date: ${o.created_at}\n`);
  });

  await db.close();
}

main().catch(console.error);
