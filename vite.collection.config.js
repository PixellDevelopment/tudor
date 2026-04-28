import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  publicDir: false,
  build: {
    outDir: 'dist/collection',
    emptyOutDir: true,
    manifest: true,
    assetsDir: 'assets',

    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'src/collection/main.js'),
      },
      output: {
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash][extname]',
      },
    },
    cssCodeSplit: true,
  },

  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@use "${path
          .resolve(__dirname, 'src/collection/scss/_variables.scss')
          .replace(/\\/g, '/')}" as *;`,
      },
    },
  },

  server: {
    host: '0.0.0.0',
    port: 5174,
    strictPort: true,
    cors: true,
    allowedHosts: 'all',
    watch: {
      usePolling: true,
      interval: 500,
    },
    hmr: {
      host: 'vite.menichelli.pixelldemo.com',
      protocol: 'wss',
      clientPort: 443,
    },
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src/collection'),
    },
  },
})