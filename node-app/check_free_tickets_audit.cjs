// Copy of the script, run from node-app directory so it can use its node_modules
process.chdir(__dirname);

const { createClient } = require('@libsql/client/web');
const fs = require('fs');
const path = require('path');

const TURSO_URL = 'libsql://twodawn-ugwucharles.aws-us-west-2.turso.io';
const TURSO_TOKEN = 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJpYXQiOjE3ODIwNzQ4MzksImlkIjoiMDE5ZWVhYmQtYWYwMS03OWU4LTk3ZTgtZjE1NmMwMTRhZWQ2IiwicmlkIjoiZWJmNDA1NTktMTBlYS00YWVkLTg1N2EtZmZiMjYxZDg0NGQyIn0.lzIxv7SszXjLK4oR124Bs0fblN-H92vdaHmH18uZFFU2qb6PEhILR12hQUpn61PX0gxxe895abDs_eJxjgweDA';

const MAIL_LOG = path.resolve(__dirname, '../../storage/logs/mail.log');

async function main() {
  const db = createClient({ url: TURSO_URL, authToken: TURSO_TOKEN });

  const result = await db.execute({
    sql: `SELECT id, buyer_name, buyer_email, amount, status, paystack_reference, created_at
          FROM orders
          WHERE amount = 0 AND status IN ('paid', 'confirmed')
          ORDER BY created_at`,
    args: [],
  });

  const freeOrders = result.rows.map(r => ({ ...r }));

  console.log(`\n=== FREE TICKET ORDERS IN DB (amount=0, status=paid/confirmed) ===`);
  console.log(`Total: ${freeOrders.length}`);

  let mailLog = '';
  try {
    mailLog = fs.readFileSync(MAIL_LOG, 'utf8');
  } catch (e) {
    console.log('(Could not read mail.log:', e.message, ')');
  }

  const loggedRefs = new Set();
  const refMatches = mailLog.matchAll(/Subject: Your Ticket for .+ - (.+)/g);
  for (const m of refMatches) {
    loggedRefs.add(m[1].trim());
  }

  console.log(`\nTotal email entries in mail.log: ${loggedRefs.size}`);

  console.log('\n=== FREE ORDER CROSS-CHECK ===');
  let allEmailed = true;
  for (const order of freeOrders) {
    const ref = String(order.paystack_reference || '').trim();
    const emailedByRef = loggedRefs.has(ref);
    const emailedByEmail = mailLog.includes(`To: ${order.buyer_email}`);
    const emailed = emailedByRef || emailedByEmail;
    if (!emailed) allEmailed = false;

    console.log(`\n  ID: ${order.id} | ${order.buyer_name} <${order.buyer_email}>`);
    console.log(`  Reference: ${ref}`);
    console.log(`  Amount: ${order.amount} | Status: ${order.status}`);
    console.log(`  Created: ${order.created_at}`);
    console.log(`  Email in log by ref: ${emailedByRef ? 'YES' : 'NO'} | by email addr: ${emailedByEmail ? 'YES' : 'NO'}`);
    console.log(`  => Email sent: ${emailed ? '✅ YES' : '❌ NO'}`);
  }

  if (freeOrders.length === 0) {
    console.log('  No free (amount=0) paid/confirmed orders in DB.');
  } else if (allEmailed) {
    console.log('\n✅ RESULT: All free ticket holders were emailed.');
  } else {
    console.log('\n❌ RESULT: Some free ticket holders were NOT emailed.');
  }

  await db.close();
}

main().catch(console.error);
