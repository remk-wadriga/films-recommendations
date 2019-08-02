<template src="@/templates/film/create.html" />

<script>
    import { mapMutations } from 'vuex'
    import Form from '@/components/film/Form'
    import { SET_PAGE_TITLE_MUTATION, SET_TOP_BUTTONS_MUTATION } from '@/store/mutation-types'

    export default {
        name: "Create",
        components: { Form },
        data () {
            return {

            }
        },
        methods: {
            ...mapMutations({
                setPageTitle: SET_PAGE_TITLE_MUTATION,
                setTopButtons: SET_TOP_BUTTONS_MUTATION
            })
        },
        mounted () {
            this.setPageTitle('Add film')
            this.setTopButtons([])
        }
    }
</script>

<style scoped>

</style>