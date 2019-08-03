<template src="@/templates/film/form.html" />

<script>
    //import { FILM_CREATE_URL, FILM_UPDATE_URL } from '@/api/request-urls'

    export default {
        name: "Form",
        data () {
            return {
                film: {
                    isNew: false,
                    name: null,
                    genres: []
                },
                genresNames: [],
                errors: []
            }
        },
        methods: {
            successFrom () {
                console.log(this.film)
            }
        },
        mounted () {

        }
    }
</script>

<style scoped>

</style>