<template src="@/templates/film/list.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import { FILMS_LIST_URL } from '@/api/request-urls'
    import { ROUTE_FILM_CREATE } from '@/router/routes-list'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "List",
        data () {
            return {
                films: []
            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            })
        },
        async mounted () {
            this.setPageTitle('Films')
            this.setTopButtons([{title: 'Add film', type: 'success', click: {url: {name: ROUTE_FILM_CREATE}}}])

            this.films = await Vue.api.get(FILMS_LIST_URL)
        }
    }
</script>

<style scoped>

</style>