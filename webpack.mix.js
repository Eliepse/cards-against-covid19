const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

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

mix.disableNotifications()
	.js('resources/js/app.js', 'public/js')
	.js('resources/js/room.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')
	.sass('resources/sass/welcome.scss', 'public/css')
	.options({
		processCssUrls: true,
		postCss: [tailwindcss('tailwind.config.js')],
	});

if (mix.inProduction()) {
	mix.version();
}