// window.$ = window.jQuery = require('jquery');
// window.Popper = require('popper.js/dist/umd/popper');
// require('bootstrap');

import Alpine from 'alpinejs'
import Swal from 'sweetalert2/dist/sweetalert2.js'

window.Alpine = Alpine

window.render = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}

window.Swal = Swal

window.Mustache = require('mustache');

require('./installer.js');

Alpine.start()
