# Git Deployment Instructions

## Overview
This guide covers how to deploy the current 2DAWN codebase to a new git repository and prepare it for Vercel deployment.

## Prerequisites
- Git installed and configured
- Access to the target git repository
- Vercel CLI installed (`npm i -g vercel`)
- Production environment variables configured in `.env`

## Step 1: Prepare the Current Codebase

### 1.1 Clean up unnecessary files
Remove any development-only files that shouldn't be in production:

```bash
# Remove development artifacts (if they exist)
rm -rf node_modules/.cache
rm -rf frontend/dist
rm -rf frontend/node_modules
rm -rf .vercel
```

### 1.2 Verify .gitignore is correct
Ensure `.gitignore` excludes sensitive files:
- `.env` (should be gitignored)
- `node_modules/`
- `.vercel/`
- `*.log`
- Database files (if using local SQLite)

### 1.3 Commit current changes
```bash
git add .
git commit -m "Prepare for production deployment"
```

## Step 2: Clone the Target Repository

### 2.1 Clone the new repository
```bash
git clone <YOUR_TARGET_REPO_URL>
cd <TARGET_REPO_DIRECTORY>
```

### 2.2 Remove all existing files
```bash
# Remove all files except .git
rm -rf *
rm -rf .gitignore
rm -rf README.md
# Be careful with this command - ensure you're in the right directory
```

### Alternative: Safer approach using git
```bash
# Remove all tracked files
git rm -rf .
git clean -fdx
```

## Step 3: Copy Current Codebase to Target Repository

### 3.1 Copy files from current project
```bash
# From the current 2DAWN directory
cp -r /path/to/2DAWN/* /path/to/target/repo/
cp -r /path/to/2DAWN/.* /path/to/target/repo/  # Copy hidden files like .gitignore
```

### 3.2 Windows alternative (using PowerShell)
```powershell
# From the target repository directory
Copy-Item -Path "C:\Users\uber\Desktop\2DAWN\*" -Destination "." -Recurse -Force
Copy-Item -Path "C:\Users\uber\Desktop\2DAWN\.gitignore" -Destination "." -Force
Copy-Item -Path "C:\Users\uber\Desktop\2DAWN\vercel.json" -Destination "." -Force
# Add other hidden files as needed
```

### 3.3 Verify the copy
```bash
ls -la  # Check that all files are present
```

## Step 4: Configure the New Repository

### 4.1 Update .gitignore if needed
Ensure the target repository has proper .gitignore:
```
node_modules/
.env
.env.local
.vercel/
dist/
*.log
database.sqlite
.DS_Store
```

### 4.2 Create production .env.example
The `.env.example` should be in the repo, but `.env` should not:
```bash
# .env is already gitignored, so it won't be committed
# .env.example should be present for reference
```

### 4.3 Update README.md
Update the README with production deployment instructions:
```bash
# Edit README.md to reflect the new repository and deployment info
```

## Step 5: Commit and Push to Target Repository

### 5.1 Add all files
```bash
git add .
```

### 5.2 Commit changes
```bash
git commit -m "Initial production deployment"
```

### 5.3 Push to remote
```bash
git push origin main
# or
git push origin master
```

## Step 6: Set Up Vercel Deployment

### 6.1 Install Vercel CLI (if not installed)
```bash
npm i -g vercel
```

### 6.2 Login to Vercel
```bash
vercel login
```

### 6.3 Link the project to Vercel
```bash
cd /path/to/target/repo
vercel link
```

### 6.4 Set up production environment variables
```bash
npm run setup:vercel
```

This will read from your local `.env` file and set the production variables in Vercel.

### 6.5 Deploy to production
```bash
vercel --prod
```

## Step 7: Verify Deployment

### 7.1 Check Vercel dashboard
- Navigate to your Vercel project
- Verify the deployment was successful
- Check build logs for any errors

### 7.2 Test the production URL
- Visit the production URL provided by Vercel
- Test critical functionality:
  - Sign up/sign in
  - Event creation
  - Ticket purchase
  - Admin dashboard

### 7.3 Monitor logs
```bash
vercel logs
```

## Troubleshooting

### Files not copying correctly
- Use `rsync` on Linux/Mac for better copying
- On Windows, use PowerShell with `Copy-Item -Recurse -Force`
- Verify file permissions after copying

### Git repository issues
```bash
# If git gets confused about file changes
git reset --hard HEAD
git clean -fdx
```

### Vercel build errors
- Check Vercel build logs
- Verify `package.json` scripts are correct
- Ensure all dependencies are listed
- Check Node.js version compatibility

### Environment variable issues
- Verify `.env` file has production values (no placeholders)
- Run `npm run setup:vercel` again
- Check Vercel dashboard environment variables section

## Quick Reference Commands

```bash
# From current project directory
cd /path/to/2DAWN

# Clone target repo
git clone <TARGET_REPO_URL>
cd <TARGET_REPO>

# Remove existing files (careful!)
rm -rf *
rm -rf .gitignore

# Copy files (Windows PowerShell)
Copy-Item -Path "C:\Users\uber\Desktop\2DAWN\*" -Destination "." -Recurse -Force
Copy-Item -Path "C:\Users\uber\Desktop\2DAWN\.gitignore" -Destination "." -Force

# Commit and push
git add .
git commit -m "Initial production deployment"
git push origin main

# Setup Vercel
npm run setup:vercel
vercel --prod
```

## Security Notes

- **NEVER** commit `.env` file with real credentials
- **NEVER** commit database files with real user data
- **ALWAYS** use environment variables for sensitive data
- **ALWAYS** review `.gitignore` before pushing
- **ALWAYS** use HTTPS for git operations

## Post-Deployment

After successful deployment:

1. **Update DNS** (if using custom domain)
2. **Configure SSL** (Vercel handles this automatically)
3. **Set up monitoring** (error tracking, analytics)
4. **Test all functionality** thoroughly
5. **Notify team** of deployment
6. **Monitor logs** for first 24-48 hours

## Rollback Procedure

If you need to rollback:

```bash
# Rollback to previous deployment
vercel rollback

# Or redeploy previous commit
git checkout <PREVIOUS_COMMIT_HASH>
git push
vercel --prod
```

## Support

For issues with:
- **Git**: https://git-scm.com/docs
- **Vercel**: https://vercel.com/docs
- **This project**: Check SETUP_GUIDE.md and DEPLOYMENT_CHECKLIST.md
