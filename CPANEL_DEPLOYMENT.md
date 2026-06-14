# cPanel Laravel Deployment Guide

## Prerequisites
- cPanel hosting account
- SSH access (optional but recommended)
- Database credentials

## Step 1: Prepare Laravel for Production

```bash
# In your local project
cp .env .env.backup
cp .env.example .env.production
```

Update `.env.production` with your cPanel database credentials:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

## Step 2: Upload Files to cPanel

### Option A: Using File Manager
1. Log in to cPanel
2. Go to File Manager
3. Navigate to `public_html`
4. Upload all Laravel files EXCEPT:
   - `.git` folder
   - `node_modules` folder
   - `.env` file (use .env.production instead)
   - `storage` folder (keep structure but clear cache)

### Option B: Using Git (Recommended)
```bash
# SSH into your cPanel server
ssh username@yourdomain.com

# Navigate to public_html
cd public_html

# Clone your repository
git clone https://github.com/ugwucharles/2DAWN.git .

# Or if already cloned
git pull origin main
```

## Step 3: Set File Permissions

```bash
# SSH into server
cd public_html

# Set proper permissions
chmod -R 755 .
chmod -R 777 storage bootstrap/cache

# Copy environment file
cp .env.production .env
```

## Step 4: Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 5: Create Database

1. Log in to cPanel
2. Go to "MySQL Database Wizard"
3. Create a new database
4. Create a database user
5. Grant all privileges to the user
6. Note down the database name, username, and password

## Step 6: Update Database Configuration

Edit `.env` file with your cPanel database credentials:
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

## Step 7: Run Migrations

```bash
php artisan migrate --force
php artisan db:seed --force
```

## Step 8: Configure Document Root

1. In cPanel, go to "Subdomains" or "Domains"
2. Set document root to: `public_html/public`
3. This ensures Laravel's public folder is the web root

## Step 9: Test Your Application

Visit your domain and verify:
- Homepage loads correctly
- Routes work
- Database connections work
- File uploads work

## Troubleshooting

### 500 Internal Server Error
- Check storage permissions: `chmod -R 777 storage bootstrap/cache`
- Check .env file exists and has correct permissions
- Check error logs in `storage/logs/laravel.log`

### Database Connection Error
- Verify database credentials in .env
- Ensure database user has proper privileges
- Check database exists

### Asset Loading Issues
- Run: `php artisan storage:link`
- Check public folder permissions

## Security Tips

1. Keep Laravel updated
2. Use strong database passwords
3. Enable HTTPS (SSL certificate)
4. Restrict file uploads
5. Regular backups

## Common cPanel Issues

**"Weird" cPanel behavior:**
- Use File Manager instead of FTP for large files
- Enable SSH access for easier command-line operations
- Check PHP version in "Select PHP Version" (requires PHP 8.0+)
- Clear browser cache after deployment

**Performance optimization:**
- Enable OPcache in PHP settings
- Use Redis if available
- Configure proper caching
- Optimize images
