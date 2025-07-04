/**
 * PostCSS Configuration for MoomsDev Theme
 * 
 * Optimized for critical CSS extraction and performance
 */

const path = require('path');

module.exports = (ctx) => {
  const isProduction = ctx.env === 'production';
  
  return {
    plugins: [
      // Import support
      require('postcss-import')({
        path: ['resources/styles', 'node_modules']
      }),
      
      // SCSS-like features
      require('postcss-nested'),
      require('postcss-mixins'),
      require('postcss-simple-vars'),
      
      // Future CSS features
      require('postcss-preset-env')({
        stage: 1,
        features: {
          'nesting-rules': true,
          'custom-media-queries': true,
          'custom-properties': true,
        },
        browsers: ['> 1%', 'last 2 versions', 'not dead']
      }),
      
      // Autoprefixer
      require('autoprefixer')({
        grid: 'autoplace'
      }),
      
      // Critical CSS extraction
      ...(isProduction ? [
        require('@fullhuman/postcss-purgecss')({
          content: [
            './theme/**/*.php',
            './resources/**/*.js',
            './resources/**/*.scss',
          ],
          defaultExtractor: content => {
            // Extract class names, IDs, and pseudo-classes
            return content.match(/[\w-/:]+(?<!:)/g) || [];
          },
          safelist: {
            standard: [
              // WordPress classes
              'wp-block', 'wp-admin-bar', 'logged-in', 'admin-bar',
              // Dynamic classes
              'active', 'show', 'hide', 'fade', 'collapse', 'collapsing',
              'modal', 'dropdown', 'tooltip', 'popover',
              // Animation classes
              'animate', 'aos-init', 'aos-animate',
              // Grid classes
              /^col-/, /^row-/, /^container/,
              // Utility classes
              /^text-/, /^bg-/, /^border-/, /^p-/, /^m-/, /^d-/,
            ],
            deep: [
              // WordPress blocks
              /^wp-block/,
              // Third-party plugins
              /^woocommerce/, /^wc-/,
              /^contact-form/, /^cf7/,
            ],
            greedy: [
              // Dynamic state classes
              /active$/,
              /show$/,
              /open$/,
            ]
          },
          // Variables and keyframes to keep
          variables: true,
          keyframes: true,
        }),
        
        // CSS optimization
        require('cssnano')({
          preset: ['default', {
            discardComments: {
              removeAll: true,
            },
            normalizeUnicode: false,
            // Keep calc() for better browser support
            calc: false,
            // Preserve z-index values
            zindex: false,
          }]
        })
      ] : []),
      
      // Critical CSS will be handled by build process
      
      // Development helpers
      ...(!isProduction ? [
        // CSS debugging
        require('postcss-reporter')({
          clearReportedMessages: true,
          noPlugin: true
        })
      ] : [])
    ]
  };
}; 