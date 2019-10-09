<template src="@/templates/test/data-languages-geography.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_DATA_LANGUAGES_GEOGRAPHY_URL, TEST_DATA_LANGUAGES_GEOGRAPHY_KNN_PREDICTIONS_URL } from '@/api/request-urls'
    import BubbleChart from '@/components/charts/BubbleChart'
    import BarChart from '@/components/charts/BarChart'

    export default {
        name: "LanguagesGeography",
        components: { BubbleChart, BarChart },
        data () {
            return {
                languagesChartData: [],
                languagesChartOptions: null,
                predictionsChartLabels: [],
                predictionsChartData: [],
                predictionsChartOptions: null,
                predictionsChartTooltipLabelCallback: null,
                form: {
                    from: 1,
                    to: 7
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

                // Set up languages chart
                let languages = await Vue.api.request(TEST_DATA_LANGUAGES_GEOGRAPHY_URL)
                let languagesValues = {}

                languages.forEach(elem => {
                    if (languagesValues[elem.index] === undefined) {
                        languagesValues[elem.index] = {label: elem.index, data: []}
                    }
                    languagesValues[elem.index].data.push(elem.value)
                })

                for (let index in languagesValues) {
                    this.languagesChartData.push(languagesValues[index])
                }


                // Set up predictions chart
                let predictions = await Vue.api.request(TEST_DATA_LANGUAGES_GEOGRAPHY_KNN_PREDICTIONS_URL, {range: this.form.from + '-' + this.form.to})
                let predictionsValues = []
                let predictionsLabels = []

                predictions.forEach(elem => {
                    this.predictionsChartLabels.push(elem.name)
                    predictionsValues.push(elem.correctCount)
                    predictionsLabels.push(elem.correctLabel)
                })

                this.predictionsChartData = [
                    {
                        label: 'Prediction results',
                        data: predictionsValues
                    }
                ]

                this.predictionsChartTooltipLabelCallback = item => {
                    return 'Result: ' + predictionsLabels[item.index]
                }
            }
        },
        mounted () {
            this.setPageTitle('Data, languages geography')
            this.setTopButtons([])

            this.setUpCharts()
        }
    }
</script>

<style scoped>

</style>