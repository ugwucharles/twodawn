const https = require('https');

// Check event 11 details including slug
const SLUG = 'afterdarkhouseparty';

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

async function checkEvent() {
  try {
    console.log('🔍 Checking custom slug:', SLUG);
    
    const response = await makeRequest('GET', `/event/${SLUG}`);
    
    console.log('📊 Response status:', response.statusCode);
    console.log('📦 Event data:', JSON.stringify(response.data, null, 2));
    
    if (response.statusCode === 200 && response.data.ok) {
      const event = response.data.event;
      console.log('✅ Custom slug works!');
      console.log('📅 Title:', event.title);
      console.log('🔗 Slug:', event.slug || 'NOT SET');
      console.log('🎯 Use Custom Slug:', event.use_custom_slug);
      console.log('🌐 URL:', `https://twodawn.com.ng/event/${SLUG}`);
      console.log('📝 Must Know:', event.must_know || 'NOT SET');
    } else {
      console.log('❌ Custom slug not working');
    }
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

checkEvent();
