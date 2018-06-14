// Bootstrap
require('./bootstrap')
// Start Vue
window.Vue = require('vue')
// App settings
Vue.prototype.$appSettings = appSettings;

// Vue Router
import router from 'root/router'

// Store
import store from 'root/store'
// Get token if exists
const token = localStorage.getItem('user-token')
if (token) {
    axios.defaults.headers.common['Authorization'] = "Bearer " + token
}

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
import { AUTH_LOGOUT } from 'root/store/actions/auth'
import { USER_REQUEST } from 'root/store/actions/user'
const app = new Vue({
    router,
    store,
    created: function() {
        var self = this;

        // Handle authentication failures on API calls
        axios.interceptors.response.use(undefined, (err) => {
            if(err.response.status === 401 && err.response.config && !err.response.config.__isRetryRequest) {
                self.$swal({
                    title: "Error",
                    text: typeof err.response.data.message === "string" ? err.response.data.message : "Authentication failed. Please relogin.",
                    type: 'error'
                })
                self.$store.dispatch(AUTH_LOGOUT)
                self.$router.push('/login')
            }
            if((err.response.status === 400 || err.response.status === 500) && typeof err.response.data.message === "string") {
                self.$swal({
                    title: "Error",
                    text: err.response.data.message,
                    type: 'error'
                })
            }
            return Promise.reject(err)
        })

        // Retrieve profile on load if authenticated from storage
        if(self.$store.getters.isAuthenticated) {
            self.$store.dispatch(USER_REQUEST);
        }
    }
}).$mount('#app')
window.app = app;
