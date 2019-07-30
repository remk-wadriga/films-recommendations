<template src="@/templates/user/account.html" />

<script>
    import Vue from 'vue'
    import AccountForm from '@/components/user/AccountForm'

    export default {
        name: "Account",
        components: { AccountForm },
        data () {
            return {
                user: Vue.user
            }
        }
    }
</script>

<style scoped>

</style>