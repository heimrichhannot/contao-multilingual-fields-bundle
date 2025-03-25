var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/assets/')
    .addEntry('contao-multilingual-fields-bundle', './assets/js/contao-multilingual-fields-bundle.js')
    .setPublicPath('/bundles/heimrichhannotmultilingualfields/assets/')
    .setManifestKeyPrefix('bundles/heimrichhannotmultilingualfields/assets')
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
