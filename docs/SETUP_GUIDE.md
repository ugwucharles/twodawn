# 2DAWN Setup Guide

## Prerequisites

- Node.js 18+ (LTS recommended)
- npm or yarn
- Git

## Quick Start

### 1. Install Node.js

**Windows:**
```powershell
winget install OpenJS.NodeJS.LTS -e --source winget
```

**Mac:**
```bash
brew install node
```

**Linux:**
```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Clone and Install Dependencies

```bash
git clone https://github.com/ugwucharles/2DAWN.git
cd 2DAWN
npm install
```

### 3. Configure Environment Variables

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

**Required Environment Variables:**

```bash
# Database Configuration (Choose ONE)
# Option 1: Turso (Recommended for production)
TURSO_DATABASE_URL=libsql://your-database-url.turso.io
TURSO_AUTH_TOKEN=your-turso-auth-token

# Option 2: Local SQLite (For development only)
# DB_PATH=./database.sqlite

# Paystack Payment Integration
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
PAYSTACK_CALLBACK_URL=http://localhost:3001/paystack/callback

# Email Configuration (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_FROM_ADDRESS=info@yourdomain.com

# Cloudinary (Optional - for image uploads)
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### 4. Initialize Database

The database will be automatically initialized on first run. If using local SQLite:

```bash
node node-app/scripts/seed.cjs
```

### 5. Start Development Server

```bash
npm run dev
```

This will start:
- Frontend: http://localhost:5173
- Backend: http://localhost:3001

## Database Setup

### Using Turso (Recommended)

1. Create a Turso account at https://turso.tech
2. Create a new database
3. Get your database URL and auth token
4. Add to `.env`:
   ```bash
   TURSO_DATABASE_URL=libsql://your-database-url.turso.io
   TURSO_AUTH_TOKEN=your_auth_token
   ```

### Using Local SQLite

For local development, the app will automatically use `database.sqlite` in the project root if Turso credentials are not provided.

## Troubleshooting

### "Cannot open database because the directory does not exist"

This happens on Windows when the database path uses Unix-style paths. The app now automatically handles cross-platform paths. Ensure your `.env` file doesn't have a hardcoded `/tmp/database.sqlite` path.

### Port Already in Use

If ports 3001 or 5173 are already in use, you can change them in the respective configuration files or kill the processes:

**Windows:**
```powershell
taskkill /F /IM node.exe
```

**Mac/Linux:**
```bash
pkill -f node
```

### Module Not Found Errors

If you encounter module not found errors, run:

```bash
rm -rf node_modules package-lock.json
npm install
```

### Permission Issues (PowerShell)

If npm commands fail with execution policy errors in PowerShell:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Or use `npm.cmd` instead of `npm` in PowerShell.

## Deployment

### Vercel Deployment

1. Push your code to GitHub
2. Import project in Vercel
3. Add environment variables in Vercel dashboard
4. Deploy

Required Vercel environment variables:
- `TURSO_DATABASE_URL`
- `TURSO_AUTH_TOKEN`
- `PAYSTACK_PUBLIC_KEY`
- `PAYSTACK_SECRET_KEY`
- `PAYSTACK_CALLBACK_URL` (set to your production domain)

## Development

### Project Structure

```
2DAWN/
├── frontend/          # React frontend
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   └── store/
│   └── package.json
├── node-app/          # Node.js backend
│   ├── config/
│   ├── db/
│   ├── models/
│   ├── routes/
│   ├── services/
│   └── server.cjs
├── database.sqlite    # Local SQLite database
└── package.json       # Root package.json
```

### Available Scripts

```bash
npm run dev           # Start both frontend and backend
npm run node:dev      # Start backend only
npm run frontend:dev  # Start frontend only
npm run build         # Build frontend for production
```

## Support

For issues or questions, refer to the project documentation or create an issue on GitHub.
