const defaultConfigProvider = require('./webpack.default.config.js');
const webpack = require('webpack');

/**
 * Extend default config with parameters
 * related to a single commons chunk entry
 *
 * @param {Object} options Provided from Symfony Webpack bundle
 */
module.exports = function(options) {

	const config = defaultConfigProvider(options);

	config.entry.vendor = [
		'./src/TestCommonsChunkBundle/Resources/assets/vendor-1',
		'./src/TestCommonsChunkBundle/Resources/assets/vendor-2',
		// 'jquery',
		// 'lodash',
	];

	config.plugins.push(

		new webpack.optimize.CommonsChunkPlugin({
			name: 'vendor',
		})
	);

	return config;
};
