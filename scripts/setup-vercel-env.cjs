#!/usr/bin/env node

/**
 * Vercel Production Environment Setup Script
 * This script helps set up production environment variables for Vercel deployment
 * Run this after you have your Vercel project ID
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

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
    log(`⏳ ${description}...`, 'blue');
    const result = execSync(command, { encoding: 'utf-8', stdio: 'pipe' });
    log(`✅ ${description} completed`, 'green');
    return result.trim();
  } catch (error) {
    log(`❌ ${description} failed`, 'red');
    log(`Error: ${error.message}`, 'red');
    return null;
  }
}

// Required production environment variables
const PRODUCTION_ENV_VARS = [
  'TURSO_DATABASE_URL',
  'TURSO_AUTH_TOKEN',
  'PAYSTACK_PUBLIC_KEY',
  'PAYSTACK_SECRET_KEY',
  'PAYSTACK_CALLBACK_URL',
];

// Optional environment variables
const OPTIONAL_ENV_VARS = [
  'MAIL_MAILER',
  'MAIL_HOST',
  'MAIL_PORT',
  'MAIL_USERNAME',
  'MAIL_PASSWORD',
  'MAIL_FROM_ADDRESS',
  'CLOUDINARY_CLOUD_NAME',
  'CLOUDINARY_API_KEY',
  'CLOUDINARY_API_SECRET',
  'GOOGLE_CLIENT_ID',
  'PAYSTACK_CALLBACK_URL',
  'FRONTEND_URL',
  'BACKEND_URL',
];

function checkVercelCLI() {
  try {
    execSync('vercel --version', { stdio: 'pipe' });
    log('✅ Vercel CLI is installed', 'green');
    return true;
  } catch (error) {
    log('❌ Vercel CLI is not installed', 'red');
    log('Install it with: npm i -g vercel', 'yellow');
    return false;
  }
}

function checkEnvFile() {
  const envPath = path.join(__dirname, '../.env');
  
  if (!fs.existsSync(envPath)) {
    log('❌ .env file not found', 'red');
    log('Please create a .env file with your production credentials', 'yellow');
    return false;
  }
  
  log('✅ .env file found', 'green');
  return true;
}

function getEnvValue(key) {
  const envPath = path.join(__dirname, '../.env');
  const envContent = fs.readFileSync(envPath, 'utf-8');
  
  const match = envContent.match(new RegExp(`^${key}=(.*)$`, 'm'));
  if (match) {
    let value = match[1].trim();
    // Remove quotes if present
    if ((value.startsWith('"') && value.endsWith('"')) || 
        (value.startsWith("'") && value.endsWith("'"))) {
      value = value.slice(1, -1);
    }
    return value;
  }
  
  return null;
}

function setVercelEnvVar(key, value, environment = 'production') {
  const command = `vercel env add ${key} ${environment}`;
  
  try {
    // Use echo to pipe the value to vercel command
    const fullCommand = `echo "${value}" | ${command}`;
    execSync(fullCommand, { stdio: 'pipe' });
    log(`✅ Set ${key} for ${environment}`, 'green');
    return true;
  } catch (error) {
    log(`❌ Failed to set ${key}`, 'red');
    return false;
  }
}

function setupProductionEnv() {
  log('\n🚀 Setting up Vercel Production Environment Variables', 'blue');
  log('================================================\n', 'blue');
  
  if (!checkVercelCLI()) {
    process.exit(1);
  }
  
  if (!checkEnvFile()) {
    process.exit(1);
  }
  
  // Check if user is logged in to Vercel
  log('Checking Vercel authentication...', 'blue');
  const whoami = runCommand('vercel whoami', 'Checking Vercel login');
  if (!whoami) {
    log('Please login to Vercel first: vercel login', 'yellow');
    process.exit(1);
  }
  
  log(`Logged in as: ${whoami}\n`, 'green');
  
  // Set required environment variables
  log('\n📋 Setting required production environment variables...\n', 'blue');
  
  let missingVars = [];
  
  for (const key of PRODUCTION_ENV_VARS) {
    const value = getEnvValue(key);
    
    if (!value || value.includes('your_') || value.includes('YOUR_')) {
      log(`⚠️  ${key} is not configured in .env (placeholder value)`, 'yellow');
      missingVars.push(key);
      continue;
    }
    
    if (value) {
      setVercelEnvVar(key, value, 'production');
    } else {
      log(`⚠️  ${key} not found in .env`, 'yellow');
      missingVars.push(key);
    }
  }
  
  // Set optional environment variables
  log('\n📋 Setting optional environment variables...\n', 'blue');
  
  for (const key of OPTIONAL_ENV_VARS) {
    const value = getEnvValue(key);
    
    if (value && !value.includes('your_') && !value.includes('YOUR_')) {
      setVercelEnvVar(key, value, 'production');
    }
  }
  
  // Summary
  log('\n📊 Setup Summary', 'blue');
  log('================\n', 'blue');
  
  if (missingVars.length > 0) {
    log(`⚠️  Missing required variables: ${missingVars.join(', ')}`, 'yellow');
    log('Please configure these in your .env file and run this script again', 'yellow');
  } else {
    log('✅ All required production environment variables have been set!', 'green');
  }
  
  log('\n📝 Next steps:', 'blue');
  log('1. Deploy to Vercel: vercel --prod', 'reset');
  log('2. Or use the Vercel dashboard to deploy', 'reset');
  log('\n');
}

// Run the setup
setupProductionEnv()
