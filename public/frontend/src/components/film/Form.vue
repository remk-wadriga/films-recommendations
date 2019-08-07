<template src="@/templates/film/form.html" />

<script>
    import Vue from 'vue'
    import { Multiselect } from 'vue-multiselect'
    import { Datetime } from 'vue-datetime'
    import 'vue-datetime/dist/vue-datetime.css'
    import { ROUTE_FILMS_LIST } from '@/router/routes-list'
    import { FILM_CREATE_URL, FILM_UPDATE_URL, GENRES_LIST_URL, COMPANIES_LIST_URL, DIRECTORS_LIST_URL, ACTORS_LIST_URL, PRODUCERS_LIST_URL, WRITERS_LIST_URL, PREMIUMS_LIST_URL } from '@/api/request-urls'

    export default {
        name: "Form",
        components: { Multiselect, Datetime },
        data () {
            return {
                posterPreview: null,
                poster: null,
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
                languagesNames: [
                    {id: 'EN', name: 'English'},
                    {id: 'RU', name: 'Russian'},
                    {id: 'FR', name: 'French'},
                    {id: 'DE', name: 'Deutsch'},
                    {id: 'KZ', name: 'Kazakh'},
                    {id: 'IN', name: 'Indian'},
                    {id: 'IT', name: 'Italian'},
                    {id: 'CH', name: 'Chinese'},
                    {id: 'JP', name: 'Japanese'}
                ],
                dateFormat: 'y-MM-dd'
            }
        },
        props: {
            film: Object
        },
        methods: {
            init (film = null) {
                if (this.film.isNew) {
                    return true
                }
                if (film === null) {
                    film = this.film
                }

                this.posterPreview = film.poster

                let languages = []
                film.languages.forEach(code => {
                    let name = null
                    if (typeof code === 'object') {
                        name = code.name
                        code = code.id
                    } else {
                        name = code
                    }
                    this.languagesNames.forEach(item => {
                        if (item.id === code) {
                            name = item.name
                        }
                    })
                    languages.push({id: code, name: name})
                })
                film.languages = languages

                film.date = this.$moment(film.date).add(1, 'day').format()
            },
            async successForm () {
                this.errors = []

                let url = FILM_CREATE_URL
                if (!this.film.isNew) {
                    url = [FILM_UPDATE_URL, {id: this.film.id}]
                }
                let data = {
                    poster: null,
                    name: this.film.name,
                    genres: [],
                    companies: [],
                    directors: [],
                    actors: [],
                    producers: [],
                    writers: [],
                    premiums: [],
                    budget: this.film.budget,
                    sales: this.film.sales,
                    languages: [],
                    date: this.film.date,
                    duration: this.film.duration
                }
                if (this.poster !== null) {
                    data.poster = this.poster
                }
                this.film.genres.forEach(item => {
                    data.genres.push(item.id)
                })
                this.film.companies.forEach(item => {
                    data.companies.push(item.id)
                })
                this.film.directors.forEach(item => {
                    data.directors.push(item.id)
                })
                this.film.actors.forEach(item => {
                    data.actors.push(item.id)
                })
                this.film.producers.forEach(item => {
                    data.producers.push(item.id)
                })
                this.film.writers.forEach(item => {
                    data.writers.push(item.id)
                })
                this.film.premiums.forEach(item => {
                    data.premiums.push(item.id)
                })
                this.film.languages.forEach(item => {
                    data.languages.push(item.id)
                })

                if (data.poster === null && this.film.isNew) {
                    this.errors.push('Poster is required!')
                }
                if (data.genres.length === 0) {
                    this.errors.push('Genre is required!')
                }
                if (data.companies.length === 0) {
                    this.errors.push('Company is required!')
                    return false
                }
                if (data.directors.length === 0) {
                    this.errors.push('Director is required!')
                }
                if (data.actors.length === 0) {
                    this.errors.push('Actor is required!')
                }
                if (data.producers.length === 0) {
                    this.errors.push('Producer is required!')
                }
                if (data.writers.length === 0) {
                    this.errors.push('Writer is required!')
                }
                if (data.languages.length === 0) {
                    this.errors.push('Language is required!')
                }

                if (this.errors.length > 0) {
                    return false
                }

                if (typeof data.budget === 'string') {
                    data.budget = Number(data.budget)
                }
                if (typeof data.sales === 'string') {
                    data.sales = Number(data.sales)
                }
                if (typeof data.duration === 'string') {
                    data.duration = Number(data.duration)
                }

                let film = await Vue.api.request(url, data)

                if (film.isOk) {
                    if (this.film.isNew) {
                        this.$router.push({name: ROUTE_FILMS_LIST})
                    } else {
                        delete film.isOk
                        this.film.isNew = false
                        this.init(film)
                    }
                }
            },

            setPoster (event) {
                if (event.target.files.length === 0) {
                    this.errors.push('File not set')
                } else {
                    this.poster = event.target.files[0]
                    let reader = new FileReader()
                    reader.readAsBinaryString(this.poster)
                    reader.onload = () => {
                        let base64 = btoa(reader.result)
                        this.poster = {
                            name: this.poster.name,
                            data: base64
                        }
                        this.posterPreview = 'data:image/jpeg;base64,' + base64
                    }
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
            this.init()

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