// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/admin.css',
        'resources/js/admin.js',
      ],
      refresh: [
        'resources/views/**/*.blade.php',
        'app/Http/Livewire/**/*.php',
      ],
    }),
  ],
  build: {
    // Para JavaScript
    target: 'esnext',
    // Para CSS: modern browsers com :is()
    cssTarget: ['chrome110', 'edge110', 'firefox110', 'safari16'],
  },
});
