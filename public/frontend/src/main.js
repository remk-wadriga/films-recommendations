// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import user from './user'
import api from './api'
import router from './router'
import store from './store'
import VueResource from 'vue-resource'
import BootstrapVue from 'bootstrap-vue'
import { SET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

Vue.config.productionTip = true;
Vue.use(BootstrapVue)
Vue.use(VueResource)

// If the access token exists in local store - set it to default HTTP headers
if (localStorage.accessToken) {
    store.commit(SET_ACCESS_TOKEN_MUTATION, {accessToken: localStorage.accessToken, renewToken: localStorage.renewToken})
}

Vue.api = api
Vue.api.init()

Vue.user = user
Vue.user.init()

new Vue({
    el: '#app',
    router,
    store,
    components: { App },
    template: '<App/>'
})