<template>
    <section class="section">
        <div class="is-clearfix has-margin-bottom-10">
            <div class="is-pulled-left" v-if="tag">
                <router-link :to="'/transactions/list'" class="button is-link" exact><i class="fa fa-list"></i> Back to Monthly List</router-link>
            </div>
            <div class="is-pulled-right">
                <router-link :to="'/transactions/add'" class="button is-primary" exact><i class="fa fa-pencil-square-o"></i> Add New Transaction</router-link>
                <router-link :to="'/transactions/search'" class="button is-link" exact><i class="fa fa-search"></i> Search</router-link>
            </div>
        </div>

        <div class="box" v-if="!tag">
            <div class="columns is-mobile">
                <div class="column is-paddingless is-one-quarter has-text-centered" v-on:click="previousMonth">
                    <i class="fa fa-angle-left"></i>
                </div>
                <div class="column is-paddingless has-text-centered">
                    {{ cursor.month | date('month') }} {{ cursor.year }}
                </div>
                <div class="column is-paddingless is-one-quarter has-text-centered" v-on:click="nextMonth">
                    <i class="fa fa-angle-right"></i>
                </div>
            </div>
        </div>

        <div class="box" v-if="!tag">
            <div class="transaction b">
                <div class="transaction-body white">
                    <span>Balance</span>
                    <h1 v-html="$options.filters.currency(currentBalance)"/>
                </div>
            </div>
        </div>

        <div class="box-group">
            <div class="box" v-if="!tag">
                <div class="transaction b">
                    <div class="transaction-body white">
                        <span>Balance at the end of the period</span>
                        <h1 v-html="$options.filters.currency(periodBalance)"/>
                    </div>
                </div>
            </div>
            <div class="box" v-if="tag">
                <h2 class="has-text-centered">Showing all transactions marked <span class="is-blue">#{{ tag }}</span></h2>
            </div>
            <div class="box">
                <template v-for="(transaction, index) in transactions">
                    <div class="transaction-separator" v-if="index == 0 || transactions[index - 1].date.split(' ')[0] != transaction.date.split(' ')[0]">{{ transaction.date.split(' ')[0] | date }}</div>
                    <transaction :class="transaction.type" :transaction="transaction" @updated="onTransactionUpdated()"></transaction>
                </template>
                <template v-if="transactions.length < 1">
                    <div class="has-text-centered">
                        <template v-if="!tag">
                            No transactions recorded in this time frame.
                        </template>
                        <template v-else>
                            No transactions found.
                        </template>
                    </div>
                </template>
            </div>
            <div class="box" v-if="!tag" id="bontang">
                <div class="transaction b">
                    <div class="transaction-body white">
                        <span>Starting balance</span>
                        <h1 v-html="$options.filters.currency(startingBalance)"/>
                    </div>
                </div>
            </div>
            <div class="box" v-if="tag">
                <div class="columns">
                    <div class="column is-half">
                        <div class="transaction i">
                            <div class="transaction-body white">
                                <span>Total tagged income</span>
                                <h1 v-html="$options.filters.currency(totalIncome)"/>
                            </div>
                        </div>
                    </div>
                    <div class="column is-half">
                        <div class="transaction e">
                            <div class="transaction-body white">
                                <span>Total tagged expense</span>
                                <h1 v-html="$options.filters.currency(totalExpense)"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import VueScrollTo from 'vue-scrollto'
import Transaction from './display/Transaction.vue'

