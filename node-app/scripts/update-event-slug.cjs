const { query } = require('../db/client.cjs');

async function updateEventSlug() {
  try {
    console.log('🔄 Updating event 11 custom slug...');
    
    await query(`
      UPDATE events 
      SET slug = 'afterdarkhouseparty', 
          use_custom_slug = 1,
          updated_at = datetime('now')
      WHERE id = 11
    `);
    
    console.log('✅ Event 11 updated successfully');
    console.log('🔗 New URL: https://twodawn.com.ng/event/afterdarkhouseparty');
    
    // Verify the update
    const event = await query('SELECT id, title, slug, use_custom_slug FROM events WHERE id = 11');
    console.log('📦 Event data:', event[0]);
    
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  }
}

updateEventSlug();
