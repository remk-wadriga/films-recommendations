<template src="@/templates/test/data-distances-for-dimensions.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_DATA_DISTANCES_FOR_DIMENSIONS_URL } from '@/api/request-urls'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "DistancesForDimensions",
        components: { LineChart },
        data () {
            return {
                distancesChartData: [],
                distancesChartLabels: [],
                ratioChartData: [],
                ratioChartLabels: [],
                form: {
                    from: 1,
                    to: 100
                }
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            }),
            async setUpCharts () {
                if (this.form.from <= 0 || this.form.to <= 0) {
                    return
                }

                // Set up distances chart
                let distances = await Vue.api.request(TEST_DATA_DISTANCES_FOR_DIMENSIONS_URL, {range: this.form.from + '-' + this.form.to})
                let distancesMinValues = []
                let distancesAvgValues = []
                let ratioValues = []
                let distancesLabels = []

                distances.forEach(elem => {
                    distancesLabels.push(elem.index)
                    distancesMinValues.push(elem.min)
                    distancesAvgValues.push(elem.avg)
                    ratioValues.push(elem.min / elem.avg)
                })

                this.distancesChartData = [
                    {
                        label: 'Min distance',
                        data: distancesMinValues
                    },
                    {
                        label: 'Avg distance',
                        data: distancesAvgValues
                    }
                ]
                this.ratioChartData = [
                    {
                        label: 'Ratio',
                        data: ratioValues
                    }
                ]
                this.distancesChartLabels = distancesLabels
                this.ratioChartLabels = distancesLabels
            }
        },
        mounted () {
            this.setPageTitle('Data, distances for dimensions')
            this.setTopButtons([])

            this.setUpCharts()
        }
    }
</script>

<style scoped>

</style>