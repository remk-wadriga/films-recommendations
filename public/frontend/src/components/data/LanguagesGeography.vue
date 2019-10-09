<template src="@/templates/test/data-languages-geography.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_DATA_LANGUAGES_GEOGRAPHY_URL } from '@/api/request-urls'
    import BubbleChart from '@/components/charts/BubbleChart'

    export default {
        name: "LanguagesGeography",
        components: { BubbleChart },
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
            }),
            async setUpChart () {
                let data = await Vue.api.request(TEST_DATA_LANGUAGES_GEOGRAPHY_URL)
                let values = {}

                data.forEach(elem => {
                    if (values[elem.index] === undefined) {
                        values[elem.index] = {label: elem.index, data: []}
                    }
                    values[elem.index].data.push(elem.value)
                })

                for (let index in values) {
                    this.chartData.push(values[index])
                }
            }
        },
        mounted () {
            this.setPageTitle('Data, languages geography')
            this.setTopButtons([])

            this.setUpChart()
        }
    }
</script>

<style scoped>

</style>