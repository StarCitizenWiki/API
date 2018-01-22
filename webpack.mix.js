let mix = require('laravel-mix');

mix.sourceMaps();

// APP
mix.js('resources/assets/js/app.js', 'public/js').version()
    .sass('resources/assets/sass/app.scss', 'public/css').version();
    //.copy('resources/assets/fontawesome/webfonts/*regular*', 'public/fonts/');

// TOOLS
mix.js('resources/assets/js/tools/imageresizer.js', 'public/js/tools/').version();

// RSI.IM URL Shortener CSS only
mix.combine(['resources/assets/css/rsi_im.css'], 'public/css/rsi_im/app.css').version();


