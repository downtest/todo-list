<template>
    <div class="list">
        <div class="justify-content-between row">

            <tasks-breadcrumb v-if="parentId" :id="parentId"></tasks-breadcrumb>

            <div v-if="parent">
                <h1>{{parent.message.split('\n')[0]}}</h1>

                <div>
                    <contenteditable
                        :content="parentMessage"
                        :handleWordFunction="handleWordFunction"
                        @setTime="setTimeHandler"
                    ></contenteditable>
                </div>

                <label>
                    <input class="parent--input" type="datetime-local" v-model="parentDatetime">
                </label>
            </div>

            <nested
                v-model="elements"
                :parentId="parentId"
            />

            <span class="btn add-btn" @click="createChild">
                <img class="btn-icon" :src="require('/assets/icons/plus.svg')" alt="add" title="Add task">
            </span>

            <div class="logs">
                <div :key="index" v-for="(log, index) in $store.state.todos.logs">{{log}}</div>
            </div>
        </div>
    </div>
</template>

<script>
    import nested from "../List/Nested";
    import tasksBreadcrumb from "../List/Breadcrumb";
    import contenteditable from "../Contenteditable";
    import draggable from "vuedraggable"

    export default {
        components: {
            nested,
            tasksBreadcrumb,
            contenteditable,
            draggable,
        },
        props: {
            title: {
                required: false,
                type: String,
                default: ''
            },
            // parentId: {
            //     required: false,
            //     type: [String, Number],
            // },
            value: {
                required: false,
                type: Array,
                default: null
            },
        },
        data() {
            return {}
        },
        computed: {
            parentId() {
                if (!this.$route || !this.$route.params.parentId) {
                    return null
                }

                return this.$route.params.parentId
            },
            parent() {
                if (this.parentId) {
                    return this.$store.getters['todos/getById'](this.parentId);
                } else {
                    return null;
                }
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
                    message = message.replace(/<\/?[^>]+(>|$)/g, "")
                    this.$store.dispatch('todos/updateItem', {
                        id: this.parent.id,
                        payload: {
                            message: message,
                        },
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
                        payload: {
                            datetime: datetime,
                        },
                    })
                },
            },
            elements: {
                get() {
                    return this.$store.getters['todos/children'](this.parentId);
                },
                set(payload) {
                    // this.$store.dispatch("todos/updateChildren", {
                    //     parentId: this.parentId ? this.parentId  : 0,
                    //     children: payload.map(child => child.id)
                    // });
                },
            },
        },
        methods: {
            createChild () {
                this.$store.dispatch('todos/createItem', {
                    parentId: this.parentId,
                    message: '',
                })
            },
            handleWordFunction(node) {
                if (node.firstChild.length > 3) {
                    node.classList.add(`green`)
                }
            },
            setTimeHandler(value) {
                console.log(value, `changing parentDatetime`)
                this.parentDatetime = value
            },
        },
        created() {
            this.$store.dispatch('todos/loadFromStorage')
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
