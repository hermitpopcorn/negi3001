// Bootstrap
require('./bootstrap')
// jQuery Events
require('./bulma-events')
// Start Vue
window.Vue = require('vue')

// Vue Router
import VueRouter from 'vue-router'
Vue.use(VueRouter);
import { routes } from './routes'
const router = new VueRouter({ routes })

// Vue Resource
import VueResource from 'vue-resource'
Vue.use(VueResource)
// set Auth header with API Token from meta tag
Vue.http.headers.common['Auth'] = document.head.querySelector("[name=api-token]").content;

// Vue Filters
import filters from './filters'
Vue.filter('currency', filters.currency)
Vue.filter('date', filters.date)

// Vue SweetAlert2
import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);

// Vue ScrollTo
import VueScrollTo from 'vue-scrollto';
Vue.use(VueScrollTo);

// Mount components
Vue.component('navigation', require('./components/Navigation.vue'))
const app = new Vue({
    router
}).$mount('#app')
