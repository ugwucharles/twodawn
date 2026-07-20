// Simple logger wrapper for structured logging
const logger = {
  info: (message, meta = {}) => {
    if (process.env.NODE_ENV === 'production') {
      console.log(JSON.stringify({ level: 'info', message, ...meta }));
    } else {
      console.log(`[INFO] ${message}`, meta);
    }
  },
  
  error: (message, error = null, meta = {}) => {
    if (process.env.NODE_ENV === 'production') {
      console.error(JSON.stringify({ 
        level: 'error', 
        message, 
        error: error ? { message: error.message, stack: error.stack } : null,
        ...meta 
      }));
    } else {
      console.error(`[ERROR] ${message}`, error, meta);
    }
  },
  
  warn: (message, meta = {}) => {
    if (process.env.NODE_ENV === 'production') {
      console.warn(JSON.stringify({ level: 'warn', message, ...meta }));
    } else {
      console.warn(`[WARN] ${message}`, meta);
    }
  },
  
  debug: (message, meta = {}) => {
    if (process.env.NODE_ENV !== 'production') {
      console.log(`[DEBUG] ${message}`, meta);
    }
  }
};

module.exports = logger;
