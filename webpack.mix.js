let mix = require('laravel-mix');

mix.sourceMaps();

// APP
mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copy('node_modules/font-awesome/fonts/', 'public/fonts/');

// TOOLS
mix.js('resources/assets/js/tools/imageresizer.js', 'public/js/tools/').version();

// RSI.IM URL Shortener CSS only
mix.combine(['resources/assets/css/rsi_im.css'], 'public/css/rsi_im/app.css').version();


