require('dotenv').config();
const cloudinary = require('cloudinary').v2;
const path = require('path');

// Configure Cloudinary
cloudinary.config({
  cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
  api_key: process.env.CLOUDINARY_API_KEY,
  api_secret: process.env.CLOUDINARY_API_SECRET
});

async function uploadToCloudinary() {
  try {
    const imagePath = path.join(__dirname, '../../frontend/public/storage/events/1781974607786-chrome-sessions.jpg');
    
    console.log('Uploading image to Cloudinary...');
    console.log('Image path:', imagePath);
    
    const result = await cloudinary.uploader.upload(imagePath, {
      folder: 'events',
      public_id: 'chrome-sessions',
      resource_type: 'image'
    });
    
    console.log('✅ Image uploaded successfully!');
    console.log('URL:', result.secure_url);
    console.log('Public ID:', result.public_id);
    
    process.exit(0);
  } catch (error) {
    console.error('Error uploading to Cloudinary:', error);
    process.exit(1);
  }
}

uploadToCloudinary();
