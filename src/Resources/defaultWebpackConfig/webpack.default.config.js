const path = require('path');
const webpack = require('webpack');

// Webpack Plugins
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const AssetsPlugin = require('assets-webpack-plugin');
const ExtractFilePlugin = require('extract-file-loader/Plugin');
const DashboardPlugin = require('webpack-dashboard/plugin');

/**
 * @global {Object} process.env
 * @property {string} WEBPACK_DASHBOARD Possible values: 'enabled'
 * @property {string} WEBPACK_MODE Possible values: 'watch', 'server'
 */

/**
 * @typedef {Object} webpackOptionsParameters
 * @property {string} [publicCompiled] Directory name for compiled assets (default is `compiled`)
 * @property {boolean} [extractCss]    Extract css to file and load it from <link> tag
 * @property {Object} [devServer]      Additional parameters for webpack-dev-server
 * @property {string} [devServerHost]  Url for dev server (default is `localhost:8080`)
 * @property {number} [outputPath]     Custom output path for compiled assets
 * @property {number} [logConfig]      Logs full webpack config before compilation
 */

/**
 * @param {Object} options that are provided from Webpack bundle and `config.yml`
 *                 {@link https://github.com/goldenplanetdk/symfony-webpack/wiki}
 *
 * @param {Array} options.entry
 *
 * @param {Object} options.alias
 * @param {string} options.alias['@root']            => 'root' of the repo or 'root/app' in multi-kernel app
 * @param {string} options.alias['@AcmeHappyBundle'] => 'src/Acme/HappyBundle'
 * @param {string} options.alias['@acmeHappy']       => 'src/Acme/HappyBundle/Resources/assets'
 *
 * @param {string} options.manifestPath              => 'app/cache/dev/webpack-manifest.json'
 *
 * @param {string} options.environment e.g. dev, prod
 *
 * @param {webpackOptionsParameters} options.parameters
 */
