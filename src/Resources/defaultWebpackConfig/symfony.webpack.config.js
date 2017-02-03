var path = require('path');
var webpack = require('webpack');
var autoprefixer = require('autoprefixer');

// Webpack Plugins
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var AssetsPlugin = require('assets-webpack-plugin');
var ExtractFilePlugin = require('extract-file-loader/Plugin');
var DashboardPlugin = require('webpack-dashboard/plugin');

/**
 * @param {Object} symfonyOptions that are provided from Webpack bundle and `config.yml`
 *                 {@link https://github.com/goldenplanetdk/symfony-webpack/wiki}
 *
 * @param {Array} symfonyOptions.entry
 *
 * @param {Object} symfonyOptions.alias
 * @param {string} symfonyOptions.alias['@root']            => 'root' of the repo or 'root/app' in multi-kernel app
 * @param {string} symfonyOptions.alias['@AcmeHappyBundle'] => 'src/Acme/HappyBundle'
 * @param {string} symfonyOptions.alias['@acme_happy']      => 'src/Acme/HappyBundle/Resources/assets'
 *
 * @param {string} symfonyOptions.manifestPath             => 'app/cache/dev/webpack_manifest.json'
 *
 * @param {string} symfonyOptions.environment e.g. dev, prod
 *
 * @param {Object} symfonyOptions.parameters
 * @param {string} [symfonyOptions.parameters.compiledDirName] Directory name for compiled assets (default is `compiled`)
 * @param {boolean} [symfonyOptions.parameters.extractCss] Extract css to file and load it from <link> tag
 * @param {string} [symfonyOptions.parameters.devServerHost] Url for dev server (default is `localhost:8080`)
 * @param {number} [symfonyOptions.parameters.outputPath] Custom output path for compiled assets
 */
module.exports = function makeWebpackConfig(symfonyOptions) {

	/**
	 * Environment type
	 * IS_PRODUCTION is for generating minified builds
	 */
	var IS_PRODUCTION = symfonyOptions.environment === 'prod';

	/**
	 * Whether we are running in dev-server/watch mode (versus simple compile)
	 */
	var IS_DEV_SERVER = process.env.WEBPACK_MODE === 'server';
	var IS_WATCH = !!~['server', 'watch'].indexOf(process.env.WEBPACK_MODE);

	/**
	 * Override localhost for webpack-dev-server, e.g. with an IP of the VM where the app is running
	 */
	var hostUrl = '';
	if (IS_DEV_SERVER) {
		hostUrl = `http://${symfonyOptions.parameters.devServerHost || 'localhost:8080'}`;
	}

	/**
	 * Set output path for compiled assets
	 */
	var outputPath = symfonyOptions.parameters.outputPath || `${symfonyOptions.alias['@root']}/web`;

	/**
	 * Set dir name for compiled assets
	 */
	var compiledDirName = symfonyOptions.parameters.compiledDirName || 'compiled';

	/**
	 * Config
	 * Reference: http://webpack.github.io/docs/configuration.html
	 * This is the object where all configuration gets set
	 */
	var config = {

		entry: symfonyOptions.entry,

		/**
		 * Asset file path, public web path and filename mask symfonyOptions
		 *
		 * {@link https://webpack.github.io/docs/configuration.html#output}
		 *
		 * @description
		 * Difference between hashes:
		 * - [name] contains the name of the entry point script file
		 *          and an appended hash of the script's absolute file path
		 *          required to avoid collision in entry points with same file names
		 * - [hash] is calculated for a build
		 * - [chunkhash] is calculated for a chunk (entry file)
		 * - [contenthash] is generated in ExtractTextPlugin and is calculated by extracted content, not by whole chunk content
		 *
		 * Docs about Long Term Caching with Webpack:
		 * {@link http://webpack.github.io/docs/long-term-caching.html}
		 * {@link https://medium.com/@okonetchnikov/long-term-caching-of-static-assets-with-webpack-1ecb139adb95}
		 */
		output: {
			// Absolute output directory
			path: `${outputPath}/${compiledDirName}/`,

			// Relative or absolute base URL address for compiled assets
			publicPath: `${hostUrl}/${compiledDirName}/`,

			// Filename for entry points
			filename: IS_PRODUCTION ? '[name].[chunkhash].js' : '[name].bundle.js',

			// Filename for non-entry points (on-demand loaded chunk files)
			chunkFilename: IS_PRODUCTION ? '[name].[chunkhash].js' : '[name].bundle.js'
		},

		/**
		 * symfonyOptions affecting the resolving of modules.
		 * {@link https://webpack.js.org/configuration/resolve/}
		 */
		resolve: {

			alias: symfonyOptions.alias,

			// discover files that are imported without extension
			extensions: ['.js', '.jsx', '.ts', '.tsx'],

			/**
			 * Directories that must be searched for required modules
			 * {@link https://webpack.js.org/configuration/resolve/#resolve-modules}
			 */
			modules: ['node_modules']
		},

		module: {
			rules: require('./webpack-rules')
		},
	};

	/**
	 * PostCSS
	 * Reference: https://github.com/postcss/autoprefixer-core
	 * Add vendor prefixes to your css
	 */
	// config.postcss = [
	// 	autoprefixer({
	// 		browsers: ['last 2 version']
	// 	})
	// ];

	/**
	 * Plugins
	 * Reference: http://webpack.github.io/docs/configuration.html#plugins
	 * List: http://webpack.github.io/docs/list-of-plugins.html
	 */
	config.plugins = [

		/**
		 * ExtractTextPlugin
		 * - Needed to use binary files (like images) as entry-points
		 * - Puts file-loader emitted files into manifest
		 * - allows loading css from <link> tags (css is inlined without this plugin)
		 * - This is also required to include less / sass files
		 * {@link https://github.com/webpack/extract-text-webpack-plugin}
		 */
		new ExtractTextPlugin({
			filename: IS_PRODUCTION ? '[name].[hash].css' : '[name].bundle.css',
			disable: !symfonyOptions.parameters.extractCss
		}),

		/**
		 * AssetsPlugin
		 * Emits a json file with assets paths
		 * {@link https://github.com/kossnocorp/assets-webpack-plugin}
		 */
		new AssetsPlugin({
			filename: path.basename(symfonyOptions.manifestPath),
			path: path.dirname(symfonyOptions.manifestPath),
			prettyPrint: true,
		}),

		/**
		 * ExtractFilePlugin for extract-file-loader
		 * Needed to use binary files (like images) as entry-points
		 * puts file-loader emitted files into manifest
		 * {@link https://github.com/mariusbalcytis/extract-file-loader}
		 */
		new ExtractFilePlugin(),
	];

	if (IS_WATCH && process.env.TTY_MODE === 'on') {
		config.plugins.push(new DashboardPlugin());
	}

	// Add build specific plugins
	if (IS_PRODUCTION) {

		config.plugins.push(
			/**
			 * NoErrorsPlugin
			 * Only emit files when there are no errors
			 * {@link http://webpack.github.io/docs/list-of-plugins.html#noerrorsplugin}
			 */
			new webpack.NoEmitOnErrorsPlugin(),

			/**
			 * UglifyJsPlugin
			 * Minify all javascript, switch loaders to minimizing mode
			 * {@link http://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin}
			 */
			new webpack.optimize.UglifyJsPlugin()
		);
	}

	/**
	 * Devtool
	 * Reference: http://webpack.github.io/docs/configuration.html#devtool
	 * Type of sourcemap to use per build type
	 */
	if (IS_PRODUCTION) {
		config.devtool = 'source-map';
	} else {
		config.devtool = 'eval';
	}

	return config;
};
