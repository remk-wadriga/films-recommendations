<template src="@/templates/user/account.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import AccountForm from '@/components/user/AccountForm'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "Account",
        components: { AccountForm },
        data () {
            return {
                user: Vue.user
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            })
        },
        mounted () {
            this.setPageTitle('Account')
            this.setTopButtons([])
        }
    }
</script>

<style scoped>

</style>