import Vue from 'vue'
import Router from 'vue-router'
import Login from '@/components/auth/Login'
import Registration from '@/components/auth/Registration'
import Account from '@/components/user/Account'

import FilmsList from '@/components/film/List'
import FilmCreate from '@/components/film/Create'
import FilmView from '@/components/film/View'
import FilmUpdate from '@/components/film/Update'

import TestIndex from '@/components/test/TestIndex'
import UsersFriendsCount from '@/components/test/UsersFriendsCount'
import UsersCountToFriendsCount from '@/components/test/UsersCountToFriendsCount'
import ProbabilityNormalDistribution from '@/components/test/ProbabilityNormalDistribution'

import {
    ROUTE_HOMEPAGE,
    ROUTE_LOGIN,
    ROUTE_REGISTRATION,
    ROUTE_ACCOUNT,
    ROUTE_FILM_CREATE,
    ROUTE_FILM_VIEW,
    ROUTE_FILM_UPDATE,
    ROUTE_TEST_INDEX,
    ROUTE_TEST_USERS_FRIENDS_COUNT,
    ROUTE_TEST_USERS_COUNT_TO_FRIENDS_COUNT,
    ROUTE_TEST_PROBABILITY_NORMAL_DISTRIBUTION
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
            path: '/film/create',
            name: ROUTE_FILM_CREATE,
            component: FilmCreate
        },
        {
            path: '/film/:id',
            name: ROUTE_FILM_VIEW,
            component: FilmView
        },
        {
            path: '/film/:id/update',
            name: ROUTE_FILM_UPDATE,
            component: FilmUpdate
        },
        {
            path: '/test',
            name: ROUTE_TEST_INDEX,
            component: TestIndex
        },
        {
            path: '/test/users/friends-count',
            name: ROUTE_TEST_USERS_FRIENDS_COUNT,
            component: UsersFriendsCount
        },
        {
            path: '/test/users/count-to-friends-count-relation',
            name: ROUTE_TEST_USERS_COUNT_TO_FRIENDS_COUNT,
            component: UsersCountToFriendsCount
        },
        {
            path: '/test/probability/normal-distribution',
            name: ROUTE_TEST_PROBABILITY_NORMAL_DISTRIBUTION,
            component: ProbabilityNormalDistribution
        }
    ]
})
