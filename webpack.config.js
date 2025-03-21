const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

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
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('home', './assets/home.js')
    .addEntry('login', './assets/login.js')
    .addEntry('storagecard', './assets/storagecard.js')
    .addEntry('remove_product', './assets/remove_product.js')
    .addEntry('edit_product', './assets/edit_product.js')
    .addEntry('printBtn', './assets/js/components/printBtn.js')
    .addEntry('dateWidgetConfig', './assets/js/components/dateWidgetConfig.js')
    .addEntry('inventory', './assets/js/pages/inventory.js')
    .addEntry('export_csv', './assets/js/pages/export_csv.js')
    .addEntry('export_pdf', './assets/export_pdf.js')
    .addEntry('tom-select', './assets/js/components/tom-select.js')
    .addEntry('tom-select-search', './assets/js/product-search.js')
    .addEntry('admin-product-search', './assets/js/admin-product-search.js')
    .addEntry('user', './assets/js/pages/users.js')
    .addEntry('inventory_export', './assets/js/pages/inventory_export.js')
    .addStyleEntry('inventoryExport', './assets/styles/pages/inventoryExport.scss')

    .addEntry('datepicker', './assets/js/components/datepicker.js')
    .addEntry('select', './assets/js/components/select.js')
    .addEntry('datatable', './assets/js/components/datatable.js')
    .addStyleEntry('global', './assets/styles/global.scss')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    // .enableStimulusBridge('./assets/controllers.json')

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

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // Copy images to build directory
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]'
    })

    // Copy fonts to build directory
    .copyFiles({
        from: './assets/fonts',
        to: 'fonts/[path][name].[hash:8].[ext]'
    })
;

module.exports = Encore.getWebpackConfig();
