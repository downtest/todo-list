<template>
    <div class="breadcrumb">
        <div class="breadcrumb--item" v-if="$store.getters['todos/parents'].length">
            <router-link :to="{name: 'task-list'}">
                Главная
            </router-link>
        </div>

        <div class="breadcrumb--item" :key="index" v-for="(parent, index) in $store.getters['todos/parents'](id)">
            <div v-if="index + 1 < $store.getters['todos/parents'](id).length">
                <router-link :to="{name: 'task-item', params: {itemId: parent.id}}">
                    {{parent.message ? parent.message.split("\n")[0] : ''}}
                </router-link>
            </div>

            <div v-else-if="parent">
                {{parent.message ? parent.message.split("\n")[0] : ''}}
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