export default {
    name: 'transactions-list',
    components: {
        Transaction
    },
    props: ['year', 'month', 'transactionUID', 'tag'],
    data: function() {
        return {
            block: false,
            transactions: [],
            startingBalance: 0,
            periodBalance: 0,
            currentBalance: 0,
            cursor: { },
            totalIncome: 0,
            totalExpense: 0
        }
    },
    watch: {
        'tag'(newValue, oldValue) {
            if(newValue === undefined) {
                this.loadMonthly();
            } else {
                this.loadTagged(newValue);
            }
        }
    },
    created: function() {
        if(this.tag === undefined) {
            this.loadMonthly();
        } else {
            this.loadTagged(this.tag);
        }
    },
    methods: {
        loadMonthly: function() {
            var self = this

            let date = new Date()

            // set cursor's default date as now, if not defined already
            if(self.cursor.month === undefined || self.cursor.year === undefined) {
                self.$set(self.cursor, 'month', date.getMonth() + 1)
                self.$set(self.cursor, 'year', date.getFullYear())
            }

            if(typeof self.year !== "undefined" && typeof self.month !== "undefined") {
                if(parseInt(self.year) <= 9999 && parseInt(self.year) >= 1900 && parseInt(self.month) <= 12 && parseInt(self.month) >= 1) {
                    self.$set(self.cursor, 'year', self.year)
                    self.$set(self.cursor, 'month', self.month)
                }
            }

            self.getBalance()
            self.loadMonthlyData()
        },

        getBalance: function() {
            var self = this
            self.currentBalance = 0
            self.$http.get('api/stats/balance/all').then(response => {
                self.currentBalance = response.body.balance
            }, response => {
                self.currentBalance = 0
            })
        },

        getPeriodBalance: function(year, month, day) {
            var self = this
            self.startingBalance = 0
            self.$http.get('api/stats/balance/all/'+year+'-'+month+'-'+day).then(response => {
                self.startingBalance = response.body.balance
                self.calculatePeriodBalance()
            }, response => {
                self.startingBalance = 0
            })
        },

        getTransactions: function(params, callback) {
            var self = this

            self.block = true
            self.transactions = []
            self.$http.get('api/transactions', { 'params':  params }).then(response => {
                self.transactions = response.body.transactions
                self.block = false
                callback()
            }, response => {
                self.transactions = []
                self.block = false
            })
        },

        loadMonthlyData: function() {
            var self = this

            let date, year, month, day;
            date = new Date()
            date.setMonth(this.cursor.month - 1)
            date.setYear(this.cursor.year)
            date.setDate(1)

            // get starting balance
            date.setDate(0)
            year = date.getFullYear()
            month = date.getMonth() + 1
            day = date.getDate()
            self.getPeriodBalance(year, month, day)

            year = self.cursor.year
            month = self.cursor.month
            self.getTransactions({ 'year': year, 'month': month}, function() {
                self.calculatePeriodBalance();

                if(typeof self.transactionUID !== "undefined") {
                    setTimeout(function() { VueScrollTo.scrollTo('#' + self.transactionUID, 1000, { 'offset': -25 }) }, 1000)
                }
            })
        },

        onTransactionUpdated: function() {
            var self = this

            if(self.tag === undefined) {
                self.getBalance()
                self.loadMonthlyData()
            } else {
                self.loadTagged(self.tag);
            }
        },

        calculatePeriodBalance: function() {
            var self = this
            var balance = self.startingBalance

            if(self.transactions.length > 0) {
                for(let i = 0; i < self.transactions.length; i++) {
                    if(self.transactions[i].account.type != 'sink' && self.transactions[i].account.type != 'noncurrent') {
                        if(self.transactions[i].type == 'i') {
                            balance += Number(self.transactions[i].amount)
                        } else if(self.transactions[i].type == 'e') {
                            balance -= Number(self.transactions[i].amount)
                        } else if(self.transactions[i].type == 'x' && self.transactions[i].target === null) {
                            balance -= Number(self.transactions[i].amount)
                        } else if(self.transactions[i].type == 'x' && (self.transactions[i].target.type == 'sink' || self.transactions[i].target.type == 'noncurrent')) {
                            balance -= Number(self.transactions[i].amount)
                        }
                    } else {
                        if(self.transactions[i].type == 'x' && (self.transactions[i].target.type != 'sink' && self.transactions[i].target.type != 'noncurrent')) {
                            balance += Number(self.transactions[i].amount)
                        }
                    }
                }
            }

            self.periodBalance = balance
        },

        previousMonth: function() {
            var self = this

            // Get the current date from self.cursor
            var date = new Date(self.cursor.year + "-" + self.cursor.month)
            // Set it to go backwards 1 month
            date.setDate(0)

            // Set the new cursor
            self.$set(self.cursor, 'month', date.getMonth() + 1)
            self.$set(self.cursor, 'year', date.getFullYear())

            // Get the starting balance by getting the balance up to the previous month
            let prev = new Date(date.getTime());
            prev.setDate(0)
            self.getPeriodBalance(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())
            // Get the transactions for the month
            self.getTransactions({ 'year': date.getFullYear(), 'month': date.getMonth() + 1}, function() { self.calculatePeriodBalance(); })
        },

        nextMonth: function() {
            var self = this

            // Get the current date from self.cursor
            var date = new Date(self.cursor.year + "-" + self.cursor.month)
            // Increment month
            date.setMonth(date.getMonth()+1)

            // Set the new cursor
            self.$set(self.cursor, 'month', date.getMonth() + 1)
            self.$set(self.cursor, 'year', date.getFullYear())

            // Get the starting balance by getting the balance up to the previous month
            let prev = new Date(date.getTime());
            prev.setDate(0)
            self.getPeriodBalance(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())
            // Get the transactions for the month
            self.getTransactions({ 'year': date.getFullYear(), 'month': date.getMonth() + 1}, function() { self.calculatePeriodBalance(); })
        },

        loadTagged: function(tag) {
            var self = this

            self.getTransactions({ 'tag': tag }, function() { self.calculateIncomeAndExpenseTotals(); })
        },

        calculateIncomeAndExpenseTotals: function() {
            var self = this

            var income = 0;
            var expense = 0;

            if(self.transactions.length > 0) {
                for(let i = 0; i < self.transactions.length; i++) {
                    if(self.transactions[i].type == 'i') {
                        income += Number(self.transactions[i].amount)
                    } else if(self.transactions[i].type == 'e') {
                        expense += Number(self.transactions[i].amount)
                    } else if(self.transactions[i].type == 'x') {
                        if(self.transactions[i].target === null) {
                            if(self.transactions[i].account.type != 'sink') {
                                expense += Number(self.transactions[i].amount)
                            }
                        } else if(self.transactions[i].account.type != 'sink' && self.transactions[i].target.type == 'sink') {
                            expense += Number(self.transactions[i].amount)
                        } else if(self.transactions[i].account.type == 'sink' && self.transactions[i].target.type != 'sink') {
                            income += Number(self.transactions[i].amount)
                        }
                    }
                }
            }

            self.totalIncome = income
            self.totalExpense = expense
        }
    }
}
</script>
