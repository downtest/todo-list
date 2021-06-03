<template>
    <div :class="{item: true, hover: hover, confirmed: modelValue.confirmed}" :data-id="modelValue.id">
        <div class="item-row" @mouseover="hover = true" @mouseleave="hover = false">
            <div class="item--name" :title="modelValue.message" @click="toggleFocus(modelValue.id)">
                #{{ modelValue.id }} {{ name }} (i:{{modelValue.index}}, parent:{{modelValue.parentId}})

                <span v-if="!modelValue.confirmed">НЕТ на бэке</span>
                <span v-else>есть на бэке</span>

                <span v-if="isActive">#{{modelValue.id}}</span>

                <span v-if="modelValue.datetime">
                    <img class="btn-icon" src="../../../assets/icons/calendar.svg" alt="datetime" :title="modelValue.datetime">
                </span>
            </div>

            <div class="item--labels" v-if="modelValue.labels">
                <div class="label" :key="index" v-for="(label, index) in modelValue.labels">
                    {{label}}
                    <span v-if="isActive" class="close-btn" @click="deleteLabel(index)">
                        <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="delete" title="Delete label">
                    </span>
                </div>
            </div>

            <div class="item--buttons">
                <span class="btn go-btn" @click="goto" v-if="$route.params.parentId != modelValue.id">
                    <img class="btn-icon" src="../../../assets/icons/right_arrow.svg" alt="go" title="Focus on task">
                </span>
                <span class="btn add-btn" @click="createChild">
                    <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="add" title="Add task">
                </span>
                <span class="btn delete-btn" @click="deleteTask">
                    <img class="btn-icon" src="../../../assets/icons/trash.svg" alt="delete" title="Delete">
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
                <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="close" title="Close edit window">
            </span>
        </div>

        <nested v-model="children"
                @input="emitter"
                @focus="focusHandler"
                @change="onChange"
                :focusId="focusId"
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
            focusId: {
                required: false,
                type: String,
                default: null,
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
                return this.focusId === this.modelValue.id
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
            message: {
                get() {
                    return this.modelValue.message || ''
                },
                set(value) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.modelValue.id,
                        payload: {
                            name: value.split("\n")[0],
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
            focusHandler(value) {
                this.$emit('focus', value);
            },
            toggleFocus (value) {
                this.$emit('focus', (value) ? value : null);
            },
            goto() {
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
                    // TODO: Исправить фокусировку после создания
                    .then(task => this.toggleFocus(task.id))
            },
        },
    };
</script>

<style lang="scss">
.item {
    border: 1px solid #2c3e50;
    background-color: rgba(250, 250, 0, 0.15);
    margin: 5px -1px 5px 0;
    padding: 3px 0 3px 5px;
    transition: .1s;

    .item-row {
        display: flex;
        justify-content: space-between;

        .item--name {

        }

        .item--labels {

            .label {
                display: inline-block;
                margin: 0 5px;
                padding: 0 3px;
                border: 1px solid #2c3e50;
                background: rebeccapurple;
                color: antiquewhite;
                border-radius: 5px;
            }
        }

        .item--buttons {

        }
    }

    .item--edit {

        .edit--label {
            display: block;
        }
    }

    .btn {
        display: inline-block;
        margin: 0 5px;
    }

    &__ghost {
        border: 2px dotted red;
    }

    &.confirmed {
        background-color: rgba(30, 30, 30, 0.15);
    }

    &__chosen {
        animation: shaker .5s ease-in-out;
    }

    &__drag {
        display: none;
    }

    &.hover {
        transition: .1s;
        box-shadow: 3px 3px 5px rgba(0,0,0,0.5);
        margin-left: -1px;
        margin-right: 0;
    }

    &:first-child {
        margin-top: 0;
    }
    &:last-child {
        margin-bottom: 0;
    }
}

.nested {
    text-align: center;
}

.btn-icon {
    cursor: pointer;
    max-height: 20px;
    width: 20px;
}
.close-btn {
    .btn-icon {
        transform: rotate(45deg);
    }
}

@keyframes shaker {
    from,
    to {
        transform: none
    }
    25% {transform: rotate(+1deg)}
    50% {transform: rotate(-.7deg)}
    75% {transform: rotate(.4deg)}
}

</style>
