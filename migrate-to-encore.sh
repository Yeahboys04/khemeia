#!/bin/bash

# Script de migration vers Webpack Encore pour Symfony
# Ce script va installer les dépendances, créer l'arborescence,
# déplacer les fichiers et configurer Webpack Encore

set -e  # Exit on error

echo "🚀 Début de la migration vers Webpack Encore"

# Vérification que nous sommes à la racine du projet Symfony
if [ ! -f "composer.json" ] || [ ! -d "public" ] || [ ! -d "templates" ]; then
    echo "❌ Erreur: Veuillez exécuter ce script à la racine de votre projet Symfony"
    exit 1
fi

# 1. Installation des dépendances
echo "📦 Installation de Webpack Encore et des dépendances nécessaires..."
composer require symfony/webpack-encore-bundle
yarn add @symfony/webpack-encore --dev
yarn add core-js regenerator-runtime --dev
yarn add sass-loader@^13.0.0 sass --dev
yarn add postcss-loader autoprefixer --dev
yarn add file-loader --dev
yarn add bootstrap@3.3.7 --dev  # Version utilisée dans le projet
yarn add jquery@3.4.1 --dev     # Version utilisée dans le projet
yarn add font-awesome@4.7.0 --dev # Version probable basée sur vos imports
yarn add @fortawesome/fontawesome-free --dev

# 2. Création de l'arborescence pour les assets
echo "📁 Création de l'arborescence pour les assets..."
mkdir -p assets/styles
mkdir -p assets/js
mkdir -p assets/images
mkdir -p assets/fonts
mkdir -p assets/styles/components
mkdir -p assets/styles/pages
mkdir -p assets/js/components
mkdir -p assets/js/pages

# 3. Déplacement des fichiers CSS existants
echo "🔄 Déplacement des fichiers CSS..."

# Fichiers CSS généraux
cp public/assets/css/google_style.css assets/styles/google_fonts.scss
cp public/assets/css/general_style.css assets/styles/general.scss
cp public/assets/css/print.css assets/styles/print.scss
cp public/assets/css/header_style.css assets/styles/components/header.scss
cp public/assets/css/responsive_style.css assets/styles/responsive.scss

# Fichiers CSS spécifiques
cp public/assets/css/login_style.css assets/styles/pages/login.scss
cp public/assets/css/home_page_style.css assets/styles/pages/home.scss
cp public/assets/css/storagecard.css assets/styles/pages/storagecard.scss
cp public/assets/css/remove_product.css assets/styles/pages/remove_product.scss
cp public/assets/css/style_connexion.css assets/styles/pages/connexion.scss

# 4. Déplacement des fichiers JS existants
echo "🔄 Déplacement des fichiers JS..."
cp public/assets/js/form.js assets/js/components/form.js
cp public/assets/js/remove_product.js assets/js/pages/remove_product.js
cp public/assets/js/storagecard.js assets/js/pages/storagecard.js

# 5. Copie des images
echo "🔄 Déplacement des images..."
if [ -d "public/assets/images" ]; then
    cp -r public/assets/images/* assets/images/
fi

# 6. Copie des polices
echo "🔄 Déplacement des polices..."
if [ -d "public/assets/fonts" ]; then
    cp -r public/assets/fonts/* assets/fonts/
fi

# 7. Création du fichier principal app.js
echo "📝 Création du fichier app.js principal..."
cat > assets/app.js << 'EOL'
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
// import './bootstrap';

// Import jQuery and expose it to global scope (for legacy code)
import $ from 'jquery';
global.$ = global.jQuery = $;

// Import Bootstrap
import 'bootstrap';

// Import Font Awesome
import '@fortawesome/fontawesome-free/css/all.min.css';

// Uncomment for AdminLTE support if needed
// import 'admin-lte/dist/css/adminlte.min.css';
// import 'admin-lte/dist/js/adminlte.min.js';

// Import legacy JavaScript as needed
// This approach allows gradual migration without breaking functionality
EOL

# 8. Création du fichier principal app.scss
echo "📝 Création du fichier app.scss principal..."
cat > assets/styles/app.scss << 'EOL'
// Import Bootstrap
@import "~bootstrap/dist/css/bootstrap.min.css";

// Import Font Awesome
@import "~@fortawesome/fontawesome-free/css/all.min.css";

// Import base styles
@import "google_fonts";
@import "general";
@import "responsive";

// Import component styles
@import "components/header";

// Optionally import page-specific styles as needed
// @import "pages/home";
// @import "pages/login";
// @import "pages/storagecard";
// @import "pages/remove_product";

// Print styles should be imported last
@import "print";
EOL

# 9. Création des entrées spécifiques pour chaque page
echo "📝 Création des entrées JavaScript spécifiques..."

# Pour la page d'accueil
cat > assets/home.js << 'EOL'
import './styles/app.scss';
import './styles/pages/home.scss';
EOL

# Pour la page de connexion
cat > assets/login.js << 'EOL'
import './styles/app.scss';
import './styles/pages/login.scss';
import './styles/pages/connexion.scss';
EOL

# Pour la page de fiche de stockage
cat > assets/storagecard.js << 'EOL'
import './styles/app.scss';
import './styles/pages/storagecard.scss';
import './js/pages/storagecard.js';
EOL

# Pour la page de retrait de produit
cat > assets/remove_product.js << 'EOL'
import './styles/app.scss';
import './styles/pages/remove_product.scss';
import './js/pages/remove_product.js';
EOL

# 10. Configuration de Webpack Encore
echo "⚙️ Configuration de Webpack Encore..."
cat > webpack.config.js << 'EOL'
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
EOL

# 11. Mise à jour du fichier base.html.twig
echo "📝 Mise à jour des templates Twig..."

# Créer une sauvegarde du fichier base.html.twig
cp templates/base.html.twig templates/base.html.twig.backup

# Remplacer les références aux assets dans base.html.twig
sed -i.bak '
    /<!-- Bootstrap 3.3.7 -->/,/<!-- Google Font -->/c\
        {% block stylesheets %}\
            {{ encore_entry_link_tags('"'"'app'"'"') }}\
            {% block page_stylesheets %}{% endblock %}\
        {% endblock %}
' templates/base.html.twig

# Remplacer les références aux scripts JavaScript dans base.html.twig
sed -i.bak '
    /<!-- jQuery 3 -->/,/{% block javascripts %}/c\
        {% block javascripts %}\
            {{ encore_entry_script_tags('"'"'app'"'"') }}\
            {% block page_javascripts %}{% endblock %}\
        {% endblock %}
' templates/base.html.twig

# 12. Mise à jour du fichier home_page/index.html.twig
cp templates/home_page/index.html.twig templates/home_page/index.html.twig.backup
sed -i.bak '
    /{% block stylesheets %}/,/{% endblock %}/c\
    {% block page_stylesheets %}\
        {{ encore_entry_link_tags('"'"'home'"'"') }}\
    {% endblock %}
' templates/home_page/index.html.twig

# 13. Compiler les assets pour la première fois
echo "🔨 Compilation des assets pour la première fois..."
yarn encore dev

echo "✅ Migration terminée avec succès!"
echo ""
echo "Pour compiler les assets en développement :"
echo "yarn encore dev --watch"
echo ""
echo "Pour compiler les assets pour la production :"
echo "yarn encore production"
echo ""
echo "Pensez à adapter vos templates Twig pour utiliser les nouveaux assets."
echo "Exemple : {{ encore_entry_link_tags('app') }} et {{ encore_entry_script_tags('app') }}"