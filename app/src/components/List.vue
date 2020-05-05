<template>
    <div class="list">
        <div class="justify-content-between row">

            <tasks-breadcrumb v-if="parentId" :id="parseInt(parentId)"></tasks-breadcrumb>

            <div v-if="parent">
                <h1>{{parent.message.split('\n')[0]}}</h1>
                <textarea
                    class="parent--input"
                    v-model="parentMessage"
                    :rows="parentMessage.split('\n').length"
                ></textarea>
                <input class="parent--input" type="datetime-local" v-model="parentDatetime">
            </div>

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
    import tasksBreadcrumb from "./List/Breadcrumb";

    export default {
        props: {
            title: {
                required: false,
                type: String,
                default: ''
            },
            parentId: {
                required: false,
                type: [String, Number],
            },
            value: {
                required: false,
                type: Array,
                default: null
            },
        },
        components: {
            nested,
            tasksBreadcrumb,
        },
        data() {
            return {
                focusId: null,
            }
        },
        computed: {
            parent: {
                get() {
                    if (this.parentId) {
                        return this.$store.getters['todos/getById'](this.parentId);
                    } else {
                        return null;
                    }
                },
            },
            parentMessage: {
                get() {
                    if (this.parent) {
                        return this.parent.message;
                    } else {
                        return null;
                    }
                },
                set(message) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.parent.id,
                        payload: {message},
                    })
                },
            },
            parentDatetime: {
                get() {
                    if (this.parent) {
                        return this.parent.datetime;
                    } else {
                        return null;
                    }
                },
                set(datetime) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.parent.id,
                        payload: {datetime},
                    })
                },
            },
            elements: {
                get() {
                    if (this.parentId) {
                        return this.$store.getters['todos/children'](this.parentId);
                    } else {
                        return this.$store.getters['todos/firstLevel'];
                    }
                },
                set(payload) {
                    this.$store.dispatch("todos/updateChildren", {
                        parentId: parseInt(this.parent.id),
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
.parent {

    &--input {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
