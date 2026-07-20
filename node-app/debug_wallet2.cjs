'use strict';
process.chdir('C:/Users/chief/Desktop/2DAWN/node-app');
require('dotenv').config({ path: './.env' });
const { query } = require('./db/client.cjs');

(async () => {
  const events = await query(`SELECT starts_at, ends_at, title FROM events WHERE user_id = 9 AND (deleted_at IS NULL OR deleted_at = '')`);
  console.log('Events:', JSON.stringify(events, null, 2));

  const now = await query(`SELECT datetime('now') as utc_now, datetime('now', '+1 hours') as wat_now`);
  console.log('UTC now:', now[0].utc_now, '| WAT now (UTC+1):', now[0].wat_now);

  const up1 = await query(`SELECT COUNT(*) as cnt FROM events WHERE user_id = 9 AND starts_at > datetime('now') AND (deleted_at IS NULL OR deleted_at = '')`);
  console.log('Upcoming (UTC compare):', up1[0].cnt);

  const up2 = await query(`SELECT COUNT(*) as cnt FROM events WHERE user_id = 9 AND starts_at > datetime('now', '+1 hours') AND (deleted_at IS NULL OR deleted_at = '')`);
  console.log('Upcoming (WAT +1h offset):', up2[0].cnt);

  // Wallet audit
  const orders = await query(`SELECT id, amount FROM orders WHERE event_id IN (SELECT id FROM events WHERE user_id = 9 AND (deleted_at IS NULL OR deleted_at = '')) AND status IN ('paid','used')`);
  let rev = 0, fee = 0;
  for (const o of orders) {
    const amt = Number(o.amount);
    rev += amt;
    if (amt > 0) fee += (amt * 0.10) + 10000;
  }
  console.log('\nRevenue (kobo):', rev, '=', rev/100, 'NGN');
  console.log('Fee (kobo):', fee, '=', fee/100, 'NGN');
  console.log('Net NGN (after fee):', (rev - fee) / 100);

  const wdraw = await query(`SELECT status, amount FROM withdrawals WHERE user_id = 9`);
  console.log('Withdrawals:', JSON.stringify(wdraw, null, 2));

  const pendingTotal = wdraw.filter(w => w.status === 'pending').reduce((a, w) => a + Number(w.amount), 0);
  const approvedTotal = wdraw.filter(w => w.status === 'approved').reduce((a, w) => a + Number(w.amount), 0);
  const netBalance = (rev - fee) / 100 - approvedTotal - pendingTotal;
  console.log('\nExpected wallet balance:', netBalance);
})().catch(console.error);
