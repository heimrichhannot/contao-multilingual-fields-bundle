var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/js/')
    .addEntry('contao-multilingual-fields-bundle', './src/Resources/assets/js/contao-multilingual-fields-bundle.js')
    .setPublicPath('/public/js/')
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .configureBabel(function (babelConfig) {
    }, {
        // include to babel processing
        includeNodeModules: ['@hundh/contao-multilingual-fields-bundle']
    })
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
