import Vue from 'vue'
import Vuex from 'vuex'
import { SET_ACCESS_TOKEN_MUTATION,
    UNSET_ACCESS_TOKEN_MUTATION,
    ADD_LOGGER_MESSAGE_MUTATION,
    DELETE_LOGGER_MESSAGE_MUTATION,
    SET_PAGE_TITLE_MUTATION,
    SET_TOP_BUTTONS_MUTATION
} from './mutation-types'

Vue.use(Vuex);

let index = 0;

export default new Vuex.Store({
    state: {
        accessToken: null,
        renewToken: null,
        pageTitle: null,
        loggerMessages: [],
        pageTopButtons: []
    },
    mutations: {
        [ SET_ACCESS_TOKEN_MUTATION ] (state, value) {
            state.accessToken = value.accessToken
            state.renewToken = value.renewToken

            localStorage.accessToken = value.accessToken
            localStorage.renewToken = value.renewToken

            if (Vue.api !== undefined) {
                Vue.api.init()
            }
        },
        [ UNSET_ACCESS_TOKEN_MUTATION ] (state) {
            state.accessToken = null
            state.renewToken = null

            delete localStorage.accessToken
            delete localStorage.renewToken

            delete Vue.http.headers.common['Authorization']
        },
        [ ADD_LOGGER_MESSAGE_MUTATION ] (state, msg, liveTime = 10000) {
            if (msg.id === undefined) {
                index++;
                msg.id = 'logger_message_' + index;
            }
            state.loggerMessages.push(msg)
            setTimeout(() => {
                this.commit(DELETE_LOGGER_MESSAGE_MUTATION, msg.id)
            }, liveTime)
        },
        [ DELETE_LOGGER_MESSAGE_MUTATION ] (state, id) {
            state.loggerMessages.forEach((msg, i) => {
                if (msg.id === id) {
                    state.loggerMessages.splice(i, 1)
                }
            })
        },

        [ SET_PAGE_TITLE_MUTATION ] (state, title) {
            state.pageTitle = title
        },
        [ SET_TOP_BUTTONS_MUTATION ] (state, buttons) {
            buttons.forEach(btn => {
                btn.click = JSON.stringify(btn.click)
            })
            state.pageTopButtons = buttons
        }
    }
})