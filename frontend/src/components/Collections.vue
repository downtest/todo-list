<template>
    <div class="list collections">
        <div class="collection" v-for="collection in collections">
            <small class="collection--id">#{{collection.id}}</small>
            <router-link class="collection--name" :to="{name: 'task-list', params: {collectionId: collection.id}}">
                {{collection.name}}
            </router-link>
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
                return this.$store.getters['collections/all']
            },
        },
        methods: {
        },
        mounted() {
            if (this.$store.getters['user/current']['id']) {
                this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
            }
        },
    }
</script>
