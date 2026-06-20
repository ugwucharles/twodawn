import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:3001',
        changeOrigin: true
      },
      '/organizer': {
        target: 'http://127.0.0.1:3001',
        changeOrigin: true,
        bypass: (req, res, options) => {
          if (req.headers.accept && req.headers.accept.indexOf('text/html') !== -1) {
            return '/index.html';
          }
        }
      },
      '/admin': {
        target: 'http://127.0.0.1:3001',
        changeOrigin: true,
        bypass: (req, res, options) => {
          if (req.headers.accept && req.headers.accept.indexOf('text/html') !== -1) {
            return '/index.html';
          }
        }
      }
    }
  }
})
