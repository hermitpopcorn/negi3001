export var routes = [
    {
        path: '/',
        redirect: '/overview',
        name: 'home'
    },
    {
        path: '/overview',
        name: 'overview',
        component: require('./components/Overview.vue')
    },
    {
        path: '/transactions',
        redirect: '/transactions/list',
        name: 'transactions',
        component: {
            render (c) { return c('router-view') }
        },
        children: [
            {
                path: 'list',
                name: 'transactions.list',
                component: require('./components/Transactions-List.vue')
            },
            {
                path: 'list/:year(\\d+)/:month(\\d+)',
                name: 'transactions.list.jump',
                component: require('./components/Transactions-List.vue'),
                props: true
            },
            {
                path: 'list/tagged/:tag',
                name: 'transactions.list.tagged',
                component: require('./components/Transactions-List.vue'),
                props: true
            },
            {
                path: 'add',
                name: 'transactions.add',
                component: require('./components/Transactions-Form.vue'),
                props: { transaction: null }
            },
            {
                path: 'edit/:transaction',
                name: 'transactions.edit',
                component: require('./components/Transactions-Form.vue'),
                props: true
            }
        ]
    },
    {
        path: '/accounts',
        name: 'accounts',
        component: require('./components/Accounts.vue')
    },
    {
        path: '/stats/monthly',
        name: 'stats.monthly',
        component: require('./components/Stats-Monthly.vue')
    },
];
