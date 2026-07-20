'use strict';
process.chdir('C:/Users/chief/Desktop/2DAWN/node-app');
require('dotenv').config({ path: './.env' });
const { query } = require('./db/client.cjs');

(async () => {
  const withdrawals = await query(`SELECT w.*, u.name as organizer_name FROM withdrawals w LEFT JOIN users u ON w.user_id = u.id`);
  console.log('All withdrawals:');
  console.log(JSON.stringify(withdrawals, null, 2));
})().catch(console.error);
