import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const proxyTarget = 'http://127.0.0.1:3001'

const htmlBypass = (req) => {
  if (req.headers.accept && req.headers.accept.indexOf('text/html') !== -1) {
    return '/index.html'
  }
}

export default defineConfig({
  plugins: [react()],
  envDir: '..',
  server: {
    port: 5173,
    proxy: {
      '/api': { target: proxyTarget, changeOrigin: true },
      '/organizer': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/admin': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/events': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/event': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/orders': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/h': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
    },
  },
})
