<template src="@/templates/test/models-spam-filter.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'
    import { TEST_MODELS_SPAM_FILTER_URL } from '@/api/request-urls'
    import LineChart from '@/components/charts/LineChart'

    export default {
        name: "SpamFilter",
        components: { LineChart },
        data () {
            return {
                accuracyChartData: [],
                accuracyChartLabels: [],
                resultsChartData: [],
                resultsChartLabels: [],
                accuracyChartTooltipLabelCallback: null,
                resultsChartTooltipLabelCallback: null,
                form: {
                    k: 0.75
                }
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            }),
            async setUpCharts () {
                if (this.form.k === 0 || this.form.k === '') {
                    return
                }

                // Set up distances chart
                let predictions = await Vue.api.request(TEST_MODELS_SPAM_FILTER_URL, {k: this.form.k})
                let accuracyValues = []
                let completenessValues = []
                let accuracyLabels = []
                let resultsCorrectValues = []
                let resultsIncorrectValues = []
                let resultsLabels = []


                predictions.forEach(elem => {
                    accuracyLabels.push(elem.limit)
                    resultsLabels.push(elem.limit)

                    accuracyValues.push(elem.accuracy)
                    completenessValues.push(elem.completeness)

                    resultsCorrectValues.push(elem.correct)
                    resultsIncorrectValues.push(elem.incorrect)
                })

                this.accuracyChartData = [
                    {
                        label: 'Accuracy',
                        data: accuracyValues
                    },
                    {
                        label: 'Completeness',
                        data: completenessValues
                    }
                ]
                this.resultsChartData = [
                    {
                        label: 'Correct',
                        data: resultsCorrectValues
                    },
                    {
                        label: 'Incorrect',
                        data: resultsIncorrectValues
                    }
                ]
                this.accuracyChartLabels = accuracyLabels
                this.resultsChartLabels = resultsLabels

                this.accuracyChartTooltipLabelCallback = this.resultsChartTooltipLabelCallback = item => {
                    item[0].value = item[0].value + '%'
                }
            }
        },
        mounted () {
            this.setPageTitle('Models, spam-filter')
            this.setTopButtons([])

            this.setUpCharts()
        }
    }
</script>

<style scoped>

</style>