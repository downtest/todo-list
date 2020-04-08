<template>
    <div>
        <h3>Changind User!</h3>

        <input v-model="name" type="text">
    </div>
</template>

<script>
export default {
    computed: {
        name: {
            get() {
                return this.$store.state.user.name
            },
            set(value) {
                this.$store.commit('user/updateName', value)
            }
        },
    },
}
</script>

<style lang="scss">

</style>
