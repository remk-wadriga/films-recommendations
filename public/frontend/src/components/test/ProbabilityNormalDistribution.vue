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
                step: "0.2",
                enabledCharts: 1,
                form1: {
                    mu: "0",
                    sigma: "1"
                },
                form2: {
                    mu: "0",
                    sigma: "1"
                },
                form3: {
                    mu: "0",
                    sigma: "1"
                },
                form4: {
                    mu: "0",
                    sigma: "1"
                },
                form5: {
                    mu: "0",
                    sigma: "1"
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
                    range: this.from + '-' + this.to,
                    step: this.step,
                    mu: this.form1.mu,
                    sigma: this.form1.sigma
                }
                let params2 = {
                    range: this.from + '-' + this.to,
                    step: this.step,
                    mu: this.form2.mu,
                    sigma: this.form2.sigma
                }
                let params3 = {
                    range: this.from + '-' + this.to,
                    step: this.step,
                    mu: this.form3.mu,
                    sigma: this.form3.sigma
                }
                let params4 = {
                    step: this.step,
                    range: this.from + '-' + this.to,
                    mu: this.form4.mu,
                    sigma: this.form4.sigma
                }
                let params5 = {
                    range: this.from + '-' + this.to,
                    step: this.step,
                    mu: this.form5.mu,
                    sigma: this.form5.sigma
                }

                // Get data for "normal distribution chart"
                this.chartLabelsD = []
                let valuesD1 = []; let valuesD2 = []; let valuesD3 = []; let valuesD4 = []; let valuesD5 = []

                let dataD1 = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params1)
                dataD1.forEach(elem => {
                    this.chartLabelsD.push(elem.index)
                    valuesD1.push(elem.value)
                })
                this.chartDataD = [{label: 'Normal1', data: valuesD1}]

                if (this.enabledCharts > 1 && (this.form2.mu !== this.form1.mu || this.form2.sigma !== this.form1.sigma)) {
                    let dataD2 = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params2)
                    dataD2.forEach(elem => { valuesD2.push(elem.value) })
                    this.chartDataD.push({label: 'Normal2', data: valuesD2})
                }
                if (this.enabledCharts > 2 && (this.form3.mu !== this.form1.mu || this.form3.sigma !== this.form1.sigma)) {
                    let dataD3 = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params3)
                    dataD3.forEach(elem => { valuesD3.push(elem.value) })
                    this.chartDataD.push({label: 'Normal3', data: valuesD3})
                }
                if (this.enabledCharts > 3 && (this.form4.mu !== this.form1.mu || this.form4.sigma !== this.form1.sigma)) {
                    let dataD4 = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params4)
                    dataD4.forEach(elem => { valuesD4.push(elem.value) })
                    this.chartDataD.push({label: 'Normal4', data: valuesD4})
                }
                if (this.enabledCharts > 4 && (this.form5.mu !== this.form1.mu || this.form5.sigma !== this.form1.sigma)) {
                    let dataD5 = await Vue.api.request(TEST_PROBABILITY_NORMAL_DISTRIBUTION_URL, params5)
                    dataD5.forEach(elem => { valuesD5.push(elem.value) })
                    this.chartDataD.push({label: 'Normal5', data: valuesD5})
                }


                // Get data for "CDF" chart
                this.chartLabelsCDF = []
                let valuesCDF1 = []; let valuesCDF2 = []; let valuesCDF3 = []; let valuesCDF4 = []; let valuesCDF5 = []

                let dataCDF1 = await Vue.api.request(TEST_PROBABILITY_NORMAL_CDF_URL, params1)
                dataCDF1.forEach(elem => {
                    this.chartLabelsCDF.push(elem.index)
                    valuesCDF1.push(elem.value)
                })
                this.chartDataCDF = [{label: 'CDF1', data: valuesCDF1}]

                if (this.form2.mu !== this.form1.mu || this.form2.sigma !== this.form1.sigma) {
                    let dataCDF2 = await Vue.api.request(TEST_PROBABILITY_NORMAL_CDF_URL, params2)
                    dataCDF2.forEach(elem => { valuesCDF2.push(elem.value) })
                    this.chartDataCDF.push({label: 'CDF2', data: valuesCDF2})
                }
                if (this.form3.mu !== this.form1.mu || this.form3.sigma !== this.form1.sigma) {
                    let dataCDF3 = await Vue.api.request(TEST_PROBABILITY_NORMAL_CDF_URL, params3)
                    dataCDF3.forEach(elem => { valuesCDF3.push(elem.value) })
                    this.chartDataCDF.push({label: 'CDF3', data: valuesCDF3})
                }
                if (this.form4.mu !== this.form1.mu || this.form4.sigma !== this.form1.sigma) {
                    let dataCDF4 = await Vue.api.request(TEST_PROBABILITY_NORMAL_CDF_URL, params4)
                    dataCDF4.forEach(elem => { valuesCDF4.push(elem.value) })
                    this.chartDataCDF.push({label: 'CDF4', data: valuesCDF4})
                }
                if (this.form5.mu !== this.form1.mu || this.form5.sigma !== this.form1.sigma) {
                    let dataCDF5 = await Vue.api.request(TEST_PROBABILITY_NORMAL_CDF_URL, params5)
                    dataCDF5.forEach(elem => { valuesCDF5.push(elem.value) })
                    this.chartDataCDF.push({label: 'CDF5', data: valuesCDF5})
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
            this.setPageTitle('Probability, normal distribution')
            this.setTopButtons([])

            this.setUpChart()
        }
    }
</script>

<style scoped>

</style>