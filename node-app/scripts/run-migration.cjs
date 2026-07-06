const { ensureOrdersSchema } = require('../db/ensureOrdersSchema.cjs');

async function runMigration() {
  try {
    console.log('🔄 Running orders schema migration...');
    await ensureOrdersSchema();
    console.log('✅ Migration completed successfully');
    process.exit(0);
  } catch (error) {
    console.error('❌ Migration failed:', error);
    process.exit(1);
  }
}

runMigration();
