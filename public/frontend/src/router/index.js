import Vue from 'vue'
import Router from 'vue-router'
import Content from '@/components/Content'
import LoginForm from '@/components/auth/LoginForm'
import RegistrationForm from '@/components/auth/RegistrationForm'

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
        component: LoginForm
    },
        {
            path: '/registration',
            name: 'app_registration',
            component: RegistrationForm
        }
]
})
