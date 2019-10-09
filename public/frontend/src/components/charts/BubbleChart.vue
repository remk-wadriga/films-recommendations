<script>
    //Importing Bubble class from the vue-chartjs wrapper
    import { Bubble } from 'vue-chartjs'
    //Exporting this so it can be used in other components
    export default {
        extends: Bubble,
        props: {
            labels: Array,
            data: Array,
            options: Object,
            tooltipTitleCallback: Function,
            tooltipLabelCallback: Function,
            onClick: Function,
            backgroundColor: String,
            pointBackgroundColor: String,
            borderWidth: String,
            pointBorderColor: String
        },
        watch: {
            data() {
                this.render()
            }
        },
        methods: {
            render () {
                let options = {
                    responsive: true,
                    maintainAspectRatio: false
                }
                if (this.options) {
                    options = this.options
                }
                if (!this.data) {
                    this.data = []
                }

                if (this.tooltipTitleCallback || this.tooltipLabelCallback) {
                    if (options.tooltips === undefined) {
                        options.tooltips = {
                            callbacks: {}
                        }
                    }
                    if (this.tooltipTitleCallback) {
                        options.tooltips.callbacks.title = this.tooltipTitleCallback
                    }
                    if (this.tooltipLabelCallback) {
                        options.tooltips.callbacks.label = this.tooltipLabelCallback
                    }
                }

                this.data.forEach(data => {
                    if (data.backgroundColor === undefined) {
                        data.backgroundColor = this.backgroundColor ? this.backgroundColor : 'black'
                    }
                    if (data.pointBackgroundColor === undefined) {
                        data.pointBackgroundColor = this.pointBackgroundColor ? this.pointBackgroundColor : 'white'
                    }
                    if (data.borderWidth === undefined) {
                        data.borderWidth = this.borderWidth ? this.borderWidth : 1
                    }
                    if (data.pointBorderColor === undefined) {
                        data.pointBorderColor = this.pointBorderColor ? this.pointBorderColor : '#249EBF'
                    }
                    data.data.forEach(point => {
                        if (point.r === undefined) {
                            point.r = 3
                        }
                    })
                })

                this.renderChart({
                    labels: this.labels,
                    datasets: this.data
                }, options)
            }
        },
        mounted () {
            this.render()
        }
    }
</script>