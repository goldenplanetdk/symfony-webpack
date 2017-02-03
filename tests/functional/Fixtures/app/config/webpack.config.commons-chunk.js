var webpack = require('webpack');

/**
 * Extend default symfony.webpack.config.js (the file is copied here during test run)
 * with parameters related to commons chunk entries
 *
 * @param {Object} symfonyOptions
 */
module.exports = function makeWebpackConfig(symfonyOptions) {

	var config = require('./symfony.webpack.config')(symfonyOptions);

	config.entry.shared = ['./src/WebpackTestBundle/Resources/assets/shared'];

	config.plugins.push(
		new webpack.optimize.CommonsChunkPlugin({
			names: [
				'shared',
			],
		})
	);

	return config;
};
