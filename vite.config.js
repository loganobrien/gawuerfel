import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  define: {
    APP_VERSION: JSON.stringify(process.env.npm_package_version),
    APP_BUILD_TIME: Date.now(),
    __VUE_OPTIONS_API__: false,
  },
})
