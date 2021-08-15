<template>
    <div class="collections">
        <div class="collection" v-for="collection in collections">
            <span>#{{collection.id}} {{collection.name}}</span>
        </div>
    </div>
</template>

<script>
    import  nested from "./List/Nested";
    import tasksBreadcrumb from "./List/Breadcrumb";
    import contenteditable from "./Contenteditable";
    import draggable from "vuedraggable"
    import search from "./List/Search"

    export default {
        components: {
            nested,
            tasksBreadcrumb,
            contenteditable,
            draggable,
            search,
        },
        data() {
            return {}
        },
        computed: {
            collections() {
                return [{id: 'f43f4f43f',name: 'Personal',}, {id: 'h76kj87kk87j', name: 'Working'}]
            },
        },
        methods: {
        },
        created() {
            this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
        },
    }
</script>

<style lang="scss">

</style>
