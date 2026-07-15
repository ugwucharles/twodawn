const { query } = require('../db/client.cjs');

async function checkEvent() {
  try {
    console.log('🔍 Checking if event 11 exists...');
    
    const event = await query(`
      SELECT id, title, is_published, slug, use_custom_slug 
      FROM events 
      WHERE id = 11
    `);
    
    if (event.length === 0) {
      console.log('❌ Event 11 does not exist in database');
    } else {
      console.log('✅ Event 11 exists:');
      console.log('  - ID:', event[0].id);
      console.log('  - Title:', event[0].title);
      console.log('  - Published:', event[0].is_published);
      console.log('  - Slug:', event[0].slug);
      console.log('  - Use Custom Slug:', event[0].use_custom_slug);
    }
    
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  }
}

checkEvent();
