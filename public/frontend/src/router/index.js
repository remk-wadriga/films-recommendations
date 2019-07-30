import Vue from 'vue'
import Router from 'vue-router'
import Content from '@/components/Content'
import Login from '@/components/auth/Login'
import Registration from '@/components/auth/Registration'
import Account from '@/components/user/Account'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: 'app_homepage',
            component: Content
        },
        {
            path: '/login',
            name: 'app_login',
            component: Login
        },
        {
            path: '/registration',
            name: 'app_registration',
            component: Registration
        },
        {
            path: '/account',
            name: 'user_account',
            component: Account
        }
    ]
})
