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
            onClick: Function
        },
        data () {
            return {
                backgroundColors: [
                    '#BF0422',
                    '#BF9A01',
                    '#08BFB7',
                    '#9CBF05',
                    '#0480BF',
                    '#BF4B06',
                    '#085BBF',
                    '#021BBF',
                    '#08BF17',
                    '#BF6D6D',
                    '#2F00BF',
                    '#3DBF08',
                    '#BF68A1',
                    '#8000BF',
                    '#BF9FA3',
                    '#BF9580',
                    '#95BF72',
                    '#7DBF97',
                    '#6AA3BF',
                    '#09BF70',
                    '#6876BF',
                    '#BFBB78',
                    '#8C71BF',
                    '#BF8F72'
                ],
                colorIndex: 0,
                dataColors: {}
            }
        },
        watch: {
            data() {
                this.render()
            }
        },
        methods: {
            getBackgroundColor (label) {
                if (this.dataColors[label] === undefined) {
                    let color = this.backgroundColors[this.colorIndex++]
                    this.dataColors[label] = color !== undefined ? color : 'black'
                }
                return this.dataColors[label]
            },
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

                /*this.chartTooltipLabelCallback = item => {
                    let label = labels[item.index]
                    let string = ' ' + label
                    if (values[label] !== undefined && values[label].data[item.index] !== undefined) {
                        let coordinates = values[label].data[item.index]
                        string += ' (' + coordinates.x + ', ' + coordinates.y + ')'
                    }
                    return string
                }*/

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
                } else {
                    options.tooltips.callbacks.label = item => {
                        let option = this.data[item.datasetIndex]
                        let data = option.data[item.index]
                        return ' ' + option.label + ' (' + data.x + ', ' + data.y + ')'
                    }
                }

                this.data.forEach(data => {
                    if (data.backgroundColor === undefined) {
                        data.backgroundColor = this.getBackgroundColor(data.label)
                    }
                    if (data.pointBackgroundColor === undefined) {
                        data.pointBackgroundColor = 'white'
                    }
                    if (data.borderWidth === undefined) {
                        data.borderWidth = 1
                    }
                    if (data.pointBorderColor === undefined) {
                        data.pointBorderColor = 'white'
                    }

                    data.data.forEach(point => {
                        if (point.x === undefined && point[0] !== undefined) {
                            point.x = point[0]
                        }
                        if (point.y === undefined && point[1] !== undefined) {
                            point.y = point[1]
                        }
                        if (point.r === undefined) {
                            point.r = point[2] !== undefined ? point[2] : 3
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
            //this.render()
        }
    }
</script>

<style>
    .chart-container {
        background: #212733;
        border-radius: 15px;
        box-shadow: 0px 2px 15px rgba(25, 25, 25, 0.27);
        margin:  25px 0;
        width: 100%;
    }
    .chart-container h2 {
        margin-top: 0;
        padding: 15px 0;
        color:  rgba(255, 0,0, 0.5);
        border-bottom: 1px solid #323d54;
    }
</style>