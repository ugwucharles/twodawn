const https = require('https');

// Test purchase for event 11 with referral data
const API_URL = 'https://api.twodawn.com.ng';
const EVENT_ID = 11;

const orderData = {
  buyer_name: 'Test User',
  buyer_email: `test${Date.now()}@example.com`,
  buyer_phone: '08012345678',
  quantity: 1,
  ticket_type: 'General',
  referral_source: 'Test Referral John Doe'
};

function makeRequest(method, path, data) {
  return new Promise((resolve, reject) => {
    const postData = data ? JSON.stringify(data) : null;
    
    const options = {
      hostname: 'api.twodawn.com.ng',
      port: 443,
      path: path,
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    };

    if (postData) {
      options.headers['Content-Length'] = Buffer.byteLength(postData);
    }

    const req = https.request(options, (res) => {
      let body = '';
      
      res.on('data', (chunk) => {
        body += chunk;
      });
      
      res.on('end', () => {
        try {
          const response = JSON.parse(body);
          resolve({ statusCode: res.statusCode, data: response });
        } catch (e) {
          resolve({ statusCode: res.statusCode, data: body });
        }
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    if (postData) {
      req.write(postData);
    }
    req.end();
  });
}

async function testPurchase() {
  try {
    // First check event details
    console.log('🔍 Checking event 11 details...');
    const eventResponse = await makeRequest('GET', `/api/v1/events/${EVENT_ID}`);
    console.log('📊 Event response status:', eventResponse.statusCode);
    console.log('📦 Event data:', JSON.stringify(eventResponse.data, null, 2));
    
    if (eventResponse.statusCode !== 200 || !eventResponse.data.ok) {
      console.log('❌ Event not found or not accessible');
      return;
    }

    const event = eventResponse.data.event;
    console.log('📅 Event dates:', {
      starts_at: event.starts_at,
      ends_at: event.ends_at,
      is_published: event.is_published
    });

    // Check if event is in the past
    const now = new Date();
    const endsAt = event.ends_at ? new Date(event.ends_at) : null;
    const startsAt = event.starts_at ? new Date(event.starts_at) : null;
    const isPast = (endsAt && endsAt < now) || (!endsAt && startsAt && startsAt < now);
    
    console.log('⏰ Current time:', now.toISOString());
    console.log('🚫 Is event in the past?', isPast);
    
    if (isPast) {
      console.log('❌ Cannot purchase - event is in the past');
      return;
    }

    console.log('🎫 Testing purchase for event 11...');
    console.log('📝 Order data:', JSON.stringify(orderData, null, 2));
    
    const response = await makeRequest('POST', `/events/${EVENT_ID}/orders`, orderData);
    
    console.log('📊 Response status:', response.statusCode);
    console.log('📦 Response data:', JSON.stringify(response.data, null, 2));
    
    if (response.statusCode === 200 && response.data.ok) {
      console.log('✅ Purchase successful!');
      console.log('🎫 Reference:', response.data.reference);
      console.log('🔗 Authorization URL:', response.data.authorization_url || 'N/A (free ticket)');
    } else {
      console.log('❌ Purchase failed');
    }
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

testPurchase();
