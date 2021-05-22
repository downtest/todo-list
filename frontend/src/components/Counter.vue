<template>
    <div>
        Tengo! {{count}}
        <button @click="$store.commit('counter/decrement')">-</button>
        <button @click="$store.commit('counter/increment')">+</button>
    </div>
</template>

<script>
    export default {
        computed: {
            count: function() {
                return this.$store.state.counter.count;
            },
        }
    }
</script>

<style lang="scss">

</style>
