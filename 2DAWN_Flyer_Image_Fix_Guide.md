# 2DAWN Flyer Image Fix Guide

## Problem Description
Party flyer images are broken after redeploy. This happens because:

1. **Storage Configuration Mismatch**: Images are stored in different locations (Cloudinary, S3, local) but URL generation doesn't handle all cases properly
2. **Deployment Environment Changes**: Storage configuration changes between deployments
3. **Missing Storage Links**: The `storage:link` command may not work properly with S3 storage

## Root Cause Analysis

### Current Image Storage Strategy
- **Primary**: Cloudinary (CDN with automatic optimization)
- **Fallback**: Local storage (`storage/app/public/events/`)
- **Production**: S3-compatible storage (AWS S3, Cloudflare R2, DigitalOcean Spaces)

### Issues Identified
1. **Event Model URL Generation**: The `getImageUrlAttribute()` method doesn't properly handle S3 URLs
2. **Mixed Storage Paths**: Images stored in different locations have inconsistent URL generation
3. **Deployment Script**: `storage:link` command doesn't work with S3 storage

## Fixes Applied

### 1. Enhanced Event Model (`app/Models/Event.php`)
```php
public function getImageUrlAttribute(): ?string
{
    if (empty($this->image_path)) {
        return null;
    }
    
    // If it's already a full URL (Cloudinary), return as-is
    if (str_starts_with($this->image_path, 'http')) {
        return $this->image_path;
    }
    
    // For local storage paths, use Storage::url() which handles both local and S3
    try {
        return Storage::url($this->image_path);
    } catch (\Exception $e) {
        // Fallback: if Storage::url() fails, try to construct URL manually
        $disk = config('filesystems.default', 'public');
        if ($disk === 's3') {
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');
            $endpoint = config('filesystems.disks.s3.endpoint');
            
            if ($endpoint) {
                // Custom endpoint (Cloudflare R2, DigitalOcean Spaces, etc.)
                return rtrim($endpoint, '/') . '/' . $bucket . '/' . $this->image_path;
            } else {
                // Standard AWS S3
                return "https://{$bucket}.s3.{$region}.amazonaws.com/{$this->image_path}";
            }
        } else {
            // Local storage fallback
            return url('storage/' . $this->image_path);
        }
    }
}
```

### 2. Improved Event Controller (`app/Http/Controllers/Admin/EventController.php`)
```php
if ($request->hasFile('image')) {
    try {
        // Try Cloudinary first (production preferred)
        $upload = Cloudinary::uploadFile($request->file('image')->getRealPath(), ['folder' => '2dawn/events']);
        $data['image_path'] = $upload->getSecurePath();
    } catch (\Throwable $e) {
        // Fallback to configured storage disk
        $data['image_path'] = $request->file('image')->storePublicly('events');
    }
}
```

### 3. Enhanced S3 Configuration (`config/filesystems.php`)
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'visibility' => 'public',
    'throw' => false,
    'report' => false,
    'options' => [
        'ACL' => 'public-read',
    ],
],
```

### 4. Image Fix Command (`app/Console/Commands/FixEventImages.php`)
Created a command to fix existing broken image URLs:
```bash
# Dry run to see what would be fixed
php artisan images:fix-events --dry-run

# Actually fix the images
php artisan images:fix-events
```

## Deployment Checklist

### Environment Variables Required
```bash
# Core
APP_NAME=2DAWN
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-SERVICE.onrender.com

# Storage Configuration
FILESYSTEM_DISK=s3  # or 'public' for local storage

# S3 Configuration (choose one provider)
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com
AWS_ENDPOINT=  # leave empty for AWS S3
AWS_USE_PATH_STYLE_ENDPOINT=false

# Cloudflare R2 Example
AWS_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true

# DigitalOcean Spaces Example
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
AWS_URL=https://your-bucket.nyc3.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Post-Deployment Steps
1. **Run Image Fix Command**:
   ```bash
   php artisan images:fix-events
   ```

2. **Test Image Upload**: Upload a new event image to verify the fix

3. **Verify Existing Images**: Check that existing event images load properly

4. **Check Storage Configuration**:
   ```bash
   php artisan config:show filesystems
   ```

## Troubleshooting

### Images Still Not Loading
1. **Check Environment Variables**: Ensure all S3/Cloudinary variables are set
2. **Verify Bucket Permissions**: Ensure S3 bucket allows public read access
3. **Test Storage Connection**:
   ```bash
   php artisan tinker
   >>> Storage::disk('s3')->put('test.txt', 'test content');
   >>> Storage::disk('s3')->url('test.txt');
   ```

### Cloudinary Issues
1. **Check Cloudinary Configuration**: Verify `CLOUDINARY_URL` environment variable
2. **Test Cloudinary Upload**:
   ```bash
   php artisan tinker
   >>> Cloudinary::uploadFile(public_path('favicon.ico'), ['folder' => 'test']);
   ```

### Local Development
For local development, set:
```bash
FILESYSTEM_DISK=public
APP_URL=http://localhost:8000
```

## Prevention Measures

### 1. Consistent Storage Strategy
- Always use Cloudinary for production image uploads
- Fallback to configured storage disk only when Cloudinary fails
- Store full URLs in database for Cloudinary images

### 2. Environment-Specific Configuration
- Use different storage strategies for different environments
- Test image uploads in staging before production deployment

### 3. Monitoring
- Add logging for image upload failures
- Monitor image URL generation in production
- Set up alerts for broken image URLs

## Testing the Fix

### 1. Test New Image Upload
1. Go to admin panel
2. Create/edit an event
3. Upload a new image
4. Verify the image displays correctly

### 2. Test Existing Images
1. Run the fix command: `php artisan images:fix-events`
2. Check existing events for broken images
3. Verify all images load properly

### 3. Test Different Storage Scenarios
1. Test with Cloudinary (primary)
2. Test with S3 fallback
3. Test with local storage fallback

## Summary

The flyer image issue has been fixed by:

1. **Enhanced URL Generation**: Improved the Event model to handle all storage types
2. **Consistent Upload Strategy**: Standardized image upload handling in controllers
3. **Better S3 Configuration**: Added proper ACL settings for public access
4. **Fix Command**: Created a command to repair existing broken image URLs
5. **Comprehensive Documentation**: Provided troubleshooting and prevention guides

The fix ensures that images work correctly across all deployment scenarios and storage configurations.
