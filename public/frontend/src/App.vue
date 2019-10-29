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
        ROUTE_LOGIN,
        ROUTE_REGISTRATION,
        ROUTE_ACCOUNT,
        ROUTE_TEST_INDEX,
        ROUTE_TEST_USERS_FRIENDS_COUNT,
        ROUTE_TEST_USERS_COUNT_TO_FRIENDS_COUNT,
        ROUTE_TEST_PROBABILITY_NORMAL_DISTRIBUTION,
        ROUTE_TEST_PROBABILITY_BINOMIAL_DISTRIBUTION,
        ROUTE_TEST_PROBABILITY_BETA_DISTRIBUTION,
        ROUTE_TEST_DATA_LANGUAGES_GEOGRAPHY,
        ROUTE_TEST_DATA_DISTANCES_FOR_DIMENSIONS,
        ROUTE_TEST_MODELS_SPAM_FILTER
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
                routeTestUsersCountToFriendsCount: ROUTE_TEST_USERS_COUNT_TO_FRIENDS_COUNT,
                routeTestProbabilityNormalDistribution: ROUTE_TEST_PROBABILITY_NORMAL_DISTRIBUTION,
                routeTestProbabilityBinomialDistribution: ROUTE_TEST_PROBABILITY_BINOMIAL_DISTRIBUTION,
                routeTestProbabilityBetaDistribution: ROUTE_TEST_PROBABILITY_BETA_DISTRIBUTION,
                routeTestDataLanguagesGeography: ROUTE_TEST_DATA_LANGUAGES_GEOGRAPHY,
                routeTestDataDistancesForDimensions: ROUTE_TEST_DATA_DISTANCES_FOR_DIMENSIONS,
                routeTestModelsSpamFilter: ROUTE_TEST_MODELS_SPAM_FILTER,
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
