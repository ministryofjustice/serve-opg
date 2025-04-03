let Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    .addEntry('uploadCO', './assets/js/PageSpecific/uploadCO.js')
    .addEntry('orderSummary', './assets/js/PageSpecific/orderSummary.js')
    .addEntry('timeout', './assets/js/PageSpecific/timeout.js')
    .addEntry('addDeputy', './assets/js/PageSpecific/addDeputy.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    // copying govuk-frontend images
    .copyFiles(
      {
        from: './node_modules/govuk-frontend/dist/govuk/assets/images',
        to: 'images/[path][name].[ext]',
      })

    // copying govuk-frontend fonts
    .copyFiles(
      {
        from: './node_modules/govuk-frontend/dist/govuk/assets/fonts',
        to: 'fonts/[path][name].[ext]',
      })

    // copying icons
    .copyFiles({
      from: './assets/images/icons',
      to: 'images/icons/[path][name].[ext]',
    })

    // enables Sass/SCSS support
    .enableSassLoader(options => {
      options.implementation = require('sass')
      options.sassOptions.includePaths = ['./node_modules'];
    })
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
