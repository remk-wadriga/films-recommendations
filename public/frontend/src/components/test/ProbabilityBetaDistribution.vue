<template src="@/templates/test/probability-beta-distribution.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_PROBABILITY_BETA_DISTRIBUTION_URL } from '@/api/request-urls'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "ProbabilityBetaDistribution",
        components: { LineChart },
        data () {
            return {
                chartLabels: [],
                chartData: [],
                chartOptions: null,
                step: "0.02",
                form1: {
                    alpha: "4",
                    beta: "16"
                },
                form2: {
                    alpha: "10",
                    beta: "10"
                },
                form3: {
                    alpha: "16",
                    beta: "4"
                },
                form4: {
                    alpha: "4",
                    beta: "16"
                },
                form5: {
                    alpha: "4",
                    beta: "16"
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
                    step: this.step,
                    alpha: this.form1.alpha,
                    beta: this.form1.beta
                }
                let params2 = {
                    step: this.step,
                    alpha: this.form2.alpha,
                    beta: this.form2.beta
                }
                let params3 = {
                    step: this.step,
                    alpha: this.form3.alpha,
                    beta: this.form3.beta
                }
                let params4 = {
                    step: this.step,
                    alpha: this.form4.alpha,
                    beta: this.form4.beta
                }
                let params5 = {
                    step: this.step,
                    alpha: this.form5.alpha,
                    beta: this.form5.beta
                }

                this.chartLabels = []
                let values1 = []; let values2 = []; let values3 = []; let values4 = []; let values5 = []

                let data1 = await Vue.api.request(TEST_PROBABILITY_BETA_DISTRIBUTION_URL, params1)
                data1.forEach(elem => {
                    this.chartLabels.push(elem.index)
                    values1.push(elem.value)
                })
                this.chartData = [{label: 'Beta1', data: values1}]

                if (this.form2.alpha !== this.form1.alpha || this.form2.beta !== this.form1.beta) {
                    let data2 = await Vue.api.request(TEST_PROBABILITY_BETA_DISTRIBUTION_URL, params2)
                    data2.forEach(elem => { values2.push(elem.value) })
                    this.chartData.push({label: 'Beta2', data: values2})
                }
                if (this.form3.alpha !== this.form1.alpha || this.form3.beta !== this.form1.beta) {
                    let data3 = await Vue.api.request(TEST_PROBABILITY_BETA_DISTRIBUTION_URL, params3)
                    data3.forEach(elem => { values3.push(elem.value) })
                    this.chartData.push({label: 'Beta3', data: values3})
                }
                if (this.form4.alpha !== this.form1.alpha || this.form4.beta !== this.form1.beta) {
                    let data4 = await Vue.api.request(TEST_PROBABILITY_BETA_DISTRIBUTION_URL, params4)
                    data4.forEach(elem => { values4.push(elem.value) })
                    this.chartData.push({label: 'Beta4', data: values4})
                }
                if (this.form5.alpha !== this.form1.alpha || this.form5.beta !== this.form1.beta) {
                    let data5 = await Vue.api.request(TEST_PROBABILITY_BETA_DISTRIBUTION_URL, params5)
                    data5.forEach(elem => { values5.push(elem.value) })
                    this.chartData.push({label: 'Beta5', data: values5})
                }
            }
        },
        mounted () {
            this.setPageTitle('Probability, beta distribution')
            this.setTopButtons([])

            this.setUpChart()
        }
    }
</script>

<style scoped>

</style>