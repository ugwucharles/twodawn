const https = require('https');

function testMetaTags(url) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    
    const options = {
      hostname: urlObj.hostname,
      port: 443,
      path: urlObj.pathname + urlObj.search,
      method: 'GET',
      headers: {
        'User-Agent': 'facebookexternalhit/1.1'
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
          body: body
        });
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    req.end();
  });
}

async function testSocialMetaTags() {
  try {
    console.log('🧪 Testing meta tags for event page (Frontend with crawler UA)...');
    
    const response = await testMetaTags('https://twodawn.com.ng/event/afterdarkhouseparty');
    
    console.log('📊 Status Code:', response.statusCode);
    
    // Check for Open Graph tags
    const ogTitle = response.body.match(/<meta property="og:title" content="([^"]*)"/);
    const ogImage = response.body.match(/<meta property="og:image" content="([^"]*)"/);
    const ogDescription = response.body.match(/<meta property="og:description" content="([^"]*)"/);
    const twitterImage = response.body.match(/<meta name="twitter:image" content="([^"]*)"/);
    
    console.log('\n📋 Open Graph Tags:');
    console.log('  Title:', ogTitle ? ogTitle[1] : '❌ NOT FOUND');
    console.log('  Image:', ogImage ? ogImage[1] : '❌ NOT FOUND');
    console.log('  Description:', ogDescription ? ogDescription[1].substring(0, 50) + '...' : '❌ NOT FOUND');
    
    console.log('\n🐦 Twitter Tags:');
    console.log('  Image:', twitterImage ? twitterImage[1] : '❌ NOT FOUND');
    
    if (ogTitle && ogImage) {
      console.log('\n✅ Meta tags are properly set for social sharing on frontend');
      console.log('💡 WhatsApp should now show the event flyer when links are shared');
    } else {
      console.log('\n❌ Meta tags not properly configured on frontend');
      console.log('💡 Vercel rewrite might not be working yet - WhatsApp caches link previews for 24-48 hours');
    }
    
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

testSocialMetaTags();
