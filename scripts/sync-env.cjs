const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

function runSync(command, args, options = {}) {
  return spawnSync(command, args, {
    stdio: 'inherit',
    shell: true,
    ...options
  });
}

function getVercelCommand() {
  const check = spawnSync('vercel', ['--version'], { shell: true });
  if (check.status === 0) {
    return { cmd: 'vercel', args: [] };
  }
  return { cmd: 'npx', args: ['vercel'] };
}

function main() {
  const envPath = path.join(process.cwd(), '.env');
  if (!fs.existsSync(envPath)) {
    console.error('❌ Error: No .env file found in the current directory.');
    console.error('Please make sure you have a .env file in this directory.');
    process.exit(1);
  }

  const v = getVercelCommand();
  const commandDisplayName = v.cmd === 'npx' ? 'npx vercel' : 'vercel';
  console.log(`Using Vercel CLI command: ${commandDisplayName}`);

  // Check if project is linked to Vercel
  const vercelDir = path.join(process.cwd(), '.vercel');
  if (!fs.existsSync(vercelDir)) {
    console.log('💡 Project is not linked to Vercel yet. Starting linking process...');
    console.log('Follow the prompts to link your Vercel project.');
    const linkResult = runSync(v.cmd, [...v.args, 'link']);
    if (linkResult.status !== 0) {
      console.error('❌ Error: Failed to link project to Vercel.');
      process.exit(1);
    }
  }

  console.log('Reading .env file...');
  const content = fs.readFileSync(envPath, 'utf8');
  const envVars = [];
  
  content.split(/\r?\n/).forEach(line => {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) return;

    const match = line.match(/^\s*([\w.-]+)\s*=\s*(.*)?\s*$/);
    if (match) {
      const key = match[1];
      let value = match[2] || '';
      
      // Strip quotes
      if (value.startsWith('"') && value.endsWith('"')) {
        value = value.slice(1, -1);
      } else if (value.startsWith("'") && value.endsWith("'")) {
        value = value.slice(1, -1);
      }
      
      envVars.push({ key, value: value.trim() });
    }
  });

  if (envVars.length === 0) {
    console.log('No environment variables found to sync.');
    process.exit(0);
  }

  console.log(`Found ${envVars.length} variables. Syncing to Vercel...`);

  for (const { key, value } of envVars) {
    console.log(`Syncing ${key}...`);

    const targets = ['production', 'preview', 'development'];
    let success = true;

    for (const target of targets) {
      // 1. Remove if already exists on Vercel
      spawnSync(v.cmd, [...v.args, 'env', 'rm', key, target, '--yes'], {
        stdio: 'ignore',
        shell: true
      });

      // 2. Add the variable for this specific environment
      const result = spawnSync(v.cmd, [...v.args, 'env', 'add', key, target], {
        input: value + '\n',
        stdio: ['pipe', 'ignore', 'ignore'],
        shell: true
      });

      if (result.status !== 0) {
        success = false;
      }
    }

    if (success) {
      console.log(`✅ Successfully synced ${key} to all environments`);
    } else {
      console.log(`⚠️ Partial success/failure syncing ${key}`);
    }
  }

  console.log('\n🎉 Done! All environment variables have been synced to Vercel.');
  console.log('👉 Note: You must redeploy your project on Vercel for these environment variables to take effect.');
}

main();
