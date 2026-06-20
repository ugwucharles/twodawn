const fs = require('fs');
const path = require('path');

function loadEnv() {
  const rootEnvPath = path.join(__dirname, '../../.env');
  
  if (fs.existsSync(rootEnvPath)) {
    const content = fs.readFileSync(rootEnvPath, 'utf8');
    content.split(/\r?\n/).forEach(line => {
      // Ignore comments and empty lines
      const trimmed = line.trim();
      if (!trimmed || trimmed.startsWith('#')) return;

      const match = line.match(/^\s*([\w.-]+)\s*=\s*(.*)?\s*$/);
      if (match) {
        const key = match[1];
        let value = match[2] || '';
        
        // Remove surrounding quotes if any
        if (value.startsWith('"') && value.endsWith('"')) {
          value = value.slice(1, -1);
        } else if (value.startsWith("'") && value.endsWith("'")) {
          value = value.slice(1, -1);
        }
        
        // Only set if not already defined (prefers command line env)
        if (process.env[key] === undefined) {
          process.env[key] = value.trim();
        }
      }
    });
  }
}

// Auto-run on require
loadEnv();

module.exports = {
  loadEnv,
};
