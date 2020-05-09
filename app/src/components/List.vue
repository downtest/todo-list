<template>
    <div class="list">
        <div class="justify-content-between row">

            <tasks-breadcrumb v-if="parentId" :id="parseInt(parentId)"></tasks-breadcrumb>

            <div v-if="parent">
                <h1>{{parent.message.split('\n')[0]}}</h1>

                <label>
                    <textarea
                        class="parent--input"
                        v-model="parentMessage"
                        :rows="parentMessage.split('\n').length"
                    ></textarea>
                </label>

                <label>
                    <input class="parent--input" type="datetime-local" v-model="parentDatetime">
                </label>
            </div>

            <nested
                v-model="elements"
                @focus="focusHandler"
                @change="onChange"
                :focusId="focusId"
            />

            <span class="btn add-btn" @click="createChild">
                <img class="btn-icon" src="../../assets/icons/plus.svg" alt="add" title="Add task">
            </span>

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
                        parentId: this.parentId ? this.parentId  : 0,
                        children: payload.map(child => child.id)
                    });
                },
            },
        },
        methods: {
            createChild () {
                this.$store.dispatch('todos/createItem', {
                    parentId: this.parentId ? this.parentId  : 0,
                    payload: {message: '',}
                }).then(newId => this.focusHandler(newId))
            },
            focusHandler(value) {
                let focusId;

                if (value) {
                    focusId = value

                    setTimeout(() =>{
                        document.querySelector(`[data-id="${focusId}"] > .item--edit > .edit--label > .edit--message`)
                            .focus()
                    })
                } else {
                    focusId = null
                }

                this.focusId = focusId
            },
            onChange(value) {
                if (value.added) {
                    this.$store.dispatch("todos/updateParent", {
                        id: value.added.element.id,
                        parentId: this.parentId ? this.parentId  : 0,
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
