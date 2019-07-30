<template src="@/templates/user/login-form.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_ACCESS_TOKEN_MUTATION, UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

    export default {
        name: "LoginForm",
        data () {
            return {
                username: '',
                password: ''
            }
        },
        methods: {
            ...mapMutations({
                setAccessToken: SET_ACCESS_TOKEN_MUTATION,
                unsetAccessToken: UNSET_ACCESS_TOKEN_MUTATION
            }),
            loginUser () {
                let body = {
                    username: this.username,
                    password: this.password
                }
                this.$http.post('login', body).then(response => this.loginSuccessful(response), errorResponse => this.loginFailed(errorResponse));
            },
            loginSuccessful (response) {
                if (!response.body['access_token']) {
                    this.loginFailed()
                    return false
                }

                // Set mutate store with new access token (set access token to local store and add it to default requests's headers)
                this.setAccessToken(response.body['access_token'])

                // Init user
                Vue.user.init()

                // Redirect logged user to home page
                this.$router.push({name: 'app_homepage'})
            },
            loginFailed () {
                this.unsetAccessToken()
            }
        }
    }
</script>

<style scoped>

</style>