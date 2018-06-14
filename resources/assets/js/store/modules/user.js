import { USER_REQUEST, USER_ERROR, USER_SUCCESS } from 'root/store/actions/user'
import { AUTH_LOGOUT } from 'root/store/actions/auth'
import Vue from 'vue'

const state = { status: '', profile: {} }

const getters = {
    getProfile: state => state.profile,
    isProfileLoaded: state => !!state.profile.name,
}

const actions = {
    [USER_REQUEST]: ({commit, dispatch}) => {
        return new Promise((resolve, reject) => {
            commit(USER_REQUEST)
            axios.get('api/user')
            .then(resp => {
                commit(USER_SUCCESS, resp)
                resolve(resp)
            })
            .catch(err => {
                commit(USER_ERROR)
                dispatch(AUTH_LOGOUT)
                reject(err)
            })
        })
    },
}

const mutations = {
    [USER_REQUEST]: (state) => {
        state.status = 'loading'
    },
    [USER_SUCCESS]: (state, resp) => {
        state.status = 'success'
        Vue.set(state, 'profile', resp.data)
    },
    [USER_ERROR]: (state) => {
        state.status = 'error'
    },
    [AUTH_LOGOUT]: (state) => {
        state.profile = {}
    }
}

export default {
    state,
    getters,
    actions,
    mutations,
}
