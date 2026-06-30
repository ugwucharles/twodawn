/**
 * clean-db.cjs
 * Deletes all events, organizer users (non-admin), orders, and activity logs
 * from the Turso database. Keeps only the admin user account.
 *
 * Usage: node scripts/clean-db.cjs
 */

const path = require('path');
const nodeAppDir = path.join(__dirname, '../node-app');

// Load env from root .env
require(path.join(nodeAppDir, 'node_modules/dotenv')).config({
  path: path.join(__dirname, '../.env')
});

// Reuse the existing db client
const { query } = require(path.join(nodeAppDir, 'db/client.cjs'));

const TURSO_DATABASE_URL = process.env.TURSO_DATABASE_URL;
const TURSO_AUTH_TOKEN = process.env.TURSO_AUTH_TOKEN;

if (!TURSO_DATABASE_URL || !TURSO_AUTH_TOKEN) {
  console.error('❌  TURSO_DATABASE_URL or TURSO_AUTH_TOKEN not set in .env');
  process.exit(1);
}

async function run(sql, args = []) {
  return query(sql, args);
}

async function main() {
  console.log('🔌  Connected to Turso:', TURSO_DATABASE_URL);

  // Identify the admin user so we don't delete it
  const adminRows = await run(`SELECT id, email, is_admin FROM users WHERE is_admin = 1 LIMIT 5`);
  if (!Array.isArray(adminRows) || adminRows.length === 0) {
    console.warn('⚠️   No admin user found (is_admin=1). Aborting to avoid data loss.');
    process.exit(1);
  }
  console.log('✅  Admin accounts that will be KEPT:');
  adminRows.forEach(r => console.log(`   id=${r.id}  email=${r.email}`));

  const adminIds = adminRows.map(r => String(r.id));
  const placeholders = adminIds.map(() => '?').join(', ');

  // ── 1. Delete all orders (tickets / transactions)
  const orders = await run('DELETE FROM orders');
  console.log(`🗑   orders deleted: ${orders.affectedRows ?? '?'}`);

  // ── 2. Delete ticket_types if table exists
  try {
    const tt = await run('DELETE FROM ticket_types');
    console.log(`🗑   ticket_types deleted: ${tt.affectedRows ?? '?'}`);
  } catch (_) { /* table may not exist */ }

  // ── 3. Delete all events
  const events = await run('DELETE FROM events');
  console.log(`🗑   events deleted: ${events.affectedRows ?? '?'}`);

  // ── 4. Delete non-admin users (organizers + regular users)
  const users = await run(
    `DELETE FROM users WHERE id NOT IN (${placeholders})`,
    adminIds
  );
  console.log(`🗑   users (organizers + public) deleted: ${users.affectedRows ?? '?'}`);

  // ── 5. Clear activity logs
  try {
    const logs = await run('DELETE FROM activity_logs');
    console.log(`🗑   activity_logs deleted: ${logs.affectedRows ?? '?'}`);
  } catch (_) { /* table may not exist */ }

  // ── 6. Clear sessions
  try {
    const sess = await run('DELETE FROM sessions');
    console.log(`🗑   sessions deleted: ${sess.affectedRows ?? '?'}`);
  } catch (_) { /* table may not exist */ }

  // Verify
  const remaining = await run('SELECT id, email, is_admin FROM users');
  console.log('\n✅  Remaining users after cleanup:');
  (Array.isArray(remaining) ? remaining : []).forEach(r =>
    console.log(`   id=${r.id}  email=${r.email}  is_admin=${r.is_admin}`)
  );

  console.log('\n🎉  Database cleaned successfully.');
  process.exit(0);
}

main().catch(err => {
  console.error('❌  Error during cleanup:', err.message);
  process.exit(1);
});
