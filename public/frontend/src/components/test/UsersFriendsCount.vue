<template src="@/templates/test/users-friends-count.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_USERS_FRIENDS_COUNT_URL } from '@/api/request-urls'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "UsersFriendsCount",
        components: { BarChart },
        data () {
            return {
                chartLabels: [],
                chartData: [],
                chartOptions: null
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

            let users = await Vue.api.request(TEST_USERS_FRIENDS_COUNT_URL)
            let values = []

            users.forEach(user => {
                this.chartLabels.push(user.name)
                values.push(user.count)
            })

            this.chartData = [
                {
                    label: 'Friends count',
                    data: values
                }
            ]
        }
    }
</script>

<style scoped>

</style>