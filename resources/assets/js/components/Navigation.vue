<template>
    <div class="tabs is-centered">
        <ul>
            <li :class="{ 'is-active': $route.name.split('.')[0] == 'overview' }"><router-link :to="'/'">Dashboard</router-link></li>
            <li :class="{ 'is-active': $route.name.split('.')[0] == 'transactions' }"><router-link :to="'/transactions'">Transactions</router-link></li>
            <li :class="{ 'is-active': $route.name.split('.')[0] == 'accounts' }"><router-link :to="'/accounts'">Accounts</router-link></li>
            <li :class="{ 'is-active': $route.name.split('.')[0] == 'stats' }"><router-link :to="'/stats/monthly'">Statistics</router-link></li>
        </ul>
    </div>
</template>
