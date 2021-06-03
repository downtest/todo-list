<template>
    <div class="breadcrumb">
        <div class="breadcrumb--item" :key="index" v-for="(parent, index) in $store.getters['todos/parents'](id)">
            <div v-if="parent === null">
                <router-link :to="{name: 'task-list', params: {parentId: null}}">
                    Главная
                </router-link>
            </div>

            <div v-else-if="index + 1 < $store.getters['todos/parents'](id).length">
                <router-link :to="{name: 'task-list', params: {parentId: parent.id}}">
                    #{{parent.id}}
                    {{parent.message.split("\n")[0]}}
                </router-link>
            </div>

            <div v-else-if="parent">
                #{{parent.id}}
                {{parent.message.split("\n")[0]}}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "tasks-breadcrumb",
        props: {
            id: String,
        },
        data() {
            return {

            };
        },
        computed: {
        },
        methods: {
        },
    };
</script>

<style lang="scss">
.breadcrumb {
    text-align: left;
    display: block;
    flex-wrap: wrap;

    .breadcrumb--item {
        margin: 5px 10px;
    }
}
</style>
