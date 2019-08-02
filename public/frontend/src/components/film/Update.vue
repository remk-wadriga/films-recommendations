<template src="@/templates/film/update.html" />

<script>
    import { mapMutations } from 'vuex'
    import Form from '@/components/film/Form'
    import { FILM_INFO_URL } from '@/api/request-urls'
    import { ROUTE_FILM_CREATE } from '@/router/routes-list'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "Update",
        components: { Form },
        data () {
            return {
                ...mapMutations({
                    setPageTitle: SET_PAGE_TITLE_MUTATION,
                    setTopButtons: SET_TOP_BUTTONS_MUTATION
                })
            }
        },
        methods: {

        },
        mounted () {
            this.setPageTitle('Update film')
            this.setTopButtons([{title: 'Add film', type: 'success', click: {url: {name: ROUTE_FILM_CREATE}}}])
        }
    }
</script>

<style scoped>

</style>