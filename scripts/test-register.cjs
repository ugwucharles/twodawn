const http = require('http');

const data = JSON.stringify({
  name: 'Test User',
  email: `test${Date.now()}@example.com`,
  password: 'Test123456',
  password_confirmation: 'Test123456'
});

const options = {
  hostname: 'localhost',
  port: 3001,
  path: '/organizer/register',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': data.length
  }
};

const req = http.request(options, (res) => {
  console.log(`Status: ${res.statusCode}`);
  console.log(`Headers: ${JSON.stringify(res.headers)}`);
  
  let body = '';
  res.on('data', (chunk) => {
    body += chunk;
  });
  
  res.on('end', () => {
    console.log('Response body:', body);
  });
});

req.on('error', (error) => {
  console.error('Error:', error.message);
});

req.write(data);
req.end();
