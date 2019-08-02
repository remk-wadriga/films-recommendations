<template src="@/templates/logger.html" />

<script>
    import { mapMutations } from 'vuex'
    import { DELETE_LOGGER_MESSAGE_MUTATION } from '@/store/mutation-types'

    export default {
        name: "Logger",
        methods: {
            ...mapMutations({
                deleteMessage: DELETE_LOGGER_MESSAGE_MUTATION
            }),
            closeMessage (id) {
                this.deleteMessage(id)
            }
        },
        computed: {
            messages() {
                return this.$store.state.loggerMessages
            }
        }
    }
</script>

<style scoped>

</style>