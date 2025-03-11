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
import 'admin-lte/dist/js/adminlte.min.js';

// Import legacy JavaScript as needed
// This approach allows gradual migration without breaking functionality
