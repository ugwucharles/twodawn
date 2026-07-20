'use strict';
process.chdir(__dirname);
require('dotenv').config({ path: './.env' });

const { createClient } = require('@libsql/client/web');

async function main() {
  const db = createClient({
    url: process.env.TURSO_DATABASE_URL,
    authToken: process.env.TURSO_AUTH_TOKEN,
  });

  // Get ALL orders regardless of status
  const result = await db.execute({
    sql: `SELECT o.id, o.buyer_name, o.buyer_email, o.amount, o.status,
                 o.ticket_type, o.quantity, o.paystack_reference, o.created_at,
                 e.title AS event_title
          FROM orders o
          LEFT JOIN events e ON e.id = o.event_id
          ORDER BY o.id ASC`,
    args: [],
  });

  const rows = result.rows.map(r => ({ ...r }));

  // Count by status
  const byStatus = {};
  rows.forEach(r => { byStatus[r.status] = (byStatus[r.status] || 0) + 1; });

  console.log(`\nALL orders in DB: ${rows.length}`);
  console.log('By status:', JSON.stringify(byStatus));
  console.log('\n--- All Orders ---\n');

  rows.forEach((o, i) => {
    const amountNaira = o.amount > 0 ? `₦${(o.amount / 100).toFixed(2)}` : 'FREE';
    const isNew = o.id > 55 ? ' ⭐ NEW' : '';
    console.log(`[${i + 1}] ID:${o.id}${isNew} | ${o.buyer_name} <${o.buyer_email}>`);
    console.log(`     Ticket: ${o.ticket_type || 'General Admission'} x${o.quantity} | ${amountNaira} | STATUS: ${o.status}`);
    console.log(`     Date: ${o.created_at}\n`);
  });

  await db.close();
}

main().catch(console.error);
