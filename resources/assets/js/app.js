
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import snarkdown from './snarkdown';
import 'datatables.net-bs4';
import { library, dom } from '@fortawesome/fontawesome-svg-core'

import {
    faBook,
    faBookmark,
    faBuilding,
    faBullhorn,
    faCar,
    faCheckCircle,
    faCircle,
    faCircleNotch,
    faClipboardCheck,
    faCloud,
    faCogs,
    faComment,
    faCommentAlt,
    faCrosshairs,
    faChartBar,
    faCube,
    faDesktop,
    faDotCircle,
    faEnvelope,
    faExclamation,
    faExclamationTriangle,
    faExpandAlt,
    faExternalLinkAlt,
    faEye,
    faGlobe,
    faHome,
    faIdCard,
    faImage,
    faInfo,
    faIndustry,
    faMinus,
    faPencilAlt,
    faPlus,
    faQuestionCircle,
    faRedo,
    faRocket,
    faSearch,
    faSignInAlt,
    faSignOutAlt,
    faStickyNote,
    faStopCircle,
    faTag,
    faTable,
    faTachometerAlt,
    faTrashAlt,
    faUser,
    faUserCircle,
    faUserPlus,
    faUsers,
    faUpload,
    faToggleOn,
    faToggleOff,
} from '@fortawesome/free-solid-svg-icons'

import {
    faFacebook,
    faTwitter,
    faTeamspeak,
    faGithub
} from '@fortawesome/free-brands-svg-icons'

library.add(
    faBook,
    faBookmark,
    faBuilding,
    faBullhorn,
    faCar,
    faCheckCircle,
    faCircle,
    faCircleNotch,
    faClipboardCheck,
    faCloud,
    faCogs,
    faComment,
    faCommentAlt,
    faCrosshairs,
    faChartBar,
    faCube,
    faDesktop,
    faDotCircle,
    faEnvelope,
    faExclamation,
    faExclamationTriangle,
    faExpandAlt,
    faExternalLinkAlt,
    faEye,
    faGlobe,
    faHome,
    faIdCard,
    faImage,
    faInfo,
    faIndustry,
    faMinus,
    faPencilAlt,
    faPlus,
    faQuestionCircle,
    faRedo,
    faRocket,
    faSearch,
    faSignInAlt,
    faSignOutAlt,
    faStickyNote,
    faStopCircle,
    faTag,
    faTable,
    faTachometerAlt,
    faTrashAlt,
    faUser,
    faUserCircle,
    faUserPlus,
    faUsers,
    faUpload,
    faToggleOn,
    faToggleOff,
)

library.add(
    faFacebook,
    faTwitter,
    faTeamspeak,
    faGithub
)

dom.watch()


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
        let text = 'Darkmode an';
        if (document.body.parentElement.classList.contains('darkmode')) {
            text = 'Darkmode aus';
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
