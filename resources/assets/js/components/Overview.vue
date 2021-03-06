<template>
    <div class="container app-body">
        <section class="section">
            <div class="columns">
                <div class="column is-half">
                    <div class="box is-white has-blue-bg">
                        <small>Total Balance</small>
                        <br>
                        <b class="h4" v-html="$options.filters.currency(totalBalance)"></b>
                    </div>
                </div>
                <div class="column">
                    <div class="box is-white has-red-bg">
                        <small>Expense this month</small>
                        <br>
                        <b class="h4" v-html="$options.filters.currency(totalExpense)"></b>
                    </div>
                </div>
                <div class="column">
                    <div class="box is-white has-green-bg">
                        <small>Income this month</small>
                        <br>
                        <b class="h4" v-html="$options.filters.currency(totalIncome)"></b>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="columns is-marginless is-paddingless is-mobile">
                    <div class="column has-text-left">
                        <span class="is-green">income</span>
                    </div>
                    <div class="column has-text-centered">
                        <span class="is-light">balance this month</span>
                    </div>
                    <div class="column has-text-right">
                        <span class="is-red">expense</span>
                    </div>
                </div>
                <div class="progress is-marginless is-paddingless">
                    <div class="progress-bar" role="progressbar" :style="{ width: incomePercentage + '%' }" :aria-valuenow="incomePercentage" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="columns is-marginless is-paddingless is-mobile">
                    <div class="column has-text-left">
                        <span class="is-green">{{ incomePercentage }}%</span>
                    </div>
                    <div class="column has-text-centered">
                        <span class="is-light" v-html="$options.filters.currency(totalIncome - totalExpense)"></span>
                    </div>
                    <div class="column has-text-right">
                        <span class="is-red">{{ expensePercentage }}%</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="accounts-overview">
                <div class="box-group">
                    <template v-for="account in accounts">
                        <div class="box account" :class="account.type">
                            <div class="symbol">
                                <span class="icon is-large">
                                    <i class="fa fa-3x fa-bank" v-if="account.type == 'regular'"></i>
                                    <i class="fa fa-3x fa-anchor" v-if="account.type == 'noncurrent'"></i>
                                    <i class="fa fa-3x fa-credit-card" v-if="account.type == 'sink'"></i>
                                </span>
                            </div>
                            <div class="info">
                                <p class="type" v-if="account.type == 'regular'">Regular Account</p>
                                <p class="type" v-if="account.type == 'noncurrent'">Noncurrent Account</p>
                                <p class="type" v-if="account.type == 'sink'">Money Sink</p>

                                <p class="name">{{ account.name }}</p>

                                <p class="balance" v-if="account.balance !== null">Remaining balance: <b v-html="$options.filters.currency(account.balance)"></b></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'overview',
    data: function() {
        return {
            totalBalance: "Loading...",
            totalIncome: "Loading...",
            totalExpense: "Loading...",
            accounts: []
        }
    },
    mounted: function() {
        this.getTotalBalance()
        this.getAccountsBalance()
        this.getTotalIncome()
        this.getTotalExpense()
    },
    methods: {
        getTotalBalance: function() {
            var self = this
        axios.get('api/stats/balance/all').then(response => {
                self.totalBalance = response.data.balance
            }, response => {
                self.totalBalance = "???"
            });
        },

        getAccountsBalance: function() {
            var self = this
            axios.get('api/accounts').then(response => {
                self.accounts = response.data.accounts
                for(let i = 0; i < self.accounts.length; i++) {
                    if(self.accounts[i].type == 'sink') {
                        self.$set(self.accounts[i], 'balance', null);
                        continue;
                    }

                    self.$set(self.accounts[i], 'balance', 0)
                    axios.get('api/stats/balance/'+self.accounts[i].uid).then(response => {
                        self.accounts[i].balance = response.data.balance
                        self.$set(self.accounts[i], 'balance', response.data.balance)
                    }, response => {
                        self.$set(self.accounts[i], 'balance', 0)
                    })
                }
            }, response => {
                self.accounts = []
            })
        },

        getTotalIncome: function() {
            var self = this

            let date = new Date()
            let year = date.getFullYear()
            let month = date.getMonth() + 1
            axios.get('api/stats/income/'+year+'/'+month).then(response => {
                self.totalIncome = response.data.income
            }, response => {
                self.totalIncome = "???"
            })
        },

        getTotalExpense: function() {
            var self = this

            let date = new Date()
            let year = date.getFullYear()
            let month = date.getMonth() + 1
            axios.get('api/stats/expense/'+year+'/'+month).then(response => {
                self.totalExpense = response.data.expense
            }, response => {
                self.totalExpense = "???"
            })
        }
    },
    computed: {
        incomePercentage: function() {
            let income = !isNaN(this.totalIncome) ? this.totalIncome : 0
            let expense = !isNaN(this.totalExpense) ? this.totalExpense : 0
            let percentage = ((income / (income + expense)) * 100)
            return Math.round(percentage * 100) / 100
        },
        expensePercentage: function() {
            let income = this.incomePercentage
            let percentage = (100 - income)
            return Math.round(percentage * 100) / 100
        }
    }
}
</script>
