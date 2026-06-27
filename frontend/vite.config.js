import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const proxyTarget = 'http://localhost:3001'

const htmlBypass = (req) => {
  const accept = String(req.headers.accept || '')
  // API calls must always proxy to Node, never serve the SPA shell
  if (accept.includes('application/json')) return
  if (accept.includes('text/html')) return '/index.html'
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
      '/ucc': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/events': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/event': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/orders': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
      '/h': { target: proxyTarget, changeOrigin: true, bypass: htmlBypass },
    },
  },
})
