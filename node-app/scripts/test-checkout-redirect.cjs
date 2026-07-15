const https = require('https');

function testCheckoutAccess(url) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    
    const options = {
      hostname: urlObj.hostname,
      port: 443,
      path: urlObj.pathname + urlObj.search,
      method: 'GET',
      headers: {
        'User-Agent': 'Mozilla/5.0',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
      }
    };

    const req = https.request(options, (res) => {
      let body = '';
      res.on('data', (chunk) => {
        body += chunk;
      });
      res.on('end', () => {
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          location: res.headers.location,
          body: body.substring(0, 500) // First 500 chars
        });
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    req.end();
  });
}

async function testCheckoutRedirect() {
  try {
    console.log('🧪 Testing checkout access without referrer...');
    
    const response = await testCheckoutAccess('https://twodawn.com.ng/events/11/checkout');
    
    console.log('📊 Status Code:', response.statusCode);
    console.log('🔗 Location Header:', response.location || 'No redirect');
    console.log('📄 Body preview:', response.body);
    
    if (response.statusCode === 200) {
      // Check if it's the checkout page or event page
      if (response.body.includes('checkout') || response.body.includes('Checkout')) {
        console.log('❌ Checkout page loaded - bypass protection not working');
      } else if (response.body.includes('AFTER DARK') || response.body.includes('event')) {
        console.log('✅ Redirected to event page - bypass protection working');
      } else {
        console.log('⚠️ Unknown page content');
      }
    } else if (response.statusCode >= 300 && response.statusCode < 400) {
      console.log('✅ HTTP redirect detected!');
      console.log('🎯 Redirects to:', response.location);
    } else {
      console.log('⚠️ Unexpected status code');
    }
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

testCheckoutRedirect();
