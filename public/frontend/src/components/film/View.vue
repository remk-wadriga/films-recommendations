<template src="@/templates/film/view.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { FILM_VIEW_URL } from '@/api/request-urls'
    import { ROUTE_FILM_CREATE, ROUTE_FILM_UPDATE } from '@/router/routes-list'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "ViewFilm",
        data () {
            return {
                film: null
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            })
        },
        async mounted () {
            const id = this.$route.params.id
            this.film = await Vue.api.request([FILM_VIEW_URL, {id}])

            if (this.film.isOk) {
                let genres = []
                this.film.genres.forEach(item => { genres.push(item.name) })
                this.film.genres = genres.join(', ')

                let countries = []
                this.film.countries.forEach(item => { countries.push(item.name) })
                this.film.countries = countries.join(', ')

                let companies = []
                this.film.companies.forEach(item => { companies.push(item.name) })
                this.film.companies = companies.join(', ')

                let directors = []
                this.film.directors.forEach(item => { directors.push(item.name) })
                this.film.directors = directors.join(', ')

                let actors = []
                this.film.actors.forEach(item => { actors.push(item.name) })
                this.film.actors = actors.join(', ')

                let producers = []
                this.film.producers.forEach(item => { producers.push(item.name) })
                this.film.producers = producers.join(', ')

                let writers = []
                this.film.writers.forEach(item => { writers.push(item.name) })
                this.film.writers = writers.join(', ')

                let premiums = []
                this.film.premiums.forEach(item => { premiums.push(item.name) })
                this.film.premiums = premiums.join(', ')

                let languages = []
                this.film.languages.forEach(item => { languages.push(item.name) })
                this.film.languages = languages.join(', ')

                this.film.year = this.$moment(this.film.date).format('YYYY');

                this.setPageTitle(this.film.name)

                let topButtons = [{title: 'Create Film', type: 'success', click: {url: {name: ROUTE_FILM_CREATE}}}]
                if (this.film.isMy) {
                    topButtons.push({title: 'Update film', type: 'info', click: {url: {name: ROUTE_FILM_UPDATE}}})
                }
                this.setTopButtons(topButtons)
            }
        }
    }
</script>

<style scoped>

</style>