const { query } = require('../db/client.cjs');

// Set environment variables for database connection
process.env.TURSO_DATABASE_URL = 'libsql://twodawn-ugwucharles.aws-us-west-2.turso.io';
process.env.TURSO_AUTH_TOKEN = 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJleHAiOjE3ODM0MzczNzYsImlhdCI6MTc4MzM1MDk3NiwiaWQiOiIwMTllZWFiZC1hZjAxLTc5ZTgtOTdlOC1mMTU2YzAxNGFlZDYiLCJraWQiOiJ1WUhoV3RmTlpueG16bzNMRjBCOUFZWUlSNXREYlpBVGs0TldmSGZtaFcwIiwicmlkIjoiZWJmNDA1NTktMTBlYS00YWVkLTg1N2EtZmZiMjYxZDg0NGQyIn0.W1iVq6SxEo8xBVb3vqLkcL-fiFaX7ibozg8Eiw5KPJ4GNrNP54K_5AYNJC6ABcdWjpwZtu0braI3ipWxV6CHBA';

async function publishEvent() {
  try {
    console.log('🔄 Publishing event 11...');
    
    await query(`
      UPDATE events 
      SET is_published = 1,
          updated_at = datetime('now')
      WHERE id = 11
    `);
    
    console.log('✅ Event 11 published successfully');
    
    // Verify
    const event = await query(`SELECT id, title, is_published FROM events WHERE id = 11`);
    console.log('📦 Event status:', event[0]);
    
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  }
}

publishEvent();
