
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import snarkdown from './snarkdown';
import 'datatables.net-bs4';
import './icon'
import 'select2'


import Vue from 'vue';
window.Vue = Vue;

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => window.Vue.component(key.split('/').pop().split('.')[0], files(key).default));

if (document.getElementById('cl-live-search')) {
    new window.Vue({
        el: "#cl-live-search",
    })
}

if (document.getElementById('g-live-search')) {
    new window.Vue({
        el: "#g-live-search",
    })
}

if (document.getElementById('item-live-search')) {
    new window.Vue({
        el: "#item-live-search",
    })
}

if (document.getElementById('celestial-object-generator')) {
    new window.Vue({
        el: "#celestial-object-generator",
    })
}

window.snarkdown = snarkdown;

(() => {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#theme-selector .dropdown-menu a').forEach(a => {
            a.addEventListener('click', (e) => {
                document.body.parentElement.classList.remove('darkmode');
                document.body.parentElement.classList.remove('spacetheme');
                document.body.parentElement.classList.add(a.id);

                a.parentElement.querySelectorAll('a').forEach(a => a.classList.remove('active'));
                a.classList.add('active');

                window.localStorage.setItem('theme', a.id);
                if (a.id === 'light') {
                    window.localStorage.removeItem('theme');
                }
            });
        })
    });
})();
