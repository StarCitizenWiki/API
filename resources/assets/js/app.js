
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import snarkdown from './snarkdown';
import 'datatables.net-bs4';
import './icon'


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
    const changeButtonText = () => {
        let text = 'Darkmode';
        if (document.body.parentElement.classList.contains('darkmode')) {
            text = 'Darkmode';
        }
        document.querySelector('#darkmode-toggle span').textContent = text;
    };

    if (document.body.parentElement.classList.contains('darkmode')) {
        changeButtonText();
        document.querySelector('#darkmode-toggle i').classList.remove('fa-toggle-off');
        document.querySelector('#darkmode-toggle i').classList.add('fa-toggle-on');
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('darkmode-toggle').addEventListener('click', (e) => {
            document.body.parentElement.classList.toggle('darkmode');
            e.target.parentElement.querySelector('svg').classList.toggle('fa-toggle-on');
            e.target.parentElement.querySelector('svg').classList.toggle('fa-toggle-off');

            window.localStorage.setItem('darkmode', document.body.parentElement.classList.contains('darkmode') ? 'on' : 'off');
            changeButtonText();
        });
    });
})();
