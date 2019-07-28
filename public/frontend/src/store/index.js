import Vue from 'vue'
import Vuex from 'vuex'
import { SET_ACCESS_TOKEN_MUTATION, UNSET_ACCESS_TOKEN_MUTATION } from './mutation-types'

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        accessToken: null
    },
    mutations: {
        [ SET_ACCESS_TOKEN_MUTATION ] (state, value) {
            state.accessToken = value
            localStorage.accessToken = value
            Vue.http.headers.common['Authorization'] = 'Bearer ' + value
        },
        [ UNSET_ACCESS_TOKEN_MUTATION ] (state) {
            state.accessToken = null
            delete localStorage.accessToken
            delete Vue.http.headers.common['Authorization']
        }
    }
})