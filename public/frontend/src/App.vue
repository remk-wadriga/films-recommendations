<template src="@/templates/app.html" />

<script>
    import 'bootstrap/dist/css/bootstrap.css'
    import 'bootstrap-vue/dist/bootstrap-vue.css'
    import Vue from 'vue'
    import Login from '@/components/auth/Login.vue'
    import { mapMutations } from 'vuex'
    import { UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

    export default {
        name: 'App',
        components: { Login },
        data () {
            return {
                user: Vue.user
            }
        },
        methods: {
            ...mapMutations({
                unsetAccessToken: UNSET_ACCESS_TOKEN_MUTATION
            }),
            logoutUser () {
                this.$http.post('logout').then(response => {
                    // Remove access token
                    this.unsetAccessToken()

                    // Clear user params
                    Vue.user.flush();

                    // Redirect logged user to home page
                    this.$router.push({name: 'app_homepage'})
                });
            }
        },
        computed: {
            needLogin () {
                return !Vue.user.isLogged && this.$router.resolve({name: 'app_registration'}).route.path !== this.$route.path
            }
        }
    }
</script>

<style src="@/assets/font-awesome.css" />
<style src="@/assets/app.css" />
