<template src="@/templates/film/form.html" />

<script>
    import Vue from 'vue'
    import logger from '@/logger'
    //import { MultiSelect } from 'vue-search-select'
    import Multiselect from 'vue-multiselect'
    import { FILM_CREATE_URL, FILM_UPDATE_URL, GENRES_LIST_URL, COMPANIES_LIST_URL, DIRECTORS_LIST_URL, ACTORS_LIST_URL, PRODUCERS_LIST_URL, WRITERS_LIST_URL, PREMIUMS_LIST_URL } from '@/api/request-urls'

    export default {
        name: "Form",
        components: { Multiselect },
        data () {
            return {
                film: {
                    isNew: false,
                    poster: null,
                    name: null,
                    genres: [],
                    companies: [],
                    directors: [],
                    actors: [],
                    producers: [],
                    writers: [],
                    premiums: []
                },
                genres: [],
                genresNames: [],
                companies: [],
                companiesNames: [],
                directors: [],
                directorsNames: [],
                actors: [],
                actorsNames: [],
                producers: [],
                producersNames: [],
                writers: [],
                writersNames: [],
                premiums: [],
                premiumsNames: [],
                errors: [],
                searchTextMinLength: 3,
                isLoading: false
            }
        },
        methods: {
            async successForm () {
                let url = FILM_CREATE_URL
                if (!this.film.isNew) {
                    let url = [FILM_UPDATE_URL, {id: this.film.id}]
                }
                delete this.film.isNew
                delete this.film.id

                let film = await Vue.api.request(url, this.film)
                if (film.isOk) {
                    delete film.isOk
                    this.film = film
                    this.film.isNew = false
                } else {
                    console.log(film.message)
                }
            },

            searchGenres (text) {
                if (text.length < this.searchTextMinLength) {
                    this.genresNames = this.genres
                    return
                }
                this.genresNames = []
                this.genres.forEach(item => {
                    if (item.name.toLowerCase().indexOf(text.toLowerCase()) !== -1) {
                        this.genresNames.push(item)
                    }
                })
            },

            async searchCompanies (text) {
                if (text.length < this.searchTextMinLength) {
                    this.companiesNames = this.companies
                    return
                }
                this.companiesNames = await Vue.api.request(COMPANIES_LIST_URL, {search: text})
            },
            async addCompany (items) {
                if (items[items.length - 1].id === 'load_more') {
                    this.film.companies.pop()
                    this.companies.pop()
                    let newItems = await Vue.api.request(COMPANIES_LIST_URL, {offset: this.companies.length})
                    newItems.forEach(item => {
                        this.companies.push(item)
                    })
                    this.companies.push({id: 'load_more', name: '... load more ...'})
                    this.companiesNames = this.companies
                }
            },

            async searchDirectors (text) {
                if (text.length < this.searchTextMinLength) {
                    this.directorsNames = this.directors
                    return
                }
                this.directorsNames = await Vue.api.request(DIRECTORS_LIST_URL, {search: text})
            },
            async addDirector (items) {
                if (items[items.length - 1].id === 'load_more') {
                    this.film.directors.pop()
                    this.directors.pop()
                    let newItems = await Vue.api.request(DIRECTORS_LIST_URL, {offset: this.directors.length})
                    newItems.forEach(item => {
                        this.directors.push(item)
                    })
                    this.directors.push({id: 'load_more', name: '... load more ...'})
                    this.directorsNames = this.directors
                }
            },

            async searchActors (text) {
                if (text.length < this.searchTextMinLength) {
                    this.actorsNames = this.actors
                    return
                }
                this.actorsNames = await Vue.api.request(ACTORS_LIST_URL, {search: text})
            },
            async addActor (items) {
                if (items[items.length - 1].id === 'load_more') {
                    this.film.actors.pop()
                    this.actors.pop()
                    let newItems = await Vue.api.request(ACTORS_LIST_URL, {offset: this.actors.length})
                    newItems.forEach(item => {
                        this.actors.push(item)
                    })
                    this.actors.push({id: 'load_more', name: '... load more ...'})
                    this.actorsNames = this.actors
                }
            },

            async searchProducers (text) {
                if (text.length < this.searchTextMinLength) {
                    this.producersNames = this.producers
                    return
                }
                this.producersNames = await Vue.api.request(PRODUCERS_LIST_URL, {search: text})
            },
            async addProducer (items) {
                if (items[items.length - 1].id === 'load_more') {
                    this.film.producers.pop()
                    this.producers.pop()
                    let newItems = await Vue.api.request(PRODUCERS_LIST_URL, {offset: this.producers.length})
                    newItems.forEach(item => {
                        this.producers.push(item)
                    })
                    this.producers.push({id: 'load_more', name: '... load more ...'})
                    this.producersNames = this.producers
                }
            },

            async searchWriters (text) {
                if (text.length < this.searchTextMinLength) {
                    this.writersNames = this.writers
                    return
                }
                this.writersNames = await Vue.api.request(WRITERS_LIST_URL, {search: text})
            },
            async addWriter (items) {
                if (items[items.length - 1].id === 'load_more') {
                    this.film.writers.pop()
                    this.writers.pop()
                    let newItems = await Vue.api.request(WRITERS_LIST_URL, {offset: this.writers.length})
                    newItems.forEach(item => {
                        this.writers.push(item)
                    })
                    this.writers.push({id: 'load_more', name: '... load more ...'})
                    this.writersNames = this.writers
                }
            },

            async searchPremiums (text) {
                if (text.length < this.searchTextMinLength) {
                    this.premiumsNames = this.premiums
                    return
                }
                this.premiumsNames = await Vue.api.request(WRITERS_LIST_URL, {search: text})
            },
            async addPremium (items) {
                if (items[items.length - 1].id === 'load_more') {
                    this.film.premiums.pop()
                    this.premiums.pop()
                    let newItems = await Vue.api.request(WRITERS_LIST_URL, {offset: this.premiums.length})
                    newItems.forEach(item => {
                        this.premiums.push(item)
                    })
                    this.premiums.push({id: 'load_more', name: '... load more ...'})
                    this.premiumsNames = this.premiums
                }
            }
        },
        async mounted () {
            let genres = await Vue.api.request(GENRES_LIST_URL)
            if (genres.isOk) {
                this.genresNames = this.genres = genres
            }

            let companies = await Vue.api.request(COMPANIES_LIST_URL)
            if (companies.isOk) {
                companies.push({id: 'load_more', name: '... load more ...'})
                this.companiesNames = this.companies = companies
            }

            let directors = await Vue.api.request(DIRECTORS_LIST_URL)
            if (directors.isOk) {
                directors.push({id: 'load_more', name: '... load more ...'})
                this.directorsNames = this.directors = directors
            }

            let actors = await Vue.api.request(ACTORS_LIST_URL)
            if (actors.isOk) {
                actors.push({id: 'load_more', name: '... load more ...'})
                this.actorsNames = this.actors = actors
            }

            let producers = await Vue.api.request(PRODUCERS_LIST_URL)
            if (producers.isOk) {
                producers.push({id: 'load_more', name: '... load more ...'})
                this.producersNames = this.producers = producers
            }

            let writers = await Vue.api.request(WRITERS_LIST_URL)
            if (writers.isOk) {
                writers.push({id: 'load_more', name: '... load more ...'})
                this.writersNames = this.writers = writers
            }

            let premiums = await Vue.api.request(PREMIUMS_LIST_URL)
            if (premiums.isOk) {
                premiums.push({id: 'load_more', name: '... load more ...'})
                this.premiumsNames = this.premiums = premiums
            }
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>