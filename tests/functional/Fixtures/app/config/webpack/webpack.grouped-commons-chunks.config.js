const defaultConfigProvider = require('./webpack.default.config.js');
const webpack = require('webpack');

/**
 * Extend default config with parameters related to commons chunk(s) entries
 *
 * Files that belong to particular commons chunk are specified in TWIG files
 * as a `group` attribute of a `{% webpack group='chunk_name' %}` tag
 * or `{{ webpack_named_asset('chunk_name') }}` function
 *
 * @param {Object} options Provided from Symfony Webpack bundle
 */
module.exports = function(options) {

	const config = defaultConfigProvider(options);

	config.plugins.push(

		new webpack.optimize.CommonsChunkPlugin({
			name: 'front_commons_chunk',
			chunks: options.groups['default'],
			minChunks: 2,
		}),

		new webpack.optimize.CommonsChunkPlugin({
			name: 'admin_commons_chunk',
			chunks: options.groups['admin'],
		})
	);

	return config;
};

