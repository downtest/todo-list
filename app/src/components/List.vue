<template>
    <div class="list">
        <div class="justify-content-between row">
            <nested v-model="elements" @focus="focusHandler" @change="onChange" :focusId="focusId" />

            <div class="logs">
                <div :key="index" v-for="(log, index) in $store.state.todos.logs">{{log}}</div>
            </div>

            <pre style="text-align: left">{{this.$store.state.todos.items}}</pre>

        </div>
    </div>
</template>

<script>
    import nested from "./List/Nested";

    export default {
        props: {
            title: {
                required: false,
                type: String,
                default: ''
            },
            value: {
                required: false,
                type: Array,
                default: null
            },
        },
        components: {
            nested,
        },
        data() {
            return {
                focusId: null,
            }
        },
        computed: {
            elements: {
                get() {
                    return this.$store.getters['todos/firstLevel'];
                },
                set(payload) {
                    this.$store.dispatch("todos/updateChildren", {
                        parentId: 0,
                        children: payload.map(child => child.id)
                    });
                },
            },
        },
        methods: {
            focusHandler(value) {
                console.log(`Новый активный ${value}`);
                // this.$store.commit('todos/makeUnactive')
                this.focusId = value ?? null
                // this.$router.push({ name: 'task-detail', params: { id: value.id, data: value } })
            },
            onChange(value) {
                if (value.added) {
                    // console.log(`set parentId=0 on #${value.added.element.id}`)
                    this.$store.dispatch("todos/updateParent", {
                        id: value.added.element.id,
                        parentId: 0,
                        newIndex: value.added.newIndex,
                    });
                }
            },
        },
    }
</script>

<style lang="scss">

</style>
