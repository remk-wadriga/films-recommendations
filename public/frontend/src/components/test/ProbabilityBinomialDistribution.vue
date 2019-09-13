<template src="@/templates/test/probability-binomial-distribution.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL } from '@/api/request-urls'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "ProbabilityBinomialDistribution",
        components: { LineChart },
        data () {
            return {
                chartLabels: [],
                chartData: [],
                chartOptions: null,
                step: "0.3",
                enabledCharts: 1,
                form1: {
                    p: "0.5",
                    n: "20"
                },
                form2: {
                    p: "0.5",
                    n: "20"
                },
                form3: {
                    p: "0.5",
                    n: "20"
                },
                form4: {
                    p: "0.5",
                    n: "20"
                },
                form5: {
                    p: "0.5",
                    n: "20"
                }
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            }),
            async setUpChart () {
                if (parseFloat(this.step) <= 0) {
                    return
                }

                let params1 = {
                    step: this.step,
                    p: this.form1.p,
                    n: this.form1.n
                }
                let params2 = {
                    step: this.step,
                    p: this.form2.p,
                    n: this.form2.n
                }
                let params3 = {
                    step: this.step,
                    p: this.form3.p,
                    n: this.form3.n
                }
                let params4 = {
                    step: this.step,
                    p: this.form4.p,
                    n: this.form4.n
                }
                let params5 = {
                    step: this.step,
                    p: this.form4.p,
                    n: this.form4.n
                }

                this.chartLabels = []
                let values1 = []; let values2 = []; let values3 = []; let values4 = []; let values5 = []

                let data1 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params1)
                data1.forEach(elem => {
                    this.chartLabels.push(elem.index)
                    values1.push(elem.value)
                })
                this.chartData = [{label: 'Binomial1', data: values1}]

                if (this.enabledCharts > 1 && (this.form2.p !== this.form1.p || this.form2.n !== this.form1.n)) {
                    let data2 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params2)
                    data2.forEach(elem => { values2.push(elem.value) })
                    this.chartData.push({label: 'Binomial2', data: values2})
                }
                if (this.enabledCharts > 2 && (this.form3.p !== this.form1.p || this.form3.n !== this.form1.n)) {
                    let data3 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params3)
                    data3.forEach(elem => { values3.push(elem.value) })
                    this.chartData.push({label: 'Binomial3', data: values3})
                }
                if (this.enabledCharts > 3 && (this.form4.p !== this.form1.p || this.form4.n !== this.form1.n)) {
                    let data4 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params4)
                    data4.forEach(elem => { values4.push(elem.value) })
                    this.chartData.push({label: 'Binomial4', data: values4})
                }
                if (this.enabledCharts > 4 && (this.form5.p !== this.form1.p || this.form5.n !== this.form1.n)) {
                    let data5 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params5)
                    data5.forEach(elem => { values5.push(elem.value) })
                    this.chartData.push({label: 'Binomial5', data: values5})
                }
            },
            addChart () {
                this.enabledCharts++
                this.setUpChart()
            },
            removeChart () {
                this.enabledCharts--
                this.setUpChart()
            }
        },
        mounted () {
            this.setPageTitle('Probability, binomial distribution')
            this.setTopButtons([])

            this.setUpChart()
        }
    }
</script>

<style scoped>

</style>