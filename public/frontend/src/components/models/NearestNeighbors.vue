<template src="@/templates/test/models-nearest-neighbors.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_MODELS_NEAREST_NEIGHBORS_URL } from '@/api/request-urls'
    import BubbleChart from '@/components/charts/BubbleChart'

    export default {
        name: "NearestNeighbors",
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
                let data = await Vue.api.request(TEST_MODELS_NEAREST_NEIGHBORS_URL)
                let values = []
                let labels = []

                data.forEach(elem => {
                    values.push({
                        x: elem.value[0],
                        y: elem.value[1]
                    })
                    labels.push(elem.index)
                })

                this.chartData = [
                    {
                        label: 'Point',
                        data: values
                    }
                ]

                this.chartTooltipLabelCallback = item => {
                    return ' ' + labels[item.index] + ' (' + values[item.index].x + ', ' + values[item.index].y + ')'
                }
            }
        },
        mounted () {
            this.setPageTitle('Models, nearest neighbors')
            this.setTopButtons([])

            this.setUpChart()
        }
    }
</script>

<style scoped>

</style>