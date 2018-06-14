<template>
    <div class="container app-body">
        <section class="section">
            <div class="is-clearfix has-margin-bottom-10">
                <div class="is-pulled-left">
                    <router-link :to="'/transactions/list'" class="button is-link" exact><i class="fa fa-list"></i> Back to Transactions List</router-link>
                </div>
            </div>

            <div class="box" v-if="!tag">
                <h2>Search Criteria</h2>
                <form>
                    <div class="field">
                        <div class="columns">
                            <div class="column is-half">
                                <label class="label">From/to Account</label>
                                <div class="control is-expanded">
                                    <div class="select is-fullwidth">
                                        <select v-model="form.account">
                                            <option value="null">Any</option>
                                            <template v-for="i in accounts">
                                                <option :value="i.uid">{{ i.name }}</option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-half">
                                <div class="field">
                                    <label class="label">Transcation Type</label>
                                    <div class="control is-expanded">
                                        <div class="select is-fullwidth">
                                            <select v-model="form.type">
                                                <option value="null">Any</option>
                                                <option value="i">Income</option>
                                                <option value="e">Expense</option>
                                                <option value="x">Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Amount</label>
                        <div class="control columns">
                            <div class="column is-one-quarter">
                                <div class="select is-fullwidth">
                                    <select v-model="form.equality">
                                        <option value="=">Exactly</option>
                                        <option value=">">Greater than</option>
                                        <option value=">=">Greater than or equals to</option>
                                        <option value="<">Less than</option>
                                        <option value="<=">Less than or equals to</option>
                                        <option value="<>">Anything that's not</option>
                                    </select>
                                </div>
                            </div>
                            <div class="column">
                                <vue-numeric class="input has-text-right" currency="" separator=" " v-model="form.amount" :minus="false" :precision="2"></vue-numeric>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Note text contains</label>
                        <div class="control">
                            <input class="input" type="text" v-model="form.note"></input>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Dated at</label>
                        <div class="control columns">
                            <div class="column is-one-quarter">
                                <div class="select is-fullwidth">
                                    <select v-model="form.dateRange">
                                        <option value="=">Exactly at</option>
                                        <option value=">">Later than</option>
                                        <option value=">=">Later than or exactly at</option>
                                        <option value="<">Before</option>
                                        <option value="<=">Before or exaclty at</option>
                                        <option value="<>">Any time that's not</option>
                                    </select>
                                </div>
                            </div>
                            <div class="column">
                                <input class="input" type="text" v-model="form.date"></input>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Tagged</label>
                        <div class="control">
                            <input-tag class="input" :tags="form.tags"></input-tag>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Order by</label>
                        <div class="control columns">
                            <div class="column">
                                <div class="select is-fullwidth">
                                    <select v-model="form.orderBy">
                                        <option value="date">Date</option>
                                        <option value="amount">Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="column is-one-quarter">
                                <div class="select is-fullwidth">
                                    <select v-model="form.orderArrangement">
                                        <option value="asc">Ascending</option>
                                        <option value="desc">Descending</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control has-text-right">
                            <button class="button is-primary" @click="search">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section class="section">
            <div class="box-group">
                <div class="box">
                    <h2>Results</h2>
                </div>
                <div class="box">
                    <template v-for="(transaction, index) in transactions">
                        <transaction displayDate="true" :class="transaction.type" :transaction="transaction" @updated="onTransactionUpdated()"></transaction>
                    </template>
                    <template v-if="transactions.length < 1">
                        <div class="has-text-centered">
                            No transactions found.
                        </div>
                    </template>
                </div>
                <div class="box">
                    <div class="columns">
                        <div class="column is-half">
                            <div class="transaction i">
                                <div class="transaction-body white">
                                    <span>Results' total income</span>
                                    <h1 v-html="$options.filters.currency(totalIncome)"/>
                                </div>
                            </div>
                        </div>
                        <div class="column is-half">
                            <div class="transaction e">
                                <div class="transaction-body white">
                                    <span>Results' total expense</span>
                                    <h1 v-html="$options.filters.currency(totalExpense)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import VueScrollTo from 'vue-scrollto'
import Transaction from './display/Transaction.vue'
import VueNumeric from './input/Numeric.vue'
import InputTag from './input/InputTag.vue'
import moment from 'moment'

export default {
    name: 'transactions-search',
    components: {
        Transaction,
        VueNumeric,
        InputTag
    },
    data: function() {
        return {
            form: {
                type: null,
                account: null,
                equality: '=',
                amount: 0,
                note: null,
                date: null,
                dateRange: '=',
                tags: [],
                orderBy: 'date',
                orderArrangement: 'desc'
            },
            accounts: [],
            transactions: [],
            totalIncome: 0,
            totalExpense: 0
        }
    },
    created: function() {
        var self = this

        axios.get('api/accounts').then(response => {
            self.accounts = response.data.accounts
        }, response => {
            self.accounts = []
            self.$set(self.form, 'account', null)
        })
    },
    methods: {
        search: function(e) {
            e.preventDefault()
            var self = this

            // set "null" strings to actual nulls
            for(var i in self.form) {
                if(self.form[i] === 'null') {
                    self.$set(self.form, i, null)
                }
            }

            self.transactions = []
            axios.get('api/transactions?search=1', { 'params':  self.form }).then(response => {
                self.transactions = response.data.transactions
                self.calculateIncomeAndExpenseTotals()
            }, response => {
                self.transactions = []
            })
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
