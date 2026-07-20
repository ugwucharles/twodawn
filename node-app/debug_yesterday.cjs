'use strict';
process.chdir('C:/Users/chief/Desktop/2DAWN/node-app');
require('dotenv').config({ path: './.env' });
const { query } = require('./db/client.cjs');

(async () => {
  // Yesterday in WAT = July 18, 2026
  const rows = await query(`
    SELECT 
      o.id,
      o.buyer_name,
      o.buyer_email,
      o.quantity,
      o.amount,
      o.status,
      o.created_at,
      e.title as event_title
    FROM orders o
    JOIN events e ON e.id = o.event_id
    WHERE date(o.created_at) = '2026-07-18'
      AND o.status IN ('paid', 'used')
    ORDER BY o.created_at DESC
  `);

  let totalKobo = 0;
  let totalTickets = 0;
  console.log('\n📅 Orders from July 18, 2026 (Yesterday):\n');
  for (const r of rows) {
    const amt = Number(r.amount);
    totalKobo += amt;
    totalTickets += Number(r.quantity);
    console.log(`  #${r.id} | ${r.buyer_name} | ${r.quantity} ticket(s) | ₦${(amt/100).toLocaleString()} | ${r.status} | ${r.event_title}`);
  }

  console.log(`\n🎟️  Total tickets sold: ${totalTickets}`);
  console.log(`💰 Total revenue: ₦${(totalKobo/100).toLocaleString()}`);

  // Also check all orders regardless of date
  const all = await query(`SELECT date(created_at) as day, COUNT(*) as cnt, SUM(amount) as total FROM orders WHERE status IN ('paid','used') GROUP BY day ORDER BY day DESC`);
  console.log('\n📊 All orders by day:');
  for (const d of all) console.log(`  ${d.day}: ${d.cnt} orders, ₦${(Number(d.total)/100).toLocaleString()}`);
})().catch(console.error);
