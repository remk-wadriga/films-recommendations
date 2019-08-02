<template src="@/templates/top-buttons.html" />

<script>
    export default {
        name: "TopButtons",
        data() {
            return {
                btnSize: '',
                deleteStrategyVisible: false
            }
        },
        methods: {
            btnClick(element) {
                element = JSON.parse(element)
                if (element.url !== undefined) {
                    this.$router.push(element.url)
                } else {
                    eval('this.' + element + '()')
                }
            }
        },
        computed: {
            buttons() {
                return this.$store.state.pageTopButtons
            }
        }
    }
</script>

<style scoped>

</style>