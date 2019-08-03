<template src="@/templates/user/login-form.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { ROUTE_HOMEPAGE } from '@/router/routes-list'
    import { SET_ACCESS_TOKEN_MUTATION, UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'
    import { LOGIN_URL } from '@/api/request-urls'

    export default {
        name: "LoginForm",
        data () {
            return {
                username: null,
                password: null
            }
        },
        methods: {
            ...mapMutations({
                setAccessToken: SET_ACCESS_TOKEN_MUTATION,
                unsetAccessToken: UNSET_ACCESS_TOKEN_MUTATION
            }),
            async loginUser () {
                let params = {
                    username: this.username,
                    password: this.password
                }

                let response = await Vue.api.request(LOGIN_URL, params)
                if (response.isOk) {
                    this.loginSuccessful(response)
                } else {
                    this.loginFailed(response)
                }
            },
            loginSuccessful (response) {
                if (!response['access_token']) {
                    this.loginFailed()
                    return false
                }

                // Set mutate store with new access token (set access token to local store and add it to default requests's headers)
                this.setAccessToken({accessToken: response['access_token'], renewToken: response['renew_token']})

                // Init user
                Vue.user.init()

                // Redirect logged user to home page
                this.$router.push({name: ROUTE_HOMEPAGE})
            },
            loginFailed (response) {
                //this.unsetAccessToken()
            }
        }
    }
</script>

<style scoped>

</style>