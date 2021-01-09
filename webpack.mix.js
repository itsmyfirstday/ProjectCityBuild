const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.typeScript('resources/js/app.tsx', 'public/assets/js')
    .sass('resources/sass/app.scss', 'public/assets/css')
    .options({
        processCssUrls: false
    })
   .sass('resources/sass/navonly.scss', 'public/assets/css')
   .extract([
        'react',
        'date-fns',
        'react-dom',
        'axios'
    ]);

if(mix.config.production) {
    mix.version();

} else {
    mix.browserSync({
        open: false,
        notify: false,
        proxy: 'nginx',
        files: [
            'resources/**/*.php',
            'resources/**/*.css',
            'resources/**/*.js',
            'resources/**/*.ts',
            'public/**/*',
        ],
    });
}
