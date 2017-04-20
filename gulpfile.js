const elixir = require('laravel-elixir');

/* require('laravel-elixir-vue-2'); */

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass([
            'app.scss'
        ])
        .webpack('app.js')
        .copy('node_modules/font-awesome/fonts/*.*', 'public/fonts/');
});

elixir(function(mix) {
    mix.scripts('tools/imageresizer.js', 'public/js/tools/imageresizer.js');
});

elixir(function(mix) {
    mix.version('js/tools/imageresizer.js');
});

elixir(function(mix) {
    mix.styles([
        'rsi_im.css'
    ], 'public/css/rsi_im/app.css');
});