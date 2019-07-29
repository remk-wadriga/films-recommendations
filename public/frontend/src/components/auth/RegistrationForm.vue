<template src="@/templates/auth/registration-form.html" />

<script>
    import { mapMutations } from 'vuex'
    import { SET_ACCESS_TOKEN_MUTATION, UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'

    const SEX_MALE = 'male'
    const SEX_FEMALE = 'female'

    export default {
        name: "RegistrationForm",
        data() {
            return {
                userParams: {
                    email: null,
                    firstName: null,
                    lastName: null,
                    sex: SEX_MALE,
                    age: null,
                    aboutMe: null,
                    password: null
                },
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
                setAccessToken: SET_ACCESS_TOKEN_MUTATION,
                unsetAccessToken: UNSET_ACCESS_TOKEN_MUTATION
            }),
            registerUser() {
                this.errors = []
                if (!this.userParams.email || !this.userParams.sex || !this.userParams.age || !this.userParams.password || !this.repeatPassword) {
                    this.errors.push('Params "Username", "Sex", "Age", "Password" and "Repeat password" are required!')
                }
                if (this.userParams.sex !== SEX_MALE && this.userParams.sex !== SEX_FEMALE) {
                    this.errors.push('Incorrect "Sex" value')
                }

                if (this.userParams.age && (this.userParams.age < this.minAge || this.userParams.age > this.maxAge)) {
                    this.errors.push('"Age" must be an integer between ' + this.minAge + ' and ' + this.maxAge + '!')
                }
                if (this.userParams.password !== this.repeatPassword) {
                    this.errors.push('Passwords are not match!')
                }

                if (this.errors.length > 0) {
                    return
                }

                this.userParams.plainPassword = {
                    first: this.userParams.password,
                    second: this.repeatPassword
                }
                delete this.userParams.password

                this.$http.post('registration', {'user_form': this.userParams}).then(response => this.registrationSuccessful(response), errorResponse => this.registrationFailed(errorResponse));
            },
            registrationSuccessful (response) {
                if (response.body['access_token'] === undefined) {
                    let message = response.body.message ? response.body.message : 'No access token given'
                    this.errors.push(message)
                    return
                }
                // Set mutate store with new access token (set access token to local store and add it to default requests's headers)
                this.setAccessToken(response.body['access_token'])
                // Redirect logged user to home page
                this.$router.push({name: 'app_homepage'})
            },
            registrationFailed (response) {
                let message = response.body.message ? response.body.message : 'Unknown error'
                this.errors.push(message)
            }
        },
        mounted () {
            this.unsetAccessToken()
        }
    }
</script>

<style scoped>

</style>