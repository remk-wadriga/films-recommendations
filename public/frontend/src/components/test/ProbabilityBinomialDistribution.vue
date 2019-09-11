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
                n: "100",
                form1: {
                    p: "0.5"
                },
                form2: {
                    p: "0.5"
                },
                form3: {
                    p: "0.5"
                },
                form4: {
                    p: "0.5"
                },
                form5: {
                    p: "0.5"
                }
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            }),
            async setUpChart () {
                let params1 = {
                    n: this.n,
                    p: this.form1.p
                }
                let params2 = {
                    n: this.n,
                    p: this.form2.p
                }
                let params3 = {
                    n: this.n,
                    p: this.form3.p
                }
                let params4 = {
                    n: this.n,
                    p: this.form4.p
                }
                let params5 = {
                    n: this.n,
                    p: this.form5.p
                }

                this.chartLabels = []
                let values1 = []; let values2 = []; let values3 = []; let values4 = []; let values5 = []

                let data1 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params1)
                data1.forEach(elem => {
                    this.chartLabels.push(elem.index)
                    values1.push(elem.value)
                })
                this.chartData = [{label: 'Binomial1', data: values1}]

                if (this.form2.p !== this.form1.p) {
                    let data2 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params2)
                    data2.forEach(elem => { values2.push(elem.value) })
                    this.chartData.push({label: 'Binomial2', data: values2})
                }
                if (this.form3.p !== this.form1.p) {
                    let data3 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params3)
                    data3.forEach(elem => { values3.push(elem.value) })
                    this.chartData.push({label: 'Binomial3', data: values3})
                }
                if (this.form4.p !== this.form1.p) {
                    let data4 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params4)
                    data4.forEach(elem => { values4.push(elem.value) })
                    this.chartData.push({label: 'Binomial4', data: values4})
                }
                if (this.form5.p !== this.form1.p) {
                    let data5 = await Vue.api.request(TEST_PROBABILITY_BINOMIAL_DISTRIBUTION_URL, params5)
                    data5.forEach(elem => { values5.push(elem.value) })
                    this.chartData.push({label: 'Binomial5', data: values5})
                }
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