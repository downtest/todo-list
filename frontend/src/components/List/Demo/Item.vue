<template>
    <div :class="{item: true, hover: hover, updated: true,}" :data-id="modelValue.id">
        <div class="item-row" @mouseover="hover = true" @mouseleave="hover = false">
            <div class="item--handle">=</div>

            <div class="item--name" :title="modelValue.message" @click="toggleFocus(modelValue.id)">
                {{ name }}

                <span v-if="modelValue.datetime">
                    <img class="btn-icon" :src="$store.getters['icons/Calendar']" alt="datetime" :title="modelValue.datetime">
                </span>
            </div>

            <div class="item--labels" v-if="labels">
                <div class="label" :key="index" v-for="(label, index) in labels">
                    {{label}}
                </div>
            </div>

            <div class="item--buttons">
                <span class="btn add-btn" @click="createChild">
                    <img class="btn-icon" :src="$store.getters['icons/Plus']" alt="add" title="Add task">
                </span>
                <span class="btn delete-btn" @click="deleteTask">
                    <img class="btn-icon" :src="$store.getters['icons/Trash']" alt="delete" title="Delete">
                </span>
            </div>
        </div>

        <div class="item--edit" v-if="isActive">
            <label class="edit--label">
                <textarea class="edit--message" rows="5" v-model="message"></textarea>
            </label>

            <label class="edit--label">
                Дата: <input type="datetime-local" v-model="datetime">
            </label>

            <label class="edit--label">
                Новый лейбл: <input type="text" v-model="labelInput">
            </label>

            <span class="close-btn" @click="toggleFocus(null)">
                <img class="btn-icon" :src="$store.getters['icons/Plus']" alt="close" title="Close edit window">
            </span>
        </div>

        <nested v-model="children"
                @input="emitter"
                :parentId="modelValue.id"
        />
    </div>
</template>

<script>
export default {
    name: "item",
    components: {
        // https://vuejs.org/v2/guide/components-edge-cases.html#Circular-References-Between-Components
    },
    props: {
        modelValue: {
            required: true,
            type: Object,
        },
        active: {
            required: false,
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            localData: {},
            hover: false,
            labelInput: '',
        };
    },
    watch: {
        labelInput(value) {
            if (value.split(' ').length > 1) {
                // Юзер поставил пробел и надо добавить новый лейбл
                this.$store.dispatch('todos/addLabel', {
                    id: this.modelValue.id,
                    label: {
                        name: value.split(' ')[0],
                        color: this.labelColor,
                    },
                })

                this.labelInput = ''
            }
        }
    },
    computed: {
        isActive() {
            return this.$store.state.todos.focusId === this.modelValue.id
        },
        children: {
            get() {
                return this.$store.getters['todos/children'](this.modelValue.id).sort((a, b) => a.index - b.index)
            },
            set(payload) {
                // Обновляем дочерние пункты
                // Перенёс в Nested
                // console.log(payload, `update children`)
                //
                // this.$store.dispatch("todos/updateChildren", {
                //     parentId: this.modelValue.id,
                //     children: payload
                // });
            },
        },
        labels() {
            if (this.modelValue.updated && this.modelValue.updated.labels) {
                return this.modelValue.updated.labels
            }

            return this.modelValue.labels || []
        },
        message: {
            get() {
                if (this.modelValue.updated && this.modelValue.updated.message) {
                    return this.modelValue.updated.message
                }

                return this.modelValue.message || ''
            },
            set(value) {
                this.$store.dispatch('todos/updateItem', {
                    id: this.modelValue.id,
                    payload: {
                        message: value,
                    },
                })
            },
        },
        name() {
            return this.message.split("\n")[0]
        },
        datetime: {
            get() {
                if (this.modelValue.updated && this.modelValue.updated.datetime) {
                    return this.modelValue.updated.datetime
                }

                return this.modelValue.datetime
            },
            set(datetime) {
                this.$store.dispatch('todos/updateItem', {
                    id: this.modelValue.id,
                    payload: {
                        datetime: datetime,
                    },
                })
            },
        },
    },
    methods: {
        emitter(value) {
            // console.log(value, `emitter in ${this.modelValue.id}`)
            this.$emit("input", value);
        },
        toggleFocus (value) {
            this.$store.commit('todos/setFocusId', value)
        },
        reset () {
            this.$store.dispatch('todos/resetChanges', this.modelValue.id)
        },
        deleteTask() {
            // Удаление пункта из родительского списка дочерей
            this.$store.dispatch('todos/deleteItem', this.modelValue.id)
        },
        deleteLabel(index) {
            this.$store.dispatch('todos/deleteLabel', {
                id: this.modelValue.id,
                index: index,
            })
        },
        createChild() {
            this.$store.dispatch('todos/createItem', {
                parentId: this.modelValue.id,
                message: '',
            })
        },
    },
};
</script>

<style lang="scss">
    @import "src/scss/List/item";
</style>
