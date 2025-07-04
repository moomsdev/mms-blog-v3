const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const ImageMinimizerPlugin = require('imagemin-webpack-plugin').default;
const CompressionPlugin = require('compression-webpack-plugin');
const PreloadWebpackPlugin = require('@vue/preload-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const config = require('./config.json');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
  mode: isProduction ? 'production' : 'development',
  
  entry: {
    // Critical path splitting
    critical: './resources/scripts/critical.js',
    main: './resources/scripts/main.js',
    admin: './resources/scripts/admin.js',
    // Separate vendor chunks for better caching
    vendor: './resources/scripts/vendor.js',
    // CSS entries for clean naming
    'main-css': './resources/styles/main.scss',
    'admin-css': './resources/styles/admin.scss',
    // TODO: Fix image dependencies first
    // 'login-css': './resources/styles/login/index.scss',
    // 'editor-css': './resources/styles/editor/index.scss',
  },
  
  output: {
    filename: isProduction 
      ? 'js/[name].[contenthash:8].min.js'
      : 'js/[name].min.js',
    path: path.resolve(__dirname, 'dist'),
    clean: true,
    publicPath: '/wp-content/themes/mooms_dev/dist/',
    // Enable modern output for better performance
    environment: {
      arrowFunction: false,
      bigIntLiteral: false,
      const: false,
      destructuring: false,
      dynamicImport: false,
      forOf: false,
      module: false,
    },
  },
  
  module: {
    rules: [
      // JavaScript optimization
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', {
                useBuiltIns: 'usage',
                corejs: 3,
                modules: false,
                targets: {
                  browsers: ['> 1%', 'last 2 versions', 'not ie <= 11']
                }
              }]
            ],
            cacheDirectory: true,
            cacheCompression: false,
          },
        },
      },
      
      // CSS optimization with critical path
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: !isProduction,
              importLoaders: 3,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  ['postcss-import', {
                    resolve: function(id, basedir) {
                      // Handle ~ prefix for node_modules
                      if (id.startsWith('~')) {
                        return path.resolve(__dirname, 'node_modules', id.slice(1));
                      }
                      return id;
                    }
                  }],
                  'autoprefixer',
                  'postcss-preset-env',
                  // Critical CSS optimization
                  isProduction && ['cssnano', {
                    preset: ['default', {
                      discardComments: { removeAll: true },
                      normalizeUnicode: false,
                    }]
                  }],
                  // PurgeCSS for unused CSS removal
                  isProduction && ['@fullhuman/postcss-purgecss', {
                    content: [
                      './theme/**/*.php',
                      './resources/**/*.js',
                    ],
                    defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
                    safelist: ['active', 'show', 'fade', 'collapse', 'collapsing']
                  }]
                ].filter(Boolean),
              },
              sourceMap: !isProduction,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: !isProduction,
              sassOptions: {
                outputStyle: isProduction ? 'compressed' : 'expanded',
              },
            },
          },
        ],
      },
      
      // Advanced image optimization
      {
        test: /\.(png|svg|jpg|jpeg|gif|webp|avif)$/i,
        type: 'asset',
        parser: {
          dataUrlCondition: {
            maxSize: 4 * 1024, // 4kb - reduced for better performance
          },
        },
        generator: {
          filename: 'images/[name].[contenthash:8][ext]',
        },
      },
      
      // Font optimization with preload hints
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name].[contenthash:8][ext]',
        },
      },
    ],
  },
  
  optimization: {
    minimize: isProduction,
    minimizer: [
      // CSS minification
      new CssMinimizerPlugin({
        minimizerOptions: {
          preset: [
            'default',
            {
              discardComments: { removeAll: true },
              normalizeUnicode: false,
            },
          ],
        },
      }),
      
      // JavaScript minification with aggressive optimization
      new TerserPlugin({
        terserOptions: {
          format: {
            comments: false,
          },
          compress: {
            drop_console: isProduction,
            drop_debugger: isProduction,
            pure_funcs: isProduction ? ['console.log', 'console.info'] : [],
            unsafe_arrows: true,
            unsafe_methods: true,
            unsafe_proto: true,
          },
          mangle: {
            safari10: true,
          },
        },
        extractComments: false,
        parallel: true,
      }),
    ],
    
    // Simplified code splitting - disabled for clean CSS naming
    splitChunks: false,
    
    // Runtime chunk for better caching
    runtimeChunk: {
      name: 'runtime',
    },
  },
  
  plugins: [
    // CSS extraction with optimization
    new MiniCssExtractPlugin({
      filename: isProduction
        ? 'css/[name].[contenthash:8].min.css'
        : 'css/[name].min.css',
      chunkFilename: isProduction
        ? 'css/[id].[contenthash:8].min.css'
        : 'css/[id].min.css',
    }),
    
    // Image optimization for faster loading
    new ImageMinimizerPlugin({
      test: /\.(jpe?g|png|gif|svg)$/i,
      minimizerOptions: {
        plugins: [
          ['imagemin-mozjpeg', { 
            quality: 80,
            progressive: true 
          }],
          ['imagemin-pngquant', { 
            quality: [0.65, 0.8],
            speed: 4 
          }],
          ['imagemin-gifsicle', { 
            optimizationLevel: 3 
          }],
          ['imagemin-svgo', {
            plugins: [
              { name: 'removeViewBox', active: false },
              { name: 'removeDimensions', active: true },
            ],
          }],
        ],
      },
    }),
    
    // Gzip compression for smaller file sizes
    isProduction && new CompressionPlugin({
      algorithm: 'gzip',
      test: /\.(js|css|html|svg)$/,
      threshold: 8192,
      minRatio: 0.8,
    }),
    
    // Preload critical resources
    new PreloadWebpackPlugin({
      rel: 'preload',
      include: 'initial',
      fileBlacklist: [/\.map$/, /hot-update\.js$/],
      as(entry) {
        if (/\.css$/.test(entry)) return 'style';
        if (/\.woff2$/.test(entry)) return 'font';
        if (/\.js$/.test(entry)) return 'script';
        return 'script';
      },
    }),
    
    // Bundle analyzer for optimization insights
    process.env.ANALYZE && new BundleAnalyzerPlugin({
      analyzerMode: 'static',
      openAnalyzer: true,
      generateStatsFile: true,
      statsFilename: 'bundle-stats.json',
    }),
    
    // BrowserSync for live reload with WordPress
    !isProduction && new BrowserSyncPlugin({
      proxy: config.development.url,
      port: config.development.port,
      files: [
        './theme/**/*.php',
        './resources/**/*.js',
        './resources/**/*.scss',
        './dist/**/*.css',
        './dist/**/*.js'
      ],
      open: false,
      notify: false,
      ghostMode: {
        clicks: false,
        forms: false,
        scroll: false
      },
      reloadDelay: 100,
      injectChanges: true,
    }),
  ].filter(Boolean),
  
  // Performance hints and optimization
  performance: {
    hints: isProduction ? 'warning' : false,
    maxAssetSize: 244000, // 244kb
    maxEntrypointSize: 244000,
  },
  
  devtool: isProduction ? false : 'eval-source-map',
  
  stats: {
    children: false,
    modules: false,
    entrypoints: false,
    warningsFilter: /export .* was not found in/,
  },
  
  cache: {
    type: 'filesystem',
    buildDependencies: {
      config: [__filename],
    },
    cacheDirectory: path.resolve(__dirname, 'node_modules/.cache/webpack'),
  },
  
  // Development server optimization
  devServer: {
    hot: true,
    liveReload: true,
    compress: true,
    static: {
      directory: path.join(__dirname, 'dist'),
    },
    watchFiles: [
      './theme/**/*.php',
      './resources/**/*.js',
      './resources/**/*.scss',
    ],
    client: {
      overlay: {
        errors: true,
        warnings: false,
      },
    },
  },
}; 