<template src="@/templates/film/form.html" />

<script>
    import Vue from 'vue'
    import logger from '@/logger'
    import { GENRES_LIST_URL, COMPANIES_LIST_URL, DIRECTORS_LIST_URL, ACTORS_LIST_URL, PRODUCERS_LIST_URL, WRITERS_LIST_URL, PREMIUMS_LIST_URL } from '@/api/request-urls'

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
                companiesNames: [],
                directorsNames: [],
                actorsNames: [],
                producersNames: [],
                writersNames: [],
                premiumsNames: [],
                errors: []
            }
        },
        methods: {
            successFrom () {
                console.log(this.film)
            },
            createListForDropdown (list) {
                let listForDropdown = []
                list.forEach(item => {
                    listForDropdown.push({value: item.id, text: item.name})
                })
                return listForDropdown
            }
        },
        async mounted () {
            let genres = await Vue.api.request(GENRES_LIST_URL)
            if (genres.isOk) {
                this.genresNames = this.createListForDropdown(genres)
            }


            let companies = await Vue.api.request(COMPANIES_LIST_URL)
            if (companies.isOk) {
                this.companiesNames = this.createListForDropdown(companies)
            }
        }
    }
</script>

<style scoped>

</style>