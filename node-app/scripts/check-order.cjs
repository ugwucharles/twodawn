const https = require('https');

// Check the order we just created
const REFERENCE = 'PA_482fd5b94fed7841';

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

async function checkOrder() {
  try {
    console.log('🔍 Checking order:', REFERENCE);
    
    const response = await makeRequest('GET', `/orders/${REFERENCE}`);
    
    console.log('📊 Response status:', response.statusCode);
    console.log('📦 Order data:', JSON.stringify(response.data, null, 2));
    
    if (response.statusCode === 200 && response.data.ok) {
      const order = response.data.order;
      console.log('✅ Order found!');
      console.log('🎫 Reference:', order.paystack_reference);
      console.log('👤 Buyer:', order.buyer_name);
      console.log('📧 Email:', order.buyer_email);
      console.log('🔗 Referral Source:', order.referral_source || 'NOT SET');
      console.log('🎫 Ticket Code:', order.ticket_code);
      console.log('💰 Amount:', order.amount);
      console.log('📊 Status:', order.status);
      
      if (order.referral_source) {
        console.log('✅ Referral data was saved successfully!');
      } else {
        console.log('❌ Referral data was NOT saved');
      }
    } else {
      console.log('❌ Order not found');
    }
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

checkOrder();
