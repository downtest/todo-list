<template>
<div>
    <nested
        v-model="nodes"
    />

    <span class="btn add-btn" @click="createChild">
        <img class="btn-icon" :src="$store.getters['icons/Plus']" alt="add" title="Add task">
    </span>
</div>
</template>

<script>
import nested from "./Nested";

export default {
    components: {
        nested,
    },
    name: "TodosList",
    props: {
        value: {
            required: false,
            type: Array,
            default: [],
        },
        createChild: {
            required: true,
            type: Function,
        },
    },
    computed: {
        nodes() {
            return this.value
        }
    },
    methods: {},
}
</script>

<style scoped>

</style>