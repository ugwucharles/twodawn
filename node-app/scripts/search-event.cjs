require('dotenv').config();
const { query } = require('../db/client.cjs');

async function searchEvent() {
  try {
    // Search for all events to see what's in the database
    const events = await query(`
      SELECT id, title, slug, venue, state, starts_at, ends_at, price, is_published, deleted_at, image_path
      FROM events 
      ORDER BY created_at DESC
      LIMIT 20
    `);
    
    console.log(`Found ${events.length} events in database (showing last 20):`);
    console.log('');
    
    if (events.length === 0) {
      console.log('No events found in database.');
    } else {
      events.forEach((event, index) => {
        console.log(`${index + 1}. ID: ${event.id}`);
        console.log(`   Title: ${event.title}`);
        console.log(`   Slug: ${event.slug}`);
        console.log(`   Venue: ${event.venue || 'N/A'}`);
        console.log(`   State: ${event.state || 'N/A'}`);
        console.log(`   Start: ${event.starts_at || 'N/A'}`);
        console.log(`   End: ${event.ends_at || 'N/A'}`);
        console.log(`   Price: ${event.price ? '₦' + event.price : 'Free'}`);
        console.log(`   Published: ${event.is_published ? 'Yes' : 'No'}`);
        console.log(`   Deleted: ${event.deleted_at ? 'Yes' : 'No'}`);
        console.log(`   Image: ${event.image_path || 'N/A'}`);
        console.log('');
      });
    }
    
    process.exit(0);
  } catch (error) {
    console.error('Error searching for event:', error);
    process.exit(1);
  }
}

searchEvent();
