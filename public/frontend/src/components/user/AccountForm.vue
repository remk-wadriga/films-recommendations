<template src="@/templates/user/account-form.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'
    import { REGISTRATION_URL, ACCOUNT_UPDATE_URL } from '@/api/request-urls'

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
            async successFrom() {
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

                Vue.api.notFulfillStatuses = [400]
                let response = await Vue.api.post(this.user.isLogged ? ACCOUNT_UPDATE_URL : REGISTRATION_URL, {'user_form': params})
                if (response.isOk) {
                    this.registrationSuccessful(response)
                } else {
                    this.registrationFailed(response)
                }
            },
            registrationSuccessful (response) {
                // If this is new user - check the access token in response and login him
                if (!Vue.user.isLogged) {
                    if (response['access_token'] === undefined) {
                        let message = response.message ? response.message : 'No access token given'
                        this.errors.push(message)
                        return
                    }
                    // Set mutate store with new access token (set access token to local store and add it to default requests's headers)
                    this.setAccessToken({accessToken: response['access_token'], renewToken: response['renew_token']})

                    // Init user params
                    Vue.user.init()
                }

                // Redirect logged user to home page
                this.$router.push({name: 'user_account'})
            },
            registrationFailed (response) {
                let message = response.message ? response.message : 'Unknown error'
                this.errors.push(message)
            }
        }
    }
</script>

<style scoped>

</style>