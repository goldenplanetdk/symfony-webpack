/**
 * PostCSS and plugins
 *
 * Autoprefixer plugin - adds vendor prefixes to css
 *
 * {@link https://github.com/postcss/postcss-loader}
 * {@link https://github.com/postcss/autoprefixer-core}
 */

var autoprefixer = require('autoprefixer');

module.exports = (ctx) => {

	return {
		plugins: [

			autoprefixer({

				browsers: [
					'last 2 versions',
					'> 5%',
					'ie >= 11',
				],
			})
		]
	}
};
