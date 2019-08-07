<template src="@/templates/film/list.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { FILMS_LIST_URL, FILM_DELTE_URL } from '@/api/request-urls'
    import { ROUTE_FILM_CREATE, ROUTE_FILM_UPDATE } from '@/router/routes-list'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "List",
        data () {
            return {
                films: [],
                fields: {
                    poster: {},
                    name: {sortable: true},
                    genres: {},
                    languages: {},
                    date: {sortable: true},
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
                this.$router.push({name: ROUTE_FILM_UPDATE, params: {id: film.id}})
            },
            async deleteFilm (film) {
                let response = Vue.api.request([FILM_DELTE_URL, {id: film.id}])
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
            this.setTopButtons([{title: 'Add film', type: 'success', click: {url: {name: ROUTE_FILM_CREATE}}}])

            this.films = await Vue.api.request(FILMS_LIST_URL)
            this.totalRows = this.films.length

            this.films.forEach((film) => {
                let genres = []
                film.genres.forEach(item => { genres.push(item.name) })
                film.genres = genres.join(', ')

                film.languages = film.languages.join(', ')
            })
        }
    }
</script>

<style scoped>

</style>