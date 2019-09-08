<template src="@/templates/test/index.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "TestIndex",
        data () {
            return {

            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            })
        },
        mounted () {
            this.setPageTitle('Test page')
            this.setTopButtons([])
        }
    }
</script>

<style scoped>

</style>