const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

async function createBackup(dbOnly = false) {
  const backupDir = path.join(process.cwd(), 'storage', 'backups');
  
  if (!fs.existsSync(backupDir)) {
    fs.mkdirSync(backupDir, { recursive: true });
  }
  
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
  const zipPath = path.join(backupDir, `backup_${timestamp}.zip`);
  
  const output = fs.createWriteStream(zipPath);
  const archive = archiver('zip', { zlib: { level: 9 } });
  
  return new Promise((resolve, reject) => {
    output.on('close', () => {
      console.log(`Backup created: ${zipPath} (${archive.pointer()} bytes)`);
      resolve(zipPath);
    });
    
    archive.on('error', (err) => {
      console.error('Backup error:', err);
      reject(err);
    });
    
    archive.pipe(output);
    
    // Add database file if SQLite
    const dbPath = path.join(process.cwd(), 'database', 'database.sqlite');
    if (fs.existsSync(dbPath)) {
      archive.file(dbPath, { name: 'db/database.sqlite' });
    }
    
    // Add public uploads if not dbOnly
    if (!dbOnly) {
      const publicPath = path.join(process.cwd(), 'storage', 'app', 'public');
      if (fs.existsSync(publicPath)) {
        archive.directory(publicPath, 'storage/public');
      }
    }
    
    archive.finalize();
  });
}

async function listBackups() {
  const backupDir = path.join(process.cwd(), 'storage', 'backups');
  
  if (!fs.existsSync(backupDir)) {
    return [];
  }
  
  const files = fs.readdirSync(backupDir)
    .filter(f => f.endsWith('.zip'))
    .map(f => {
      const filePath = path.join(backupDir, f);
      const stats = fs.statSync(filePath);
      return {
        name: f,
        path: filePath,
        size: stats.size,
        mtime: stats.mtime.getTime(),
      };
    })
    .sort((a, b) => b.mtime - a.mtime);
  
  return files;
}

async function deleteBackup(filename) {
  const backupDir = path.join(process.cwd(), 'storage', 'backups');
  const filePath = path.join(backupDir, filename);
  
  if (!fs.existsSync(filePath)) {
    throw new Error('Backup file not found');
  }
  
  fs.unlinkSync(filePath);
  return { success: true };
}

module.exports = {
  createBackup,
  listBackups,
  deleteBackup,
};
