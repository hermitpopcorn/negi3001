import Vue from 'vue'
import store from 'root/store'
import VueRouter from 'vue-router'

Vue.use(VueRouter);

const ifNotAuthenticated = (to, from, next) => {
    if (!store.getters.isAuthenticated) {
        next()
        return
    }
    next('/')
}

const ifAuthenticated = (to, from, next) => {
    if (store.getters.isAuthenticated) {
        next()
        return
    }
    next('/login')
}

var routes = [
    {
        path: '/',
        name: 'home',
        component: require('./components/Home.vue'),
        beforeEnter: (to, from, next) => {
            if(store.getters.isAuthenticated) {
                next('/overview')
                return
            }
            next()
        }
    },
    {
        path: '/login',
        name: 'login',
        component: require('./components/Login.vue'),
        beforeEnter: ifNotAuthenticated,
        props: { activeTab: 'login' }
    },
    {
        path: '/register',
        name: 'register',
        component: require('./components/Login.vue'),
        beforeEnter: ifNotAuthenticated,
        props: { activeTab: 'register' }
    },
    {
        path: '/overview',
        name: 'overview',
        component: require('./components/Overview.vue'),
        beforeEnter: ifAuthenticated
    },
    {
        path: '/transactions',
        redirect: '/transactions/list',
        name: 'transactions',
        component: {
            render (c) { return c('router-view') }
        },
        beforeEnter: ifAuthenticated,
        children: [
            {
                path: 'list',
                name: 'transactions.list',
                component: require('./components/Transactions-List.vue'),
            },
            {
                path: 'list/:year(\\d+)/:month(\\d+)',
                name: 'transactions.list.jump',
                component: require('./components/Transactions-List.vue'),
                props: true,
            },
            {
                path: 'list/:year(\\d+)/:month(\\d+)/:transactionUID',
                name: 'transactions.list.jumpscroll',
                component: require('./components/Transactions-List.vue'),
                props: true,
            },
            {
                path: 'list/tagged/:tag',
                name: 'transactions.list.tagged',
                component: require('./components/Transactions-List.vue'),
                props: true,
            },
            {
                path: 'add',
                name: 'transactions.add',
                component: require('./components/Transactions-Form.vue'),
                props: { transaction: null },
            },
            {
                path: 'edit/:transaction',
                name: 'transactions.edit',
                component: require('./components/Transactions-Form.vue'),
                props: true,
            },
            {
                path: 'search',
                name: 'transactions.search',
                component: require('./components/Transactions-Search.vue')
            }
        ]
    },
    {
        path: '/accounts',
        name: 'accounts',
        component: require('./components/Accounts.vue'),
        beforeEnter: ifAuthenticated
    },
    {
        path: '/stats',
        name: 'statistics',
        component: require('./components/Statistics.vue'),
        beforeEnter: ifAuthenticated
    },
    {
        path: '*',
        name: '404',
        component: require('./components/Error-NotFound.vue')
    }
];

export default new VueRouter({
     routes
})
