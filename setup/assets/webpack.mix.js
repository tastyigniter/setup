let mix = require('laravel-mix');

mix.setPublicPath('./').options({
    processCssUrls: false,
})

mix.postCss('src/css/app.css', 'css', [
    require('tailwindcss'),
]);

mix.js('src/js/app.js', 'js/app.js')
