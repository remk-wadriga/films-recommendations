<template src="@/templates/content.html" />

<script>
    export default {
        name: 'Content',
        data () {
            return {
                msg: 'Welcome to Your Vue.js App'
            }
        },
        methods: {
            clickButton () {

            }
        },
        mounted () {
            this.$http.get('games').then(response => {

            }, errorResponse => {
                //console.log(errorResponse)
            })
        }
    }
</script>

<style scoped>

</style>
