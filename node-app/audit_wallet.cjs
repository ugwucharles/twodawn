'use strict';
process.chdir(__dirname);
require('dotenv').config({ path: './.env' });
const { query } = require('./db/client.cjs');

(async () => {
  console.log('=== WITHDRAWALS RAW ===');
  const w = await query('SELECT * FROM withdrawals');
  console.log(JSON.stringify(w, null, 2));

  console.log('\n=== SUM CHECK ===');
  const wRows = await query(`
    SELECT 
      COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as approved,
      COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending
    FROM withdrawals
    WHERE user_id = 9
  `);
  console.log(JSON.stringify(wRows, null, 2));

  console.log('\n=== REVENUE & FEE AUDIT ===');
  const orderRows = await query(`
    SELECT id, event_id, status, amount, quantity, created_at FROM orders 
    WHERE event_id IN (SELECT id FROM events WHERE user_id = 9 AND deleted_at IS NULL) AND status IN ('paid', 'used')
  `);
  console.log(JSON.stringify(orderRows, null, 2));

  let totalRevenue = 0;
  let twoDawnFee = 0;
  for (const row of orderRows) {
    const amt = Number(row.amount);
    totalRevenue += amt;
    if (amt > 0) {
      const fee = (amt * 0.10) + 10000;
      twoDawnFee += fee;
      console.log(`Order ID ${row.id}: amount=${amt}, fee=${fee}`);
    } else {
      console.log(`Order ID ${row.id}: amount=${amt}, free, fee=0`);
    }
  }
  console.log(`Total Revenue: ${totalRevenue} kobo (${totalRevenue / 100} Naira)`);
  console.log(`Total 2DAWN Fee: ${twoDawnFee} kobo (${twoDawnFee / 100} Naira)`);
})().catch(console.error);
