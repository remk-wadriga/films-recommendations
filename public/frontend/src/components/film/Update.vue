<template src="@/templates/film/update.html" />

<script>
    import Vue from 'vue'
    import { mapMutations } from 'vuex'
    import Form from '@/components/film/Form'
    import { FILM_VIEW_URL } from '@/api/request-urls'
    import { ROUTE_FILM_CREATE } from '@/router/routes-list'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "Update",
        components: { Form },
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
            this.setPageTitle('Update film')
            this.setTopButtons([{title: 'Create film', type: 'success', click: {url: {name: ROUTE_FILM_CREATE}}}])

            const id = this.$route.params.id
            let film = await Vue.api.request([FILM_VIEW_URL, {id}])
            if (film.isOk) {
                film.isNew = false
                delete film.isOk
                this.film = film
            }
        }
    }
</script>

<style scoped>

</style>