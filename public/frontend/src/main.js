// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import store from './store'
import VueResource from 'vue-resource'
import BootstrapVue from 'bootstrap-vue'
import Config from '@/config.js'
import { SET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

Vue.config.productionTip = true;
Vue.use(BootstrapVue)
Vue.use(VueResource)

// Configure HTTP component
Vue.http.options.root = Config.api.baseUrl
Vue.http.headers.common['Content-Type'] = 'application/json'

// If the access token exists in local store - set it to default HTTP headers
if (localStorage.accessToken) {
    store.commit(SET_ACCESS_TOKEN_MUTATION, localStorage.accessToken)
}

new Vue({
    el: '#app',
    router,
    store,
    components: { App },
    template: '<App/>'
})
