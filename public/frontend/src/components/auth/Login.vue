<template src="@/templates/auth/login.html" />

<script>
    import LoginForm from '@/components/user/LoginForm'
    import { ROUTE_HOMEPAGE, ROUTE_REGISTRATION } from '@/router/routes-list'

    export default {
        name: "Login",
        data () {
            return {
                routeHomepage: ROUTE_HOMEPAGE,
                routeRegistration: ROUTE_REGISTRATION
            }
        },
        components: { LoginForm }
    }
</script>

<style scoped>

</style>