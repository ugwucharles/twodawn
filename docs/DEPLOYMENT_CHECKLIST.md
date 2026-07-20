# 2DAWN Production Deployment Checklist

## Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Update `.env` with production credentials (no placeholder values)
- [ ] Set `TURSO_DATABASE_URL` to production Turso database
- [ ] Set `TURSO_AUTH_TOKEN` to production Turso auth token
- [ ] Set `PAYSTACK_PUBLIC_KEY` to production Paystack key
- [ ] Set `PAYSTACK_SECRET_KEY` to production Paystack secret
- [ ] Set `PAYSTACK_CALLBACK_URL` to production domain (e.g., `https://twodawn.com.ng/paystack/callback`)
- [ ] Configure email settings (SendGrid/SMTP) if using email features
- [ ] Configure Cloudinary credentials if using image uploads
- [ ] Set `APP_ENV=production` in environment variables

### 2. Database Setup
- [ ] Ensure Turso database is created and accessible
- [ ] Verify database schema is up to date
- [ ] Test database connection from local environment
- [ ] Backup existing data if migrating from another system

### 3. Build Verification
- [ ] Run `npm run build` locally to verify frontend builds successfully
- [ ] Check for any build errors or warnings
- [ ] Test production build locally: `npm run preview` (in frontend directory)
- [ ] Verify all assets are generated correctly

### 4. Code Quality
- [ ] Remove any console.log statements from production code
- [ ] Remove any debug/development-only code
- [ ] Verify error handling is in place for all API calls
- [ ] Check for hardcoded URLs or credentials
- [ ] Ensure sensitive data is not committed to git

### 5. Testing
- [ ] Test all user flows in production-like environment
- [ ] Test sign up/sign in with Google OAuth
- [ ] Test event creation and management
- [ ] Test ticket purchase flow with Paystack
- [ ] Test email delivery (if configured)
- [ ] Test file uploads (if using Cloudinary)
- [ ] Test responsive design on mobile devices
- [ ] Test admin dashboard functionality
- [ ] Test organizer dashboard functionality
- [ ] Test host panel functionality

### 6. Security
- [ ] Verify all API endpoints have proper authentication
- [ ] Check for CORS configuration
- [ ] Ensure rate limiting is configured
- [ ] Verify environment variables are not exposed in client-side code
- [ ] Check for SQL injection vulnerabilities
- [ ] Verify XSS protection is in place
- [ ] Ensure HTTPS is enforced in production

### 7. Performance
- [ ] Optimize images and assets
- [ ] Enable caching headers
- [ ] Check for memory leaks
- [ ] Verify database queries are optimized
- [ ] Test load handling

### 8. Monitoring & Logging
- [ ] Set up error tracking (Sentry or similar)
- [ ] Configure logging for production
- [ ] Set up uptime monitoring
- [ ] Configure alerts for critical failures

## Vercel Deployment Steps

### 1. Install Vercel CLI
```bash
npm i -g vercel
```

### 2. Login to Vercel
```bash
vercel login
```

### 3. Set Up Production Environment Variables
```bash
npm run setup:vercel
```

This will automatically set all required production environment variables from your `.env` file.

### 4. Deploy to Production
```bash
vercel --prod
```

### 5. Verify Deployment
- [ ] Check Vercel dashboard for successful deployment
- [ ] Test the live production URL
- [ ] Verify all functionality works
- [ ] Check Vercel logs for any errors

## Post-Deployment Checklist

### 1. Verification
- [ ] Test all critical user flows on production URL
- [ ] Verify payment processing works end-to-end
- [ ] Check email delivery (if configured)
- [ ] Verify database operations work correctly
- [ ] Test Google OAuth flow
- [ ] Verify file uploads work (if configured)

### 2. Monitoring
- [ ] Set up analytics (Google Analytics, Plausible, etc.)
- [ ] Configure error tracking
- [ ] Set up uptime monitoring
- [ ] Check Vercel analytics

### 3. Backup
- [ ] Create database backup
- [ ] Document deployment configuration
- [ ] Save environment variables securely

### 4. Documentation
- [ ] Update any API documentation
- [ ] Document any configuration changes
- [ ] Update team on deployment status

## Rollback Plan

If issues arise after deployment:

1. **Immediate Rollback**
   ```bash
   vercel rollback
   ```

2. **Database Rollback**
   - Restore from most recent backup
   - Verify data integrity

3. **Communication**
   - Notify team of rollback
   - Communicate with users if necessary

## Troubleshooting

### Build Errors
- Check Vercel build logs
- Verify all dependencies are in package.json
- Ensure Node.js version is compatible

### Runtime Errors
- Check Vercel function logs
- Verify environment variables are set correctly
- Check database connectivity

### Payment Issues
- Verify Paystack credentials
- Check webhook URLs
- Test payment flow in Paystack dashboard

### Database Issues
- Verify Turso database is accessible
- Check connection string format
- Verify auth token is valid

## Support Resources

- Vercel Documentation: https://vercel.com/docs
- Turso Documentation: https://turso.tech/docs
- Paystack Documentation: https://paystack.com/docs
- React Documentation: https://react.dev
