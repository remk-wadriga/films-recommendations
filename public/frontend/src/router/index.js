import Vue from 'vue'
import Router from 'vue-router'
import Login from '@/components/auth/Login'
import Registration from '@/components/auth/Registration'
import Account from '@/components/user/Account'

import FilmsList from '@/components/film/List'
import FilmCreate from '@/components/film/Create'
import FilmUpdate from '@/components/film/Update'
import {
    ROUTE_HOMEPAGE,
    ROUTE_LOGIN,
    ROUTE_REGISTRATION,
    ROUTE_ACCOUNT,
    ROUTE_FILM_CREATE,
    ROUTE_FILM_UPDATE
} from './routes-list'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: ROUTE_HOMEPAGE,
            component: FilmsList
        },
        {
            path: '/login',
            name: ROUTE_LOGIN,
            component: Login
        },
        {
            path: '/registration',
            name: ROUTE_REGISTRATION,
            component: Registration
        },
        {
            path: '/account',
            name: ROUTE_ACCOUNT,
            component: Account
        },
        {
            path: '/film/',
            name: ROUTE_FILM_CREATE,
            component: FilmCreate
        },
        {
            path: '/film/:id',
            name: ROUTE_FILM_UPDATE,
            component: FilmUpdate
        }
    ]
})
