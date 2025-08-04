module.exports = {
  content: [
    // Front
    './resources/views/layouts/app.blade.php',
    './app/Http/Livewire/**/*.blade.php',

    // Admin
    './resources/views/layouts/admin.blade.php',
    './resources/views/admin/**/*.blade.php',

    // Componentes do Flowbite
    './node_modules/flowbite/**/*.js',
  ],
  theme: { extend: {} },
  plugins: [
    require('flowbite/plugin'),
    require('@tailwindcss/line-clamp'),
  ],
};
