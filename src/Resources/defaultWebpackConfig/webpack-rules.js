var ExtractTextPlugin = require('extract-text-webpack-plugin');

/**
 * Loaders (Rules in Webpack 2.x)
 * These responsible for converting, transpiling, modifying assets
 *
 * Introduction: {@link https://webpack.github.io/docs/loaders.html}
 * Reference: {@link https://webpack.github.io/docs/configuration.html#module-loaders}
 * List: {@link https://webpack.github.io/docs/list-of-loaders.html}
 */
module.exports = [

	/**
	 * Babel loader
	 * Transpile ES6 and ES7 .js files into ES5 code using babel-loader
	 * {@link https://github.com/babel/babel-loader}
	 */
	{
		test: /\.jsx?$/i,
		exclude: /node_modules/,
		loader: 'babel-loader',
	},

	/**
	 * TypeScript loader
	 * {@link https://github.com/TypeStrong/ts-loader}
	 */
	{
		test: /\.tsx?$/i,
		exclude: /node_modules/,
		loader: 'ts-loader',
	},

	/**
	 * File loader for
	 * - copy media asset files (e.g. png, jpg, gif, svg, woff) to the output (public web directory)
	 * - rename files using the asset hash
	 * - pass along the updated reference to the code
	 * {@link https://github.com/webpack/file-loader}
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
	 * HTML loader
	 * Allow loading html through js
	 * {@link https://github.com/webpack/raw-loader}
	 */
	{
		test: /\.html$/i,
		loader: 'raw-loader',
	},

	/**
	 * ExtractTextPlugin
	 * Extract CSS files in production builds
	 * {@link https://github.com/webpack/extract-text-webpack-plugin}
	 *
	 * Style loader
	 * Inlines compiled CSS to `head` section of HTML
	 * Can be used in development for hot-loading
	 * {@link https://github.com/webpack/style-loader}
	 *
	 * CSS loader
	 * Allow loading CSS through JS
	 * {@link https://github.com/webpack/css-loader}
	 *
	 * PostCSS loader
	 * Postprocess CSS with PostCSS plugins
	 * {@link https://github.com/postcss/postcss-loader}
	 */
	{
		test: /\.css$/i,
		loader: ExtractTextPlugin.extract({
			fallbackLoader: 'style-loader',
			loader: 'css-loader?sourceMap!postcss-loader',
		}),
	},

	/**
	 * LESS loader
	 * {@link https://github.com/webpack/less-loader}
	 */
	{
		test: /\.less$/i,
		loader: ExtractTextPlugin.extract({
			fallbackLoader: 'style-loader',
			loader: 'css-loader?sourceMap!postcss-loader!less-loader?sourceMap',
		}),
	},

	/**
	 * Sass loader
	 * {@link https://github.com/jtangelder/sass-loader}
	 */
	{
		test: /\.scss$/i,
		loader: ExtractTextPlugin.extract({
			fallbackLoader: 'style-loader',
			loader: 'css-loader?sourceMap!postcss-loader!sass-loader?sourceMap',
		}),
	},
];
