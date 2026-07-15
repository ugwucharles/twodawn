const https = require('https');

// Get orders for event 11 first
const EVENT_ID = 11;

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

async function clearEventOrders() {
  try {
    console.log('🔍 Checking orders for event 11...');
    
    // Get orders from organizer endpoint (this requires auth, so might fail)
    const response = await makeRequest('GET', `/organizer/events/${EVENT_ID}`);
    
    console.log('📊 Response status:', response.statusCode);
    
    if (response.statusCode === 401) {
      console.log('❌ Authentication required - cannot delete orders via API without auth');
      console.log('💡 You need to delete orders manually through the organizer dashboard or provide database access');
      return;
    }
    
    if (response.statusCode !== 200) {
      console.log('❌ Failed to fetch event orders');
      return;
    }
    
    const event = response.data.event;
    const orders = response.data.orders || [];
    
    console.log(`📦 Found ${orders.length} orders for event 11:`);
    orders.forEach(order => {
      console.log(`  - ID: ${order.id}, Ref: ${order.paystack_reference}, Buyer: ${order.buyer_name}, Status: ${order.status}`);
    });
    
    if (orders.length === 0) {
      console.log('✅ No orders to delete');
      return;
    }
    
    console.log('⚠️ To delete orders, you need to:');
    console.log('1. Go to the organizer dashboard');
    console.log('2. Navigate to the event details');
    console.log('3. Delete orders individually, or');
    console.log('4. Provide database access credentials for direct deletion');
    
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

clearEventOrders();
