const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const sass = require('sass');
const path = require('path');

module.exports = {
	stats: {
		logging: 'none', // Set logging to 'none'
		errorDetails: true // Show error details
	},
	entry: {
		admin: './assets/js/admin.js'
	},
	output: {
		path: path.join(__dirname, '/dist'),
		filename: 'js/[name].min.js'
	},
	resolve: {
		extensions: ['.js']
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader'
				}
			},
			{
				test: /\.scss$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							sourceMap: true
						}
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: true,
							implementation: sass // Use Dart Sass explicitly
						}
					}
				]
			}
		]
	},
	optimization: {
		minimizer: [new TerserPlugin(), new CssMinimizerPlugin()]
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: 'css/[name].min.css'
		})
	],
	devtool: 'source-map'
};
