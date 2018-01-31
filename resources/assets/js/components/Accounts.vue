<template>
    <section class="section">
        <div class="columns accounts-list is-multiline">
            <template v-for="(account, index) in accounts">
                <div class="column is-one-third">
                    <div class="account box is-white " :class="account.type">
                        <div style="position:relative;z-index: 1">
                            <h1>{{ account.name }}</h1>
                            <p v-if="account.type != 'sink'">Initial balance: <span class="is-unbreakable" v-html="$options.filters.currency(account.initialBalance)"/></p>
                            <p v-if="account.type == 'sink'">marked as a money sink</p>
                        </div>
                        <div class="symbol">
                            <template v-if="account.type == 'regular'">
                                    <i class="fa fa-bank"></i>
                            </template>
                            <template v-if="account.type == 'noncurrent'">
                                <i class="fa fa-anchor"></i>
                            </template>
                            <template v-if="account.type == 'sink'">
                                <i class="fa fa-credit-card"></i>
                            </template>
                        </div>
                        <div class="has-margin-top-10">
                            <div class="columns">
                                <div class="column is-half">
                                    <a class="button is-primary is-fullwidth is-small" @click="editAccount(account.uid)" v-scroll-to="'#account-form-container'"><i class="fa fa-pencil"></i> Edit</a>
                                </div>
                                <div class="column is-half">
                                    <a class="button is-danger is-fullwidth is-small" @click="deleteAccount(account.uid)"><i class="fa fa-remove"></i> Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <div class="column">
                <div class="account box is-white has-blue-bg" style="cursor:pointer" @click="addNew" v-scroll-to="'#account-form-container'">
                    <h1>New</h1>
                    <p>Add new account</p>
                    <div class="symbol">
                        <i class="fa fa-plus"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
        </div>

        <div class="columns" id="account-form-container">
            <div class="column" v-if="form.show">
                <div class="box-group accounts-form" id="account-form">
                    <div class="box has-blue-bg is-white" v-if="form.title">
                        <div class="is-pulled-right">
                            <button type="button" class="button is-link" @click="hideForm()"><i class="fa fa-times"></i></button>
                        </div>
                        <h1 v-html="form.title" />
                    </div>
                    <div class="box">
                        <form>
                            <div class="field">
                                <label class="label">Name</label>
                                <div class="control is-expanded">
                                    <input type="text" ref="nameInput" class="input" v-model="form.name"></input>
                                </div>
                            </div>
                            <div class="field" v-if="form.type != 'sink'">
                                <label class="label">Initial Balance</label>
                                <div class="control is-expanded">
                                    <vue-numeric ref="initialBalanceInput" class="input text-right" currency="" separator=" " v-model="form.initialBalance" :minus="false" :precision="2" name="initialBalance" :disabled="(form.type == 'sink')"></vue-numeric>
                                </div>
                            </div>
                            <div class="field">
                                <label class="label">Account</label>
                                <div class="control is-expanded">
                                    <div class="select is-fullwidth">
                                        <select v-model="form.type">
                                            <option value="regular">Regular</option>
                                            <option value="noncurrent">Noncurrent</option>
                                            <option value="sink">Money Sink</option>
                                        </select>
                                    </div>
                                </div>
                                <p class="form-text">
                                    The amount of balance in accounts marked as <b>noncurrent</b> will not be counted towards
                                    the statistic total.
                                    <br/>
                                    Accounts marked as <b>money sink</b> will not have its current balance shown on the overview page.
                                    The account's balance, expense, and income will not be counted towards the statistic total,
                                    also, transfers to the account will be counted as an expense, and transfers from the account
                                    will be counted as an income.
                                </p>
                            </div>
                            <div class="field">
                                <label class="label">Order</label>
                                <div class="control is-expanded">
                                    <input type="numeric" ref="orderInput" class="input" v-model="form.ordering"></input>
                                </div>
                                <p class="form-text">
                                    The account will be ordered in the list based on this number, in ascending order.
                                </p>
                            </div>
                            <div class="field has-text-right">
                                <button class="button is-primary" @click="submit">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import VueNumeric from './input/Numeric.vue'

