<template src="@/templates/user/account-form.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

    const SEX_MALE = 'male'
    const SEX_FEMALE = 'female'

    export default {
        name: "AccountForm",
        data() {
            return {
                user: Vue.user,
                repeatPassword: null,
                sexNames: [
                    {value: SEX_MALE, text: 'Male'},
                    {value: SEX_FEMALE, text: 'Female'}
                ],
                minAge: 7,
                maxAge: 120,
                errors: []
            }
        },
        methods: {
            ...mapMutations({
                setAccessToken: SET_ACCESS_TOKEN_MUTATION
            }),
            successFrom() {
                this.errors = []
                if (!this.user.email || !this.user.sex || !this.user.age || (!this.user.isLogged && (!this.user.password || !this.repeatPassword))) {
                    this.errors.push('Params "Username", "Sex", "Age", "Password" and "Repeat password" are required!')
                }
                if (this.user.sex !== SEX_MALE && this.user.sex !== SEX_FEMALE) {
                    this.errors.push('Incorrect "Sex" value')
                }

                if (this.user.age && (this.user.age < this.minAge || this.user.age > this.maxAge)) {
                    this.errors.push('"Age" must be an integer between ' + this.minAge + ' and ' + this.maxAge + '!')
                }
                if (this.repeatPassword && this.user.password !== this.repeatPassword) {
                    this.errors.push('Passwords are not match!')
                }

                if (this.errors.length > 0) {
                    return
                }

                let params = {
                    email: this.user.email,
                    firstName: this.user.firstName,
                    lastName: this.user.lastName,
                    age: this.user.age,
                    sex: this.user.sex,
                    aboutMe: this.user.aboutMe
                }
                if (this.repeatPassword) {
                    params.plainPassword = {
                        first: this.user.password,
                        second: this.repeatPassword
                    }
                }

                let url = this.user.isLogged ? 'account' : 'registration'
                this.$http.post(url, {'user_form': params}).then(response => this.registrationSuccessful(response), errorResponse => this.registrationFailed(errorResponse));
            },
            registrationSuccessful (response) {
                // If this is new user - check the access token in response and login him
                if (!Vue.user.isLogged) {
                    if (response.body['access_token'] === undefined) {
                        let message = response.body.message ? response.body.message : 'No access token given'
                        this.errors.push(message)
                        return
                    }
                    // Set mutate store with new access token (set access token to local store and add it to default requests's headers)
                    this.setAccessToken(response.body['access_token'])

                    // Init user params
                    Vue.user.init()
                }

                // Redirect logged user to home page
                this.$router.push({name: 'user_account'})
            },
            registrationFailed (response) {
                let message = response.body.message ? response.body.message : 'Unknown error'
                this.errors.push(message)
            }
        }
    }
</script>

<style scoped>

</style>