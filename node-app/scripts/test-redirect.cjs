const https = require('https');

function testRedirect(url) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    
    const options = {
      hostname: urlObj.hostname,
      port: 443,
      path: urlObj.pathname + urlObj.search,
      method: 'GET',
      headers: {
        'User-Agent': 'Mozilla/5.0',
        'Accept': 'application/json'
      }
    };

    const req = https.request(options, (res) => {
      resolve({
        statusCode: res.statusCode,
        headers: res.headers,
        location: res.headers.location
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    req.end();
  });
}

async function testRedirects() {
  try {
    console.log('🧪 Testing redirect from /events/11 to custom slug (API)...');
    
    const response = await testRedirect('https://api.twodawn.com.ng/events/11');
    
    console.log('📊 Status Code:', response.statusCode);
    console.log('🔗 Location Header:', response.location || 'No redirect');
    
    if (response.statusCode === 200) {
      console.log('❌ No redirect - API returns 200');
    } else if (response.statusCode >= 300 && response.statusCode < 400) {
      console.log('✅ Redirect detected!');
      console.log('🎯 Redirects to:', response.location);
    } else {
      console.log('⚠️ Unexpected status code');
    }
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

testRedirects();