export default {
    name: 'accounts-list',
    data: function() {
        return {
            accounts: [],
            form: {
                show: false,
                block: false,
                title: "",
                uid: null,
                name: "",
                initialBalance: 0,
                type: 'regular',
                ordering: 0
            }
        }
    },
    components: {
        VueNumeric
    },
    created: function() {
        this.getAccounts()
    },
    methods: {
        hideForm: function() {
            this.$set(this.form, 'show', false)
        },

        getAccounts: function() {
            var self = this
            self.accounts = []
            self.$http.get('api/accounts').then(response => {
                self.accounts = response.body.accounts
            }, response => {
                self.accounts = []
            })
        },

        addNew: function() {
            this.$set(this.form, 'title', "Add new account")
            this.$set(this.form, 'uid', null)
            this.$set(this.form, 'name', "")
            this.$set(this.form, 'initialBalance', 0)
            this.$set(this.form, 'type', 'regular')
            this.$set(this.form, 'ordering', 0)
            this.$set(this.form, 'block', false)
            this.$set(this.form, 'show', true)
        },

        editAccount: function(account) {
            var self = this

            self.$set(self.form, 'block', true)
            self.$http.get('api/accounts/'+account).then(response => {
                this.$set(this.form, 'title', "Edit existing account ("+response.body.account.name+")")
                self.$set(self.form, 'uid', response.body.account.uid)
                self.$set(self.form, 'name', response.body.account.name)
                self.$set(self.form, 'initialBalance', response.body.account.initialBalance)
                self.$set(self.form, 'type', response.body.account.type)
                self.$set(self.form, 'ordering', response.body.account.ordering)
                self.$set(self.form, 'block', false)
                self.$set(self.form, 'show', true)
            }, response => {
                self.$swal({
                    title: 'Not Found',
                    text: 'Could not find account with that identifier.',
                    type: 'warning'
                })
            })
        },

        deleteAccount: function(account) {
            var self = this

            self.$swal({
                title: 'Delete Confirmation',
                text: 'Are you sure? ALL transactions under this account will also be DELETED.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f86c6b',
                cancelButtonColor: '#1985ac',
                confirmButtonText: 'Delete'
            }).then(function() {
                self.$http.delete('api/accounts/'+account).then(response => {
                    self.$swal({
                        title: 'Deleted',
                        text: 'Account has been deleted.',
                        type: 'success'
                    })

                    self.getAccounts()
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Account was not deleted.',
                        type: 'error'
                    })
                })
            })
        },

        submit: function(e) {
            e.preventDefault()
            var self = this

            if(self.form.block) {
                return false
            }

            self.$set(self.form, 'block', true)

            var data = {
                name: self.form.name,
                initialBalance: (self.form.type == 'sink') ? 0 : self.form.initialBalance,
                type: self.form.type,
                ordering: self.form.ordering
            }

            if(typeof self.form.uid == "undefined" || self.form.uid == null) {

                self.$http.post('api/accounts', data).then(response => {
                    var account = response.body.account;

                    self.$swal({
                        title: 'Success',
                        text: 'Account saved.',
                        type: 'success'
                    }).then(function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    }, function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    })
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Data not saved.',
                        type: 'error'
                    })
                    self.$set(self.form, 'block', false)
                })
            } else {
                self.$http.put('api/accounts/'+self.form.uid, data).then(response => {
                    var account = response.body.account

                    self.$swal({
                        title: 'Success',
                        text: 'Account saved.',
                        type: 'success'
                    }).then(function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    }, function() {
                        self.getAccounts()
                        self.$set(self.form, 'show', false)
                    })
                    self.$set(self.form, 'block', false)
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Data not saved.',
                        type: 'error'
                    })
                    self.$set(self.form, 'block', false)
                })
            }
        }
    }
}
</script>
