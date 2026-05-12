import axios from 'axios';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Alpine.plugin(persist);
window.Alpine = Alpine;
