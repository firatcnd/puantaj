import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      // Geliştirmede CORS derdi olmadan Laravel API'sine yönlendirir
      '/api': 'http://127.0.0.1:8000',
    },
  },
})
