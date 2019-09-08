<template src="@/templates/test/users-friends-count.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_USERS_FRIENDS_COUNT_URL } from '@/api/request-urls'

    export default {
        name: "UsersFriendsCount",
        data () {
            return {
                data: [1, 2, 3]
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            })
        },
        async mounted () {
            this.setPageTitle('Users friends count')
            this.setTopButtons([])

            this.data = await Vue.api.request(TEST_USERS_FRIENDS_COUNT_URL)
        }
    }
</script>

<style scoped>

</style>