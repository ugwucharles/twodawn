const { query } = require('../client.cjs');

async function ensureActivityLogsSchema() {
  try {
    await query(`
      CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        action TEXT NOT NULL,
        entity_type TEXT NOT NULL,
        entity_id INTEGER,
        details TEXT,
        user_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Add indexes for better performance
    await query(`CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at)`);
    await query(`CREATE INDEX IF NOT EXISTS idx_activity_logs_entity ON activity_logs(entity_type, entity_id)`);
    await query(`CREATE INDEX IF NOT EXISTS idx_activity_logs_user ON activity_logs(user_id)`);

    console.log('✅ Activity logs schema ensured');
  } catch (error) {
    console.error('❌ Failed to ensure activity logs schema:', error);
    throw error;
  }
}

module.exports = { ensureActivityLogsSchema };
