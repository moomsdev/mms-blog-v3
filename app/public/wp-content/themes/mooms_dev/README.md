A modern WordPress starter theme which uses the [WP Emerge](https://github.com/htmlburger/wpemerge) framework.

## Features
- All features from [WP Emerge](https://docs.wpemerge.com/#/framework/overview):
  - Routes with custom URLs and query filters
  - Controllers
  - Middleware
  - PSR-7 Responses
  - View Composers
  - Service Container
  - Service Providers
  - PHP view layouts (a.k.a. automatic wrapping)
  - Support for PHP, [Blade 5.4](https://laravel.com/docs/5.4/blade) and/or [Twig 2](https://twig.symfony.com/doc/2.x/api.html) for views
- Gutenberg support.
- [SASS](https://sass-lang.com/) + [PostCSS](https://github.com/postcss/postcss) for stylesheets. Separate bundles are created for **front-end**, **administration**, **Gutenberg** and **login** pages.
- ES6 for JavaScript. Separate bundles are created for **front-end**, **administration**, **Gutenberg** and **login** pages.
- Pure [Webpack](https://webpack.js.org/) to transpile and bundle assets, create sprites, optimize images etc.
- [Browsersync](https://www.browsersync.io/) for synchronized browser development.
- Autoloading for all classes in your `App\` namespace.
- Automatic, fool-proof cache busting for all assets, including ones referenced in styles.
- WPCS, JavaScript and SASS linting and fixing using a single yarn command.
- Single-command optional CSS package installation:
    - Normalize.css
    - Boostrap 4
    - Bulma
    - Foundation
    - Tachyons
    - Tailwind CSS
    - Spectre.css
    - FontAwesome
- WP Unit Test scaffolding for your own classes.

## Requirements

- [PHP](http://php.net/) >= 5.5
- [WordPress](https://wordpress.org/) >= 4.7
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/en/) >= 6.9.1
- [Yarn](https://yarnpkg.com/en/) or NPM

## Documentation

### Quick start
1. Clone this project to wp-content/themes/your-theme-name

2. Run ```composer update```

3. Run ```yarn install``` (If yarn not install then run ```npm i -g yarn```)

4. If you receive an error stating TTY mode is not supported on Windows platform., run ./vendor/bin/wpemerge install inside the newly created your-theme-name directory.

5. Remove author information from composer.json.

### Yarn script

###```yarn dev```

Run the build process in development mode and enable Browsersync.

###```yarn build```

Run the build process in production mode with all optimizations enabled.

###```yarn release```

Creates a production-ready zip of your theme following these steps:

1. Run yarn build.

2. Install production-only Composer dependencies.

3. Create a /wp-content/themes/your-theme-name.zip archive containing all files and directories added to release.include of your config.json file.

By default, this list contains all necessary files for your theme.

If you have any custom files/directories outside of the standard directories of the theme make sure to add them to this list.
Restore development Composer dependencies.

###```yarn lint```

Run the php, scripts and styles linters (WPCS, eslint and stylelint respectively), reporting any lint rule violations.

###```yarn lint-fix```

Run the php, scripts and styles linters (WPCS, eslint and stylelint respectively), fixing any fixable lint rule violations.

###```yarn i18n```

Runs both yarn i18n:textdomain and i18n:pot.

###```yarn i18n:textdomain```

Runs the textdomain command of the node-wp-i18n package, adding a text domain to all gettext function calls throughout your code.

###```yarn i18n:pot```

Runs the makepot command of the node-wp-i18n package, generating your languages/app.pot file based on all gettext function calls throughout your code.

### Browsersync
By default, Browsersync will setup a simple web server and serve your files through a custom port in order to establish a communication channel between the build process and your browser like this: http://localhost:3000/

This is not ideal when working on WordPress projects that are setup in a subdirectory, for example. To let Browsersync know your site's url, open up config.json from the root theme directory and edit the development.url key like this:

```
{
    "development": {
        "url": "http://localhost/my/nested/subdirectory/wordpress/"
        // ...
    }
    // ...
}
```

Save the file and restart your development build process by running yarn dev.

## Directory structure

```
wp-content/themes/your-theme
├── app/
│   ├── helpers/              # Helper files, add your own here as well.
│   ├── routes/               # Register your WP Emerge routes.
│   │   ├── admin.php
│   │   ├── ajax.php
│   │   └── web.php
│   ├── setup/                # Register WordPress menus, post types etc.
│   │   ├── menus.php
│   │   ├── post-types.php
│   │   ├── sidebars.php
│   │   ├── taxonomies.php
│   │   ├── theme-support.php
│   │   └── widgets.php
│   ├── src/                  # PSR-4 autoloaded classes.
│   │   ├── Controllers/      # Controller classes for WP Emerge routes.
│   │   ├── Widgets/          # Widget classes.
│   │   └── ...
│   ├── config.php            # WP Emerge configuration.
│   ├── helpers.php           # Require your helper files here.
│   ├── hooks.php             # Register your actions and filters here.
│   └── views.php             # Register your WP Emerge view composers etc.
├── dist/                     # Bundles, optimized images etc.
├── languages/                # Language files.
├── resources/
│   ├── build/                # Build process configuration.
│   ├── fonts/
│   ├── images/
│   ├── scripts/
│   │   ├── admin/            # Administration scripts.
│   │   ├── editor/           # Gutenberg editor scripts.
│   │   ├── login/            # Login scripts.
│   │   └── theme/            # Front-end scripts.
│   ├── styles/
│   │   ├── admin/            # Administration styles.
│   │   ├── editor/           # Gutenberg editor styles.
│   │   ├── login/            # Login styles.
│   │   ├── shared/           # Shared styles.
│   │   └── theme/            # Front-end styles.
│   └── vendor/               # Any third-party, non-npm assets.
├── theme/                    # Required theme files and views
│   ├── partials/             # View partials.
│   ├── templates/            # Page templates.
│   ├── functions.php         # Bootstrap theme.
│   ├── screenshot.png        # Theme screenshot.
│   ├── style.css             # Theme stylesheet (avoid adding css here).
│   └── [index.php ...]
├── vendor/                   # Composer packages.
├── README.md                 # Your theme README.
└── ...
```

### Notable directories

#### `app/helpers/`

Add PHP helper files here. Helper files should include __function definitions only__. See below for information on where to put actions, filters, classes etc.

#### `app/setup/`

Modify files here according to your needs. These files should contain __registrations and declarations of WordPress entities only__ such as post types, taxonomies etc.

#### `app/src/`

Add PHP class files here. All clases in the `App\` namespace are autoloaded in accordance with [PSR-4](http://www.php-fig.org/psr/psr-4/).

#### `resources/images/`

Add images for styling here. Optimized copies will be placed in `dist/images/` when running the build process.

#### `resources/styles/theme/`

Add .css and .scss files to add them to the front-end bundle. Don't forget to `@import` them in `index.scss`.

#### `resources/styles/[admin,editor,login]/`

These directories are for the admin, editor and login bundles, respectively. They work identically to the main `resources/styles/theme/` directory.

#### `resources/scripts/theme/`

Add JavaScript files here to add them to the front-end bundle. The entry point is `index.js`.

#### `resources/scripts/[admin,editor,login]/`

These directories are for the admin, editor and login bundles, respectively. They work identically to the main `resources/scripts/theme/` directory.

#### `theme/`

Add views in this, the `theme/partials/` or the `theme/templates/` directories accordingly. Avoid adding any PHP logic here, unless it pertains to layouting (PHP logic should go into helper files or [WP Emerge controllers](https://docs.wpemerge.com/#/framework/routing/controllers))

##Reference

[http://docs.wpemerge.com/#/starter-theme/overview](http://docs.wpemerge.com/#/starter-theme/overview)

[http://docs.wpemerge.com/#/starter-theme/quickstart](http://docs.wpemerge.com/#/starter-theme/quickstart)