module.exports = function makeWebpackConfig(options) {

	// Determine production environment, e.g. for minified builds
	const IS_PRODUCTION = options.environment === 'prod';

	// Determine whether we are running in dev-server/watch mode
	const IS_DEV_SERVER = process.env.WEBPACK_MODE === 'server';
	const IS_WATCH = !!~['server', 'watch'].indexOf(process.env.WEBPACK_MODE);

	// Output path for compiled assets
	const outputPath = options.parameters.outputPath || `${options.alias['@root']}/web`;

	// Directory name for compiled assets
	const publicCompiled = options.parameters.publicCompiled || 'compiled';

	let hostUrl = '';

	// Override localhost for webpack-dev-server, e.g. with an IP of the VM where the app is running
	if (IS_DEV_SERVER) {
		hostUrl = `http://${options.parameters.devServerHost || 'localhost:8080'}`;
	}

	/**
	 * Main config
	 * Reference {@link http://webpack.github.io/docs/configuration.html}
	 */
	const config = {

		entry: options.entry,

		/**
		 * Asset file path, public web path and filename mask
		 */
		output: {

			// Absolute output directory
			path: `${outputPath}/${publicCompiled}/`,

			// Relative or absolute base URL address for compiled assets
			publicPath: `${hostUrl}/${publicCompiled}/`,

			// Filename for entry points
			filename: IS_PRODUCTION ? '[name].[chunkhash].js' : '[name].bundle.js',

			// Filename for non-entry points (on-demand loaded chunk files)
			chunkFilename: IS_PRODUCTION ? '[name].[chunkhash].js' : '[name].bundle.js'
		},

		/**
		 * Resolving absolute paths of modules
		 */
		resolve: {

			alias: options.alias,

			// discover files that are imported without extension
			extensions: ['.js', '.jsx'],

			/**
			 * Directories that must be searched for required modules
			 */
			modules: ['node_modules']
		},

		module: {
			/**
			 * Rules are responsible for converting, transpiling, modifying assets
			 */
			rules: [

				/**
				 * Babel loader
				 * Transpile ES6 and ES7 .js files into ES5 code using babel-loader
				 */
				{
					test: /\.jsx?$/i,
					exclude: /node_modules/,
					use: ['babel-loader'],
				},

				/**
				 * File loader for
				 * - copy media asset files (e.g. png, jpg, gif, svg, woff) to the output (public web directory)
				 * - rename files using the asset hash
				 * - pass along the updated reference to the code
				 */
				{
					// query string in regexp is for URLs inside css files
					// any file extension can be added here for files that must be copied to the output
					test: /\.(png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)(\?.*)?$/i,

					use: [
						// put original name in the destination filename
						'file-loader?name=[name].[hash].[ext]',
					],
				},

				/**
				 * Allow loading html through JS with HTML loader
				 */
				{
					test: /\.html$/i,
					use: ['raw-loader'],
				},

				/**
				 * CSS loader
				 * Allow loading CSS through JS
				 */
				{
					test: /\.css$/i,
					use: getStylesheetsRule(),
				},
				/**
				 * LESS/Sass loader
				 * Transpile to CSS
				 */
				{
					test: /\.less$/i,
					use: getStylesheetsRule('less'),
				},
				{
					test: /\.scss$/i,
					use: getStylesheetsRule('sass'),
				},
			],
		},

		/**
		 * Plugins
		 */
		plugins: [

			/**
			 * AssetsPlugin
			 * Emits a json file with assets paths
			 */
			new AssetsPlugin({
				filename: path.basename(options.manifestPath),
				path: path.dirname(options.manifestPath),
				prettyPrint: true,
			}),

			/**
			 * Needed to use binary files (like images) as entry-points
			 * Includes files that are emitted with `file-loader` in the chunk
			 * so it's available in the manifest that is generated with `assets-webpack-plugin`
			 * @see {@link https://github.com/mariusbalcytis/extract-file-loader}
			 */
			new ExtractFilePlugin(),

			/**
			 * ExtractTextPlugin
			 * - Needed to use binary files (like images) as entry-points
			 * - Puts file-loader emitted files into the manifest file
			 * - allows loading css from <link> tags (css is inlined without this plugin)
			 * - This is also required to include less / sass files
			 */
			new ExtractTextPlugin({
				filename: IS_PRODUCTION ? '[name].[hash].css' : '[name].bundle.css',
				disable: options.parameters.extractCss === false
			}),
		],

		/**
		 * Options for webpack-dev-server.
		 * Enables overlay inside the page if any error occurs when compiling.
		 * Enables CORS headers to allow hot reload from other domain / port.
		 * Reference: https://webpack.js.org/configuration/dev-server/
		 */
		devServer: Object.assign(
			{
				overlay: {
					warnings: false,
					errors: true
				},
				headers: {"Access-Control-Allow-Origin": "*"}
			},
			options.parameters.devServer || {}
		),

		/**
		 * Type of sourcemap to use per build type
		 */
		devtool: 'eval',
	};

	/**
	 * webpack-dashboard plugin
	 * Run with webpack-dashboard in watch/devServer mode
	 */
	if (IS_WATCH && process.env.WEBPACK_DASHBOARD) {
		config.plugins.push(new DashboardPlugin());
	}

	/**
	 * Production-specific config
	 */
	if (IS_PRODUCTION) {

		config.plugins.push(
			// Only emit files when there are no errors
			new webpack.NoEmitOnErrorsPlugin(),

			new webpack.optimize.UglifyJsPlugin()
		);

		config.devtool = 'source-map';
	}

	if (options.parameters.logConfig) {
		logConfig(options, config);
	}

	return config;

	/**
	 * ExtractTextPlugin
	 * Extract CSS files, recommended for production builds
	 *
	 * Style loader
	 * Inlines compiled CSS to `head` section of HTML
	 * Can be used in development for hot-loading
	 *
	 * @private
	 * @param {string} [loaderName]
	 *
	 * @return {Object}
	 */
	function getStylesheetsRule(loaderName) {

		return ExtractTextPlugin.extract({
			fallback: 'style-loader',
			use: [
				{
					loader: 'css-loader',
					options: {
						sourceMap: true,
						// Number of loaders that should be applied to @import-ed resources before `css-loader`
						// Stupid requirement by https://github.com/webpack-contrib/css-loader
						importLoaders: loaderName ? 1 : 0,
					}
				},
				loaderName ?
					{
						loader: `${loaderName}-loader`,
						options: {
							sourceMap: true,
						}
					}
					: null
				,
			].filter(
				(loader) => loader !== null
			),
		});
	}

	/**
	 * Log to console complete webpack configuration
	 *
	 * @param {Object} options
	 * @param {Object} config
	 */
	function logConfig(options, config) {

		const util = require('util');

		/**
		 * Log options
		 */
		console.log('\n\n--------- [ symfony-webpack options ] -----------\n');

		const optionsDump = util.inspect(options, {colors: true});

		console.log(optionsDump);

		/**
		 * Log config
		 */
		console.log('\n\n------------- [ webpack config ] ----------------\n');

		let configDump = util.inspect(config, {colors: true, depth: null});

		// Conceal 'error' string for automated tests
		configDump = configDump.replace(/error/g, 'errror');
		console.log(configDump);

		/**
		 * Log complete config on the next call stack
		 * but only if the config is extended (by a symfony-webpack test)
		 */
		if (path.basename(__filename) === 'webpack.default.config.js') {

			global.setTimeout(() => {

				console.log('\n\n--------- [ extended webpack config ] -----------\n');

				let configDump = util.inspect(config, {colors: true, depth: null});

				// Conceal 'error' string for automated tests
				configDump = configDump.replace(/error/g, 'errror');
				console.log(configDump);
			});
		}
	}

};

