<template src="@/templates/auth/registration.html" />

<script>
    import AccountForm from '@/components/user/AccountForm'
    import { ROUTE_HOMEPAGE, ROUTE_LOGIN } from '@/router/routes-list'

    export default {
        name: "Registration",
        data () {
            return {
                routeHomepage: ROUTE_HOMEPAGE,
                routeLogin: ROUTE_LOGIN
            }
        },
        components: { AccountForm }
    }
</script>

<style scoped>

</style>