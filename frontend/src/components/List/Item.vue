<template>
    <div :class="{item: true, hover: hover, updated: !modelValue.updated && !modelValue.isNew}" :data-id="modelValue.id">
        <div class="item-row" @mouseover="hover = true" @mouseleave="hover = false">
            <div class="item--handle">=</div>

            <div class="item--name" :title="modelValue.message" @click="toggleFocus(modelValue.id)">
                #{{ modelValue.id }} {{ name }} (i:{{modelValue.index}}, parent:{{modelValue.parentId}})

                <span v-if="modelValue.updated">Изменено</span>
                <span v-else>Без изменений</span>

                <span v-if="isActive">#{{modelValue.id}}</span>

                <span v-if="modelValue.datetime">
                    <img class="btn-icon" src="../../../assets/icons/calendar.svg" alt="datetime" :title="modelValue.datetime">
                </span>
            </div>

            <div class="item--labels" v-if="labels">
                <div class="label" :key="index" v-for="(label, index) in labels">
                    {{label}}
                    <span v-if="isActive" class="close-btn" @click="deleteLabel(index)">
                        <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="delete" title="Delete label">
                    </span>
                </div>
            </div>

            <div class="item--buttons">
                <span class="btn go-btn" @click="goto" v-if="$route && $route.params.parentId != modelValue.id">
                    <img class="btn-icon" src="../../../assets/icons/right_arrow.svg" alt="go" title="Focus on task">
                </span>
                <span class="btn add-btn" @click="createChild">
                    <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="add" title="Add task">
                </span>
                <span class="btn delete-btn" @click="deleteTask">
                    <img class="btn-icon" src="../../../assets/icons/trash.svg" alt="delete" title="Delete">
                </span>
                <span class="btn" @click="reset">Reset</span>
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
                <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="close" title="Close edit window">
            </span>
        </div>

        <nested v-model="children"
                @input="emitter"
                @change="onChange"
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
                        label: value.split(' ')[0],
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
            onChange(value) {
                // console.log(value, `changes in item`)

                if (value.added) {
                    // console.log(`set parentId=${this.modelValue.id} on #${value.added.element.id}`)
                    // this.$store.dispatch("todos/updateParent", {
                    //     id: value.added.element.id,
                    //     parentId: this.modelValue.id,
                    //     newIndex: value.added.newIndex,
                    // });
                }
            },
            onEnd(value) {
                console.log(value, `onEnd on ${this.modelValue.id}`)
            },
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
            goto() {
                if (!this.$router) {
                    return
                }

                this.$router.push({name: 'task-list', params: {parentId: this.modelValue.id}})
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
