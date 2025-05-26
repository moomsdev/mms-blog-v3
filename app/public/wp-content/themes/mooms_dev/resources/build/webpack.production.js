/**
 * External dependencies.
 */
const { ProvidePlugin, WatchIgnorePlugin } = require('webpack');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const ManifestPlugin = require('webpack-manifest-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

/**
 * Internal dependencies.
 */
const utils = require('./lib/utils');
const configLoader = require('./config-loader');
const spriteSmith = require('./spritesmith');
const postcss = require('./postcss');

/**
 * Setup the environment.
 */
const { env: envName } = utils.detectEnv();

/**
 * Setup Babel loader (Babel 6).
 */
const babelLoader = {
    loader: 'babel-loader',
    options: {
        cacheDirectory: false,
        comments: false,
        presets: [
            '@babel/preset-env'
        ],
    },
};

/**
 * Setup ExtractTextPlugin for CSS.
 */
const extractSass = new ExtractTextPlugin({
    filename: 'styles/[name].css',
});

/**
 * Setup Webpack plugins.
 */
const plugins = [
    new CleanWebpackPlugin(utils.distPath(), {
        root: utils.themeRootPath()
    }),
    new WatchIgnorePlugin([
        /node_modules/,
        /dist/
    ]),
    new ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery'
    }),
    extractSass,
    spriteSmith,
    new ImageminPlugin({
        optipng: { optimizationLevel: 7 },
        gifsicle: { optimizationLevel: 3 },
        svgo: { plugins: [] },
        plugins: [
            require('imagemin-mozjpeg')({
                quality: 100
            })
        ]
    }),
    new ManifestPlugin(),
    new BundleAnalyzerPlugin({
        analyzerMode: 'static',
        reportFilename: 'bundle-report.html',
        openAnalyzer: true
    })
];

module.exports = {
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                parallel: true,
                terserOptions: {
                    compress: {
                        drop_console: true
                    }
                }
            })
        ],
        splitChunks: {
            chunks: 'all'
        }
    },
    entry: require('./webpack/entry'),
    output: require('./webpack/output'),
    resolve: require('./webpack/resolve'),
    externals: require('./webpack/externals'),
    module: {
        rules: [
            // Hỗ trợ import glob cho các file JS/CSS/SCSS.
            {
                enforce: 'pre',
                test: /\.(js|jsx|css|scss|sass)$/i,
                use: 'import-glob'
            },
            // Xử lý file config.json.
            {
                test: utils.themeRootPath('config.json'),
                use: configLoader
            },
            // Xử lý JS qua Babel.
            {
                test: utils.tests.scripts,
                exclude: /node_modules/,
                use: babelLoader
            },
            // Xử lý SCSS/CSS qua ExtractTextPlugin.
            {
                test: utils.tests.styles,
                use: extractSass.extract({
                    publicPath: '../',
                    use: [
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: true,
                                importLoaders: 1
                            }
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                postcssOptions: postcss
                            }
                        },
                        'sass-loader'
                    ]
                })
            },
            // Xử lý hình ảnh.
            {
                test: utils.tests.images,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: file => `images/[name].${utils.filehash(file).substr(0, 10)}.[ext]`
                        }
                    }
                ]
            },
            // Xử lý font.
            {
                test: utils.tests.fonts,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: file => `fonts/[name].${utils.filehash(file).substr(0, 10)}.[ext]`
                        }
                    }
                ]
            }
        ]
    },
    plugins,
    mode: 'production',
    cache: false,
    bail: false,
    watch: false,
    devtool: false
};
