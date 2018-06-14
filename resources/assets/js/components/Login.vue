<template>
    <div class="container app-body">
        <div class="border border-top-0 p-4" v-if="activeTab == 'login'">
            <section class="hero is-primary">
                <div class="hero-body">
                    <div class="container">
                        <h1 class="title">
                            Login
                        </h1>
                    </div>
                </div>
            </section>
            <div class="columns is-marginless is-centered">
                <div class="column is-5">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">Login</p>
                        </header>

                        <div class="card-content">
                            <form class="login-form" @submit.prevent="login">

                                <div class="field is-horizontal">
                                    <div class="field-label">
                                        <label class="label">E-mail</label>
                                    </div>

                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control">
                                                <input class="input" id="email" type="email" name="email"
                                                       value="" required autofocus v-model="email">
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-label">
                                        <label class="label">Password</label>
                                    </div>

                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control">
                                                <input class="input" id="password" type="password" name="password" v-model="password" required>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-label"></div>

                                    <div class="field-body">
                                        <div class="field is-grouped">
                                            <div class="control">
                                                <button type="submit" class="button is-primary">Login</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border border-top-0 p-4" v-if="activeTab == 'register'">
            <section class="hero is-primary">
                <div class="hero-body">
                    <div class="container">
                        <h1 class="title">
                            Register
                        </h1>
                    </div>
                </div>
            </section>

            <div class="columns is-marginless is-centered">
                <div class="column is-5">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">Register</p>
                        </header>

                        <div class="card-content">
                            <form class="register-form" @submit.prevent="register">

                                <div class="field is-horizontal">
                                    <div class="field-label">
                                        <label class="label">Name</label>
                                    </div>

                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control">
                                                <input class="input" id="name" type="name" name="name" v-model="name" :class="{ 'is-invalid': validation.name }" required autofocus>
                                            </p>
                                            <p class="help is-danger" v-for="message in validation.name">{{ message }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-label">
                                        <label class="label">E-mail Address</label>
                                    </div>

                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control">
                                                <input class="input" id="email" type="email" name="email" v-model="email" :class="{ 'is-invalid': validation.email }" required autofocus>
                                            </p>
                                            <p class="help is-danger" v-for="message in validation.email">{{ message }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-label">
                                        <label class="label">Password</label>
                                    </div>

                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control">
                                                <input class="input" id="password" type="password" name="password" v-model="password" :class="{ 'is-invalid': validation.password }" required>
                                            </p>
                                            <p class="help is-danger" v-for="message in validation.password">{{ message }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-label">
                                        <label class="label">Confirm Password</label>
                                    </div>

                                    <div class="field-body">
                                        <div class="field">
                                            <p class="control">
                                                <input class="input" id="password-confirm" type="password" name="password_confirmation" v-model="password_confirmation" :class="{ 'is-invalid': validation.password }" required>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-horizontal">
                                    <div class="field-label"></div>

                                    <div class="field-body">
                                        <div class="field is-grouped">
                                            <div class="control">
                                                <button type="submit" class="button is-primary">Register</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {AUTH_REQUEST} from 'root/store/actions/auth'

export default {
    name: 'overview',
    data: function() {
        return {
            email: "",
            name: "",
            password: "",
            password_confirmation: "",
            state: 'ready',
            validation: {}
        }
    },
    props: {
        activeTab: {
            default: 'login',
            type: String
        }
    },
    methods: {
        toggleTab: function(to) {
            this.activeTab = to;
        },

        login: function () {
            if(this.state == 'loading') { return; }

            this.validation = {}

            const { email, password } = this
            this.$store.dispatch(AUTH_REQUEST, { email, password })
            .then(() => {
                this.state = 'success'
                this.$router.push('/overview')
            })
            .catch((err) => {
                this.state = 'error'
            })
        },

        register: function () {
            if(this.state == 'loading') { return; }

            var self = this
            self.validation = {}

            self.state = 'loading';
            let { email, name, password, password_confirmation } = self
            axios.post(
                'api/register',
                {
                    email,
                    name,
                    password,
                    password_confirmation
                }
            )
            .then(res => {
                this.$store.dispatch(AUTH_REQUEST, { email, password })
                .then(() => {
                    this.state = 'success'
                    this.$router.push('/overview')
                })
                .catch((err) => {
                    this.state = 'error'
                })
            })
            .catch(err => {
                this.state = 'error'

                if(err.response.status == 422) {
                    let validation = err.response.data.errors;
                    for(let field in validation) {
                        for(let error of validation[field]) {
                            let i = self.validation[field] ? self.validation[field] : [];
                            i.push(error)
                            self.$set(self.validation, field, i)
                        }
                    }
                }
            })
        }
    }
}
</script>
