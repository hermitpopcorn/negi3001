<template>
    <div class="navigation">
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <router-link :to="'/'" class="navbar-item">
                        <span class="is-blue">&#9679;</span>
                        <span class="is-red">&#9679;</span>
                        <span class="is-green">&#9679;</span>
                        <b>{{ $appSettings.name }}</b>
                    </router-link>
                    <div class="navbar-item">
                        <span class="is-grey-light">v{{ $appSettings.version }}</span>
                    </div>

                    <div class="navbar-burger burger" data-target="navMenu" @click="expand($event)">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div class="navbar-menu" id="navMenu">
                    <div class="navbar-start"></div>

                    <div class="navbar-end">
                        <template v-if="!$store.getters.isAuthenticated">
                            <router-link :to="'/login'" class="navbar-item has-text-centered">Login</router-link>
                            <router-link :to="'/register'" class="navbar-item has-text-centered">Register</router-link>
                        </template>
                        <template v-else>
                            <div class="navbar-item has-text-centered">
                                {{ $store.getters.getProfile.name }}
                            </div>
                            <div class="navbar-item has-text-centered">
                                <button class="button is-danger" @click="logout">Logout</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>
        <template v-if="$store.getters.isAuthenticated">
            <div class="tabs is-centered">
                <ul>
                    <li :class="{ 'is-active': $route.name.split('.')[0] == 'overview' }"><router-link :to="'/overview'">Overview</router-link></li>
                    <li :class="{ 'is-active': $route.name.split('.')[0] == 'transactions' }"><router-link :to="'/transactions'">Transactions</router-link></li>
                    <li :class="{ 'is-active': $route.name.split('.')[0] == 'accounts' }"><router-link :to="'/accounts'">Accounts</router-link></li>
                    <li :class="{ 'is-active': $route.name.split('.')[0] == 'stats' }"><router-link :to="'/stats'">Statistics</router-link></li>
                </ul>
            </div>
        </template>
    </div>
</template>

<script>
import store from 'root/store'
import {AUTH_LOGOUT} from 'root/store/actions/auth'

export default {
    name: 'navigation',
    data: function() {
        return {}
    },
    methods: {
        expand: function(e) {
            let element = e.target
            let target = document.getElementById(element.dataset.target)
            element.classList.toggle("is-active")
            if(element.classList.contains("is-active")) {
                target.classList.add("is-active")
            } else {
                target.classList.remove("is-active")
            }
        },

        logout: function () {
            this.$swal({
                title: 'Are you sure?',
                text: "This will log you out from the application.",
                type: 'warning',
                showCancelButton: true
            }).then((result) => {
                if (result.value) {
                    this.$store.dispatch(AUTH_LOGOUT)
                    .then(() => {
                        this.$router.push('/')
                    })
                }
            })
        }
    }
}
</script>
