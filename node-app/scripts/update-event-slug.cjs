const { query } = require('../db/client.cjs');

async function checkEventData() {
  try {
    console.log('� Checking event 11 data...');
    
    const event = await query('SELECT id, title, must_know, slug, use_custom_slug FROM events WHERE id = 11');
    console.log('📦 Event data:', event[0]);
    
    if (event[0] && !event[0].must_know) {
      console.log('❌ must_know is missing from database');
      console.log('💡 Adding must_know data...');
      
      await query(`
        UPDATE events 
        SET must_know = 'This tickets is only for GUEST…please leave a comment on who gave you access to this event link before purchasing the ticket, to gain entrance at the venue…😃',
            updated_at = datetime('now')
        WHERE id = 11
      `);
      
      console.log('✅ must_know added to event 11');
      
      // Verify
      const updated = await query('SELECT id, title, must_know FROM events WHERE id = 11');
      console.log('📦 Updated event data:', updated[0]);
    }
    
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  }
}

checkEventData();
