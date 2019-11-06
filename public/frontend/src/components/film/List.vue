<template src="@/templates/film/list.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { FILMS_LIST_URL, FILM_DELTE_URL } from '@/api/request-urls'
    import { ROUTE_FILM_CREATE, ROUTE_FILM_VIEW, ROUTE_FILM_UPDATE } from '@/router/routes-list'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "List",
        data () {
            return {
                films: [],
                fields: {
                    poster: {},
                    name: {sortable: true},
                    //genres: {},
                    //countries: {},
                    //languages: {},
                    year: {sortable: true},
                    budget: {sortable: true},
                    actions: {}
                },
                filter: '',
                viewBtnVar: 'primary',
                updateBtnVar: 'success',
                deleteBtnVar: 'danger',
                currentPage: 1,
                perPage: 10,
                totalRows: 0
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            }),
            onFiltered (filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length
                this.currentPage = 1
            },
            viewFilm (film) {
                this.$router.push({name: ROUTE_FILM_VIEW, params: {id: film.id}})
            },
            updateFilm (film) {
                this.$router.push({name: ROUTE_FILM_UPDATE, params: {id: film.id}})
            },
            async deleteFilm (film) {
                let response = await Vue.api.request([FILM_DELTE_URL, {id: film.id}])
                if (response.isOk) {
                    this.films.forEach((item, index) => {
                        if (item.id === film.id) {
                            this.films.splice(index, 1)
                        }
                    })
                    this.totalRows--
                }
            }
        },
        async mounted () {
            this.setPageTitle('Films')
            this.setTopButtons([{title: 'Create film', type: 'success', click: {url: {name: ROUTE_FILM_CREATE}}}])

            this.films = await Vue.api.request(FILMS_LIST_URL)
            this.totalRows = this.films.length

            this.films.forEach((film) => {
                let genres = []
                film.genres.forEach(item => { genres.push(item.name) })
                film.genres = genres.join(', ')

                let countries = []
                film.countries.forEach(item => { countries.push(item.name) })
                film.countries = countries.join(', ')

                let languages = []
                film.languages.forEach(item => { languages.push(item.name) })
                film.languages = languages.join(', ')

                film.year = this.$moment(film.date).format('YYYY');
            })
        }
    }
</script>

<style scoped>

</style>