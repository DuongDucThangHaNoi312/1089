var Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

/// GLR 20190217 https://github.com/symfony/webpack-encore/issues/236
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableSingleRuntimeChunk()
    
    .enableSassLoader()

    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .autoProvidejQuery()

    .addEntry('app', './assets/js/app.js')
    .addEntry('admin', './assets/js/admin.js')
    .addEntry('stripe', './assets/js/stripe.js')
    .addEntry('choose_subscription', './assets/js/choose_subscription.js')
    .addEntry('filter_job_titles', './assets/js/filter_job_titles.js')
    .addEntry('css/stripe', './assets/css/stripe.css')
    .addEntry('collection', './assets/js/collection.js')
    .addEntry('css/style', './assets/css/style.css')

    .addPlugin(new CopyWebpackPlugin([
        { from: './assets/images', to: 'images' }
    ]))

    .addPlugin(new CopyWebpackPlugin([
        { from: './assets/machine_learning', to: 'machine_learning' }
    ]))
;

module.exports = Encore.getWebpackConfig();