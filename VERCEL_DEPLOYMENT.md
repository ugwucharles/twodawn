# Vercel Deployment Guide for 2DAWN

## Important Notes

**Laravel on Vercel requires special configuration** since Vercel is primarily designed for Node.js applications. This project has been configured with Vercel's PHP runtime support.

## Prerequisites

1. Vercel account
2. GitHub repository connected to Vercel
3. Environment variables configured in Vercel

## Deployment Steps

### 1. Connect Repository to Vercel

1. Go to [vercel.com](https://vercel.com)
2. Click "Add New Project"
3. Import your GitHub repository (ugwucharles/2DAWN)
4. Vercel will automatically detect the `vercel.json` configuration

### 2. Configure Environment Variables

In your Vercel project settings, add these environment variables:

**Required:**
```
APP_NAME=2DAWN
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-laravel-app-key
APP_URL=https://your-project.vercel.app
```

**Database (choose one):**
```
# For SQLite (simplest for Vercel)
DB_CONNECTION=sqlite

# OR for PostgreSQL (recommended for production)
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

**Storage (choose one):**
```
# Option 1: Cloudinary (recommended for image uploads)
CLOUDINARY_URL=cloudinary://your-cloudinary-url
FILESYSTEM_DISK=cloudinary

# Option 2: S3-compatible storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com

# Option 3: Local storage (limited on Vercel)
FILESYSTEM_DISK=public
```

**Payment (Paystack):**
```
PAYSTACK_PUBLIC_KEY=your-paystack-public-key
PAYSTACK_SECRET_KEY=your-paystack-secret-key
PAYSTACK_CALLBACK_URL=https://your-project.vercel.app/paystack/callback
```

**Mail:**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@yourdomain.com
MAIL_FROM_NAME="2DAWN"
```

### 3. Generate APP_KEY

Run this command locally to generate an APP_KEY:
```bash
php artisan key:generate
```

Copy the generated key and add it to your Vercel environment variables.

### 4. Deploy

1. Click "Deploy" in Vercel
2. Wait for the build to complete
3. Vercel will provide a deployment URL

### 5. Post-Deployment Setup

Since Vercel's file system is ephemeral, you'll need to:

1. **Run migrations**: You may need to run migrations manually if using a database
2. **Storage link**: The storage:link command may not work on Vercel, so use Cloudinary or S3 for file storage
3. **Queue worker**: Configure a queue worker if you need background jobs

## Limitations of Vercel Deployment

- **No persistent file system**: Use Cloudinary, S3, or similar for file storage
- **Limited PHP support**: Vercel's PHP runtime is experimental
- **No SSH access**: You cannot SSH into Vercel deployments
- **Queue workers**: Requires additional configuration for background jobs

## Alternative Deployment Options

For a more traditional Laravel deployment, consider:

1. **Laravel Forge** + **Laravel Vapor** (AWS Lambda)
2. **DigitalOcean** (Droplet or App Platform)
3. **Railway** (better PHP support)
4. **Render** (your current deployment)
5. **Heroku** (good PHP support)

## Troubleshooting

### Images Not Uploading
- Ensure Cloudinary or S3 credentials are set
- Check that `FILESYSTEM_DISK` is set correctly
- Verify storage permissions

### Database Connection Issues
- Check database credentials in Vercel environment variables
- Ensure database is accessible from Vercel's network
- For SQLite, ensure the database file is in the correct location

### Build Failures
- Check build logs in Vercel dashboard
- Ensure all dependencies are in composer.json
- Verify PHP version compatibility (requires PHP 8.2+)

## Recommended Production Setup

For best results, consider using:

1. **Vercel for frontend** (static assets, API routes)
2. **Separate PHP hosting** for the Laravel backend
3. **Cloudinary** for image storage
4. **PostgreSQL** for database (Neon, Supabase, or Railway)

## Support

If you encounter issues:
- Check Vercel build logs
- Review Laravel logs (if accessible)
- Ensure all environment variables are set correctly
- Consider alternative deployment platforms if Vercel's PHP support is insufficient
