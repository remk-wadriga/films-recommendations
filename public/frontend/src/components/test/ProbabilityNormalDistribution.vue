<template src="@/templates/test/probability-normal-distribution.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, TEST_PROBABILITY_NORMAL_CDF_URL } from '@/api/request-urls'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "ProbabilityNormalDistribution",
        components: { LineChart },
        data () {
            return {
                chartLabelsD: [],
                chartDataD: [],
                chartLabelsCDF: [],
                chartDataCDF: [],
                chartOptions: null,
                from: -5,
                to: 5,
                mu: 0,
                sigma: 1
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            }),
            async setUpChart () {
                let params = {
                    range: this.from + '-' + this.to,
                    mu: this.mu,
                    sigma: this.sigma
                }

                // Get data for "normal distribution chart"
                this.chartLabelsD = []
                let dataD = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params)
                let valuesD1 = []
                dataD.forEach(elem => {
                    this.chartLabelsD.push(elem.index)
                    valuesD1.push(elem.value)
                })
                this.chartDataD = [
                    {
                        label: 'Value1',
                        data: valuesD1
                    }
                ]

                // Get data for "CDF" chart
                this.chartLabelsCDF = []
                let dataCDF = await Vue.api.request(TEST_PROBABILITY_NORMAL_CDF_URL, params)
                let valuesCDF = []
                dataCDF.forEach(elem => {
                    this.chartLabelsCDF.push(elem.index)
                    valuesCDF.push(elem.value)
                })
                this.chartDataCDF = [
                    {
                        label: 'Value',
                        data: valuesCDF
                    }
                ]
            }
        },
        mounted () {
            this.setPageTitle('Probability, normal distribution')
            this.setTopButtons([])

            this.setUpChart()
        }
    }
</script>

<style scoped>

</style>