let mix = require('laravel-mix');

mix.setPublicPath('./').options({
    processCssUrls: false,
})

mix.postCss('src/css/app.css', 'css', [
    require('tailwindcss'),
]);
//
// mix.copyDirectory(
//     'node_modules/@fortawesome/fontawesome-free-webfonts/webfonts',
//     'fonts/FontAwesome'
// );
//
// mix.copy(
//     'node_modules/bootstrap/dist/js/bootstrap.min.js.map',
//     'src/js/bootstrap.min.js.map'
// ).copy(
//     'node_modules/animate.css/animate.css',
//     'src/scss/vendor/animate.scss'
// )
//
// //
// //  Build SCSS
// //
// mix.sass('src/scss/app.scss', 'css/');

//
//  Combine JS
//
mix.js('src/js/app.js', 'js/app.js')
