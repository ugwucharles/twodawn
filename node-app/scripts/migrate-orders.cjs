require('../config/envLoader.cjs');
const { ensureOrdersSchema } = require('../db/ensureOrdersSchema.cjs');
const { query } = require('../db/client.cjs');

async function main() {
  await ensureOrdersSchema();
  const cols = await query('PRAGMA table_info(orders)');
  console.log('orders columns:', cols.map((r) => r.name).join(', '));
  const schema = await query("SELECT sql FROM sqlite_master WHERE type='table' AND name='orders'");
  console.log('orders ddl:', schema[0]?.sql);
}

main().catch((error) => {
  console.error('Migration failed:', error.message);
  process.exit(1);
});
