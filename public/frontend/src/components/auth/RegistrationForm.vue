<template src="@/templates/auth/registration-form.html" />

<script>

    export default {
        name: "RegistrationForm",
        data() {
            return {
                email: null,
                firstName: null,
                lastName: null,
                password: null,
                repeatPassword: null
            }
        },
        methods: {
            registerUser() {

            }
        }
    }
</script>

<style scoped>

</style>