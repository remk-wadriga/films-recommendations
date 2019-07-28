<template src="@/templates/auth/registration-form.html" />

<script>
    import { mapMutations } from 'vuex'
    import { SET_ACCESS_TOKEN_MUTATION, UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

    export default {
        name: "RegistrationForm",
        data() {
            return {
                username: null,
                firstName: null,
                lastName: null,
                password: null,
                repeatPassword: null
            }
        },
        methods: {
            ...mapMutations({
                setAccessToken: SET_ACCESS_TOKEN_MUTATION,
                unsetAccessToken: UNSET_ACCESS_TOKEN_MUTATION
            }),
            registerUser() {
                let body = {
                    username: this.username,
                    firstName: this.firstName,
                    lastName: this.lastName,
                    password: this.password
                }
                this.$http.post('register', body).then(response => this.registrationSuccessful(response), errorResponse => this.registrationFailed(errorResponse));
            },
            registrationSuccessful (response) {
                console.log(response)
            },
            registrationFailed (response) {
                console.log(response)
            }
        },
        mounted () {
            this.unsetAccessToken()
        }
    }
</script>

<style scoped>

</style>