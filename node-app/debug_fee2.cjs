'use strict';
process.chdir('C:/Users/chief/Desktop/2DAWN/node-app');
require('dotenv').config({ path: './.env' });
const { query } = require('./db/client.cjs');

(async () => {
  const rows = await query(`
    SELECT id, buyer_name, amount, quantity FROM orders 
    WHERE event_id IN (SELECT id FROM events WHERE user_id = 9 AND (deleted_at IS NULL OR deleted_at = '')) 
    AND status IN ('paid','used')
    ORDER BY id
  `);

  let totalRevenue = 0, totalFee = 0;
  console.log('\nOrder breakdown (₦100 per ticket):\n');
  for (const r of rows) {
    const amt = Number(r.amount);
    const qty = Number(r.quantity) || 1;
    totalRevenue += amt;
    if (amt > 0) {
      const fee = (amt * 0.10) + (10000 * qty);
      totalFee += fee;
      console.log(`  #${r.id} | ${r.buyer_name} | qty=${qty} | amt=₦${amt/100} | fee=₦${fee/100}`);
    }
  }

  const net = totalRevenue - totalFee;
  const pending = 130000;
  console.log(`\nGross Revenue:   ₦${(totalRevenue/100).toLocaleString()}`);
  console.log(`2DAWN Fees:     -₦${(totalFee/100).toLocaleString()}`);
  console.log(`Net Payout:      ₦${(net/100).toLocaleString()}`);
  console.log(`Pending w/draw: -₦${pending.toLocaleString()}`);
  console.log(`Wallet Balance:  ₦${((net/100) - pending).toLocaleString()}`);
})().catch(console.error);
