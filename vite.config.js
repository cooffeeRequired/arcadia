import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [],
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost',
      protocol: 'ws'
    }
  },
  // Vypneme build funkcionalitu
  build: false,
})
