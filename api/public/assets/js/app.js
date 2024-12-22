/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

//import 'bootstrap/dist/css/bootstrap.min.css';
//import 'bootstrap'

Encore
    .addStyleEntry('css/app', [
        './assets/css/app.css', // Your app's CSS
        './assets/css/bootstrap.min.css' // Bootstrap CSS
    ]);

// any CSS you import will output into a single css file (app.css in this case)

import './css/app.css';
