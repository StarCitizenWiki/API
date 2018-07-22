let mix = require('laravel-mix');

mix.sourceMaps();

// APP
mix.js('resources/assets/js/app.js', 'public/js').version()
    .sass('resources/assets/sass/app.scss', 'public/css').version();
    //.copy('resources/assets/fontawesome/webfonts/*regular*', 'public/fonts/');

