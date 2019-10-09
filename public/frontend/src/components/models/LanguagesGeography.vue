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
                chartOptions: null,
                chartTooltipLabelCallback: null
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
                let labels = []
                this.chartData = []

                data.forEach(elem => {
                    if (values[elem.index] === undefined) {
                        values[elem.index] = {label: elem.index, data: []}
                    }
                    values[elem.index].data.push({
                        x: elem.value[0],
                        y: elem.value[1]
                    })
                    labels.push(elem.index)
                })

                for (let index in values) {
                    this.chartData.push(values[index])
                }

                this.chartTooltipLabelCallback = item => {
                    let label = labels[item.index]
                    let string = ' ' + label
                    if (values[label] !== undefined && values[label].data[item.index] !== undefined) {
                        let coordinates = values[label].data[item.index]
                        string += ' (' + coordinates.x + ', ' + coordinates.y + ')'
                    }
                    return string
                }

                this.chartLabels = labels
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