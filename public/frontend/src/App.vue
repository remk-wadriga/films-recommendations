<template src="@/templates/app.html" />

<script>
    import 'bootstrap/dist/css/bootstrap.css'
    import 'bootstrap-vue/dist/bootstrap-vue.css'
    import Vue from 'vue'
    import Login from '@/components/auth/Login'
    import Logger from '@/components/Logger'
    import TopButtons from '@/components/TopButtons'
    import { mapMutations } from 'vuex'
    import {
        ROUTE_HOMEPAGE,
        ROUTE_TEST_INDEX,
        ROUTE_TEST_USERS_FRIENDS_COUNT,
        ROUTE_LOGIN,
        ROUTE_REGISTRATION,
        ROUTE_ACCOUNT
    } from '@/router/routes-list'
    import { UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'
    import { LOGOUT_URL } from '@/api/request-urls'

    export default {
        name: 'App',
        components: { Login, Logger, TopButtons },
        data () {
            return {
                user: Vue.user,
                routeHomepage: ROUTE_HOMEPAGE,
                routeTestPage: ROUTE_TEST_INDEX,
                routerTestUsersFriendsCountPage: ROUTE_TEST_USERS_FRIENDS_COUNT,
                routeLogin: ROUTE_LOGIN,
                routeRegistration: ROUTE_REGISTRATION,
                routeAccount: ROUTE_ACCOUNT
            }
        },
        methods: {
            ...mapMutations({
                unsetAccessToken: UNSET_ACCESS_TOKEN_MUTATION
            }),
            async logoutUser () {
                await Vue.api.post(LOGOUT_URL)
                this.logoutSuccessFull()
            },
            logoutSuccessFull () {
                // Remove access token
                this.unsetAccessToken()

                // Clear user params
                Vue.user.flush();

                // Redirect logged user to home page
                this.$router.push({name: ROUTE_HOMEPAGE})
            }
        },
        computed: {
            needLogin () {
                return !Vue.user.isLogged && this.$router.resolve({name: ROUTE_REGISTRATION}).route.path !== this.$route.path
            },
            pageTitle () {
                return this.$store.state.pageTitle
            }
        }
    }
</script>

<style src="@/assets/font-awesome.css" />
<style src="@/assets/app.css" />
