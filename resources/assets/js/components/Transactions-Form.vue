<template>
    <section class="section">
        <div class="is-clearfix has-margin-bottom-10">
            <div class="is-pulled-left">
                <router-link :to="'/transactions/list'" class="button is-link" exact><i class="fa fa-list"></i> Back to Transactions List</router-link>
            </div>
        </div>

        <div class="box" :class="accentType">
            <form>
                <div class="field">
                    <label class="label">Type</label>
                    <div class="switch-field">
                        <input id="type-i" type="radio" name="switch_3" value="i" v-model="form.type"/>
                        <label class="i" for="type-i">Income</label>
                        <input id="type-e" type="radio" name="switch_3" value="e" v-model="form.type"/>
                        <label class="e" for="type-e">Expense</em></label>
                        <input id="type-x" type="radio" name="switch_3" value="x" v-model="form.type"/>
                        <label class="x" for="type-x">Transfer</label>
                    </div>
                </div>
                <div class="columns">
                    <div class="column is-half">
                        <div class="field">
                            <label class="label">Account</label>
                            <div class="control is-expanded">
                                <div class="select is-fullwidth">
                                    <select v-model="form.account">
                                        <template v-for="i in accounts">
                                            <option :value="i.uid">{{ i.name }}</option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-half" v-if="form.type === 'x'">
                        <div class="field">
                            <label class="label">Transfer to</label>
                            <div class="control is-expanded">
                                <div class="select is-fullwidth">
                                    <select v-model="form.target">
                                        <template v-for="i in accounts">
                                            <option :value="i.uid">{{ i.name }}</option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Amount</label>
                    <div class="control">
                        <vue-numeric class="input has-text-right" currency="" separator=" " v-model="form.amount" :minus="false" :precision="2" name="amount"></vue-numeric>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Note</label>
                    <div class="control">
                        <textarea class="textarea" v-model="form.note"></textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Date</label>
                    <div class="control">
                        <input class="input" type="text" v-model="form.date"></input>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Tags</label>
                    <div class="control">
                        <input-tag class="input" :tags="form.tags"></input-tag>
                    </div>
                </div>
                <div class="field">
                    <div class="control has-text-right">
                        <button class="button is-primary" @click="submit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</template>

<script>
import VueNumeric from './input/Numeric.vue'
import InputTag from './input/InputTag.vue'
import moment from 'moment'

export default {
    name: 'transactions-form',
    props: ['transaction'],
    data: function() {
        return {
            accounts: [],
            form: {
                type: 'i',
                account: null,
                target: null,
                tags: [],
                amount: 0,
                note: "",
                date: moment().format("YYYY-MM-DD HH:mm"),
                block: false
            },
            temp: {
                account: null,
                target: null
            }
        }
    },
    components: {
        VueNumeric,
        InputTag
    },
    created: function() {
        var self = this

        self.$http.get('api/accounts').then(response => {
            self.accounts = response.body.accounts
            if(self.temp.account === null) {
                self.$set(self.form, 'account', self.accounts[0].uid)
            } else {
                self.$set(self.form, 'account', self.temp.account)
                self.$set(self.form, 'target', self.temp.target)
            }
        }, response => {
            self.accounts = []
            self.$set(self.form, 'account', null)
        })

        if(typeof self.transaction !== "undefined" && self.transaction !== null) {
            self.$set(self.form, 'block', true)
            self.$http.get('api/transactions/'+self.transaction).then(response => {
                self.$set(self.form, 'block', false)
                self.$set(self.form, 'type', response.body.transaction.type)
                self.$set(self.form, 'account', response.body.transaction.account.uid)
                self.$set(self.temp, 'account', response.body.transaction.account.uid)
                self.$set(self.form, 'target', response.body.transaction.target ? response.body.transaction.target.uid : null)
                self.$set(self.temp, 'target', response.body.transaction.target ? response.body.transaction.target.uid : null)
                self.$set(self.form, 'tags', response.body.transaction.tags)
                self.$set(self.form, 'amount', response.body.transaction.amount)
                self.$set(self.form, 'note', response.body.transaction.note)
                self.$set(self.form, 'date', moment(new Date(response.body.transaction.date)).format("YYYY-MM-DD HH:mm"))
            }, response => {
                self.$swal({
                    title: 'Not Found',
                    text: 'Could not find a transaction with that identifier.',
                    type: 'warning'
                }).then(function() {
                    self.$router.push('/transactions/list')
                })
            })
        }
    },
    computed: {
        accentType: function() {
            if(this.form.type == 'i') {
                return 'box-success'
            } else if(this.form.type == 'e') {
                return 'box-danger'
            } else if(this.form.type == 'x') {
                return 'box-primary'
            }
        }
    },
    methods: {
        submit: function(e) {
            e.preventDefault()
            var self = this

            if(self.form.block) {
                return false
            }

            self.$set(self.form, 'block', true)

            var data = {
                type: self.form.type,
                account: self.form.account,
                target: self.form.type === 'x' ? self.form.target : null,
                amount: self.form.amount,
                note: self.form.note,
                date: self.form.date,
                tags: self.form.tags
            }

            if(data.account === data.target) {
                self.$set(self.form, 'block', false)
                return false
            }

            if(typeof self.transaction == "undefined" || self.transaction == null) {

                self.$http.post('api/transactions', data).then(response => {
                    var transaction = response.body.transaction;

                    self.$swal({
                        title: 'Success',
                        text: 'Transaction saved.',
                        type: 'success'
                    }).then(function() {
                        self.goToList(transaction)
                    }, function() {
                        self.goToList(transaction)
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
                self.$http.put('api/transactions/'+self.transaction, data).then(response => {
                    var transaction = response.body.transaction

                    self.$swal({
                        title: 'Success',
                        text: 'Transaction saved.',
                        type: 'success'
                    }).then(function() {
                        self.goToList(transaction)
                    }, function() {
                        self.goToList(transaction)
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
        },

        goToList: function(transaction) {
            var self = this

            let date = new Date(transaction.date)
            self.$router.push('/transactions/list/'+date.getFullYear()+'/'+(parseInt(date.getMonth()+1))+'/'+transaction.uid)
        }
    }
}
</script>
