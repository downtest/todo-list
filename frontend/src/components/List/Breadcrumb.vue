<template>
    <div class="breadcrumb">
        <div class="breadcrumb--item" v-if="items.length">
            <router-link :to="{name: 'task-list'}">
                Главная
            </router-link>
        </div>

<!--        <div class="breadcrumb&#45;&#45;item" :key="index" v-for="(parent, index) in items">-->
<!--            <div v-if="index + 1 < items.length">-->
<!--                <router-link :to="{name: 'task-item', params: {itemId: parent.id}}">-->
<!--                    {{this.getTitle(parent)}}-->
<!--                </router-link>-->
<!--            </div>-->
<!--        </div>-->

        <template v-if="lastParent">

            <div class="breadcrumb--crumb" v-for="i in items.length - 1">
                ..
            </div>

            <div class="breadcrumb--item" >
                <router-link :to="{name: 'task-item', params: {itemId: lastParent.id}}">
                    {{getTitle(lastParent)}}
                </router-link>
            </div>
        </template>
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
            items() {
                return this.$store.getters['todos/parents'](this.id)
            },
            lastParent() {
                if (this.items.length < 2) {
                    return null
                }

                return this.items.splice(this.items.length - 2, 1)[0]
            },
        },
        methods: {
            getTitle(item) {
                let result = ''

                if (item.updated && item.updated.message) {
                    result = item.updated.message
                } else {
                    result = item.message || ''
                }

                return result.length > 20 ? result.split("\n")[0].substr(0, 20) + '...' : result
            },
        },
    };
</script>

<style lang="scss">
.breadcrumb {
    text-align: left;
    display: flex;
    flex-wrap: wrap;

    .breadcrumb--item {
        margin: 5px 10px;
    }
}
</style>
