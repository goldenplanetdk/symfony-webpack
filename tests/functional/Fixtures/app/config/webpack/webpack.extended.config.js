const defaultConfigProvider = require('./webpack.default.config.js');

/**
 * A skeleton for a custom config that extends the default config
 *
 * @param {Object} options Provided from Symfony Webpack bundle
 */
module.exports = function(options) {

	const config = defaultConfigProvider(options);

	return config;
};
