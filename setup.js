#!/usr/bin/env node

/**
 * 2DAWN Automated Setup Script
 * This script helps set up the project on a new machine
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const colors = {
  reset: '\x1b[0m',
  green: '\x1b[32m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
};

function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

function runCommand(command, description) {
  try {
    log(`\n⏳ ${description}...`, 'blue');
    execSync(command, { stdio: 'inherit' });
    log(`✅ ${description} completed`, 'green');
    return true;
  } catch (error) {
    log(`❌ ${description} failed`, 'red');
    return false;
  }
}

function checkNodeVersion() {
  try {
    const version = execSync('node --version', { encoding: 'utf-8' }).trim();
    const majorVersion = parseInt(version.slice(1).split('.')[0]);
    
    if (majorVersion < 18) {
      log(`❌ Node.js version ${version} is too old. Please install Node.js 18 or higher.`, 'red');
      return false;
    }
    
    log(`✅ Node.js version ${version} detected`, 'green');
    return true;
  } catch (error) {
    log(`❌ Node.js is not installed. Please install Node.js 18 or higher.`, 'red');
    return false;
  }
}

function checkEnvFile() {
  const envPath = path.join(__dirname, '.env');
  const envExamplePath = path.join(__dirname, '.env.example');
  
  if (!fs.existsSync(envPath)) {
    log(`⚠️  .env file not found`, 'yellow');
    
    if (fs.existsSync(envExamplePath)) {
      log(`📋 Creating .env from .env.example...`, 'blue');
      fs.copyFileSync(envExamplePath, envPath);
      log(`✅ .env file created`, 'green');
      log(`⚠️  Please edit .env and add your actual credentials (Turso, Paystack, etc.)`, 'yellow');
    } else {
      log(`❌ .env.example not found. Creating minimal .env file...`, 'yellow');
      const minimalEnv = `# Database Configuration
# Choose ONE of the following:

# Option 1: Turso (Recommended for production)
TURSO_DATABASE_URL=libsql://your-database-url.turso.io
TURSO_AUTH_TOKEN=your-turso-auth-token

# Option 2: Local SQLite (For development only)
# DB_PATH=./database.sqlite

# Paystack Payment Integration
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key
PAYSTACK_CALLBACK_URL=http://localhost:3001/paystack/callback
`;
      fs.writeFileSync(envPath, minimalEnv);
      log(`✅ Minimal .env file created`, 'green');
      log(`⚠️  Please edit .env and add your actual credentials`, 'yellow');
    }
    return false;
  }
  
  log(`✅ .env file exists`, 'green');
  
  // Check for required environment variables
  const envContent = fs.readFileSync(envPath, 'utf-8');
  const hasTurso = envContent.includes('TURSO_DATABASE_URL') && envContent.includes('TURSO_AUTH_TOKEN');
  const hasPaystack = envContent.includes('PAYSTACK_PUBLIC_KEY') && envContent.includes('PAYSTACK_SECRET_KEY');
  
  if (!hasTurso && !envContent.includes('DB_PATH')) {
    log(`⚠️  Turso credentials not configured. The app will use local SQLite.`, 'yellow');
  }
  
  if (!hasPaystack) {
    log(`⚠️  Paystack credentials not configured. Payment features will not work.`, 'yellow');
  }
  
  return true;
}

function setupDatabase() {
  const dbPath = path.join(__dirname, 'database.sqlite');
  const dbDir = path.dirname(dbPath);
  
  if (!fs.existsSync(dbDir)) {
    fs.mkdirSync(dbDir, { recursive: true });
  }
  
  if (!fs.existsSync(dbPath)) {
    log(`📋 Initializing database...`, 'blue');
    try {
      execSync('node node-app/scripts/seed.cjs', { stdio: 'inherit' });
      log(`✅ Database initialized`, 'green');
    } catch (error) {
      log(`❌ Database initialization failed`, 'red');
      log(`⚠️  You may need to configure your Turso credentials in .env`, 'yellow');
    }
  } else {
    log(`✅ Database already exists`, 'green');
  }
}

async function main() {
  log('\n🚀 2DAWN Setup Script', 'blue');
  log('========================\n', 'blue');
  
  // Check Node.js version
  if (!checkNodeVersion()) {
    log('\n❌ Setup failed. Please install Node.js 18 or higher.', 'red');
    process.exit(1);
  }
  
  // Install dependencies
  if (!runCommand('npm install --ignore-scripts', 'Installing dependencies')) {
    log('\n❌ Setup failed. Could not install dependencies.', 'red');
    process.exit(1);
  }
  
  // Check/create .env file
  checkEnvFile();
  
  // Setup database
  setupDatabase();
  
  log('\n✅ Setup completed successfully!', 'green');
  log('\n📝 Next steps:', 'blue');
  log('1. Edit .env file and add your actual credentials (Turso, Paystack, etc.)', 'reset');
  log('2. Run: npm run dev', 'reset');
  log('3. Open http://localhost:5173 in your browser', 'reset');
  log('\nFor detailed instructions, see SETUP_GUIDE.md\n', 'reset');
}

main().catch(error => {
  log(`\n❌ Setup failed with error: ${error.message}`, 'red');
  process.exit(1);
});
