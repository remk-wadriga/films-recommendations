<template src="@/templates/test/users-count-to-friends-count.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_USERS_COUNT_TO_FRIENDS_COUNT_URL } from '@/api/request-urls'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "UsersCountToFriendsCount",
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
            this.setPageTitle('Users count to friends count relation')
            this.setTopButtons([])

            let data = await Vue.api.request(TEST_USERS_COUNT_TO_FRIENDS_COUNT_URL)
            let values = []

            data.forEach(elem => {
                this.chartLabels.push(elem.friends)
                values.push(elem.users)
            })

            this.chartData = [
                {
                    label: 'Users count',
                    data: values
                }
            ]
        }
    }
</script>

<style scoped>

</style>