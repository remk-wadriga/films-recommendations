<template src="@/templates/test/probability-normal-distribution.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL } from '@/api/request-urls'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "ProbabilityNormalDistribution",
        components: { LineChart },
        data () {
            return {
                chartLabels: [],
                chartData: [],
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
                this.chartLabels = []
                this.chartData = []

                let params = {
                    range: this.from + '-' + this.to,
                    mu: this.mu,
                    sigma: this.sigma
                }

                let data = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params)
                let values = []

                data.forEach(elem => {
                    this.chartLabels.push(elem.index)
                    values.push(elem.value)
                })

                this.chartData = [
                    {
                        label: 'Value',
                        data: values
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