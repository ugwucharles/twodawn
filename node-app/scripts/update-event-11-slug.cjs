const https = require('https');

// Update event 11 with custom slug
const EVENT_ID = 11;

const updateData = {
  custom_slug: 'afterdarkhouseparty',
  use_custom_slug: true
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

async function updateEventSlug() {
  try {
    console.log('🔄 Updating event 11 custom slug...');
    console.log('📝 Update data:', JSON.stringify(updateData, null, 2));
    
    const response = await makeRequest('PATCH', `/organizer/events/${EVENT_ID}`, updateData);
    
    console.log('📊 Response status:', response.statusCode);
    console.log('📦 Response data:', JSON.stringify(response.data, null, 2));
    
    if (response.statusCode === 200 && response.data.ok) {
      console.log('✅ Event updated successfully!');
      console.log('🔗 New URL: https://twodawn.com.ng/event/afterdarkhouseparty');
    } else {
      console.log('❌ Update failed');
    }
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

updateEventSlug();
