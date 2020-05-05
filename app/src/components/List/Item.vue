<template>
    <div :class="{item: true, hover: hover}">
        <div class="item-row" @mouseover="hover = true" @mouseleave="hover = false">
            <div class="item--name" :title="item.message" @click="toggleFocus(item.id)">
                {{ name }}
                <span v-if="isActive">#{{item.id}}</span>
                <span v-if="item.datetime">
                    <img class="btn-icon" src="../../../assets/icons/calendar.svg" alt="datetime" :title="item.datetime">
                </span>
            </div>

            <div class="item--buttons">
                <span class="btn go-btn" @click="goto" v-if="$route.params.parentId != item.id">
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
            <textarea rows="5" v-model="message"></textarea>
            <br>
            Дата: <input type="datetime-local" v-model="datetime">

            <span class="close-btn" @click="toggleFocus(null)">
                <img class="btn-icon" src="../../../assets/icons/plus.svg" alt="close" title="Close edit window">
            </span>
        </div>

        <nested v-model="children" @input="emitter" @focus="focusHandler" @change="onChange" :focusId="focusId" />
    </div>
</template>

<script>
    export default {
        name: "item",
        data() {
            return {
                localData: {},
                hover: false,
            };
        },
        computed: {
            isActive() {
                return this.focusId === this.item.id
            },
            name() {
                return this.message.split("\n")[0]
            },
            children: {
                get() {
                    return this.$store.getters['todos/children'](this.item.id);
                },
                set(payload) {
                    // Обновляем дочерние пункты
                    this.$store.dispatch("todos/updateChildren", {
                        parentId: this.item.id,
                        children: payload.map(child => child.id)
                    });
                },
            },
            message: {
                get() {
                    return this.item.message
                },
                set(value) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.item.id,
                        payload: {
                            name: value.split("\n")[0],
                            message: value,
                        }
                    })
                },
            },
            datetime: {
                get() {
                    return this.item.datetime
                },
                set(datetime) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.item.id,
                        payload: {datetime}
                    })
                },
            },
        },
        methods: {
            onChange(value) {
                // console.log(value, `change on ${this.item.id}`)
                if (value.added) {
                    // console.log(`set parentId=${this.item.id} on #${value.added.element.id}`)
                    this.$store.dispatch("todos/updateParent", {
                        id: value.added.element.id,
                        parentId: this.item.id,
                        newIndex: value.added.newIndex,
                    });
                }
            },
            onEnd(value) {
                console.log(value, `onEnd on ${this.item.id}`)
            },
            emitter(value) {
                this.$emit("input", value);
            },
            focusHandler(value) {
                this.$emit('focus', value);
            },
            toggleFocus (value) {
                this.$emit('focus', (value) ? value : null);
            },
            goto() {
                this.$router.push({name: 'task-list', params: {parentId: this.item.id}})
            },
            deleteTask() {
                // Удаление пункта из родительского списка дочерей
                this.$store.commit('todos/removeChild', {parentId: this.item.parent_id, childId: this.item.id})

                this.$store.dispatch('todos/deleteItem', this.item.id)
            },
            createChild() {
                console.log(`creating child for ${this.item.id}`)
                this.$store.dispatch('todos/createItem', {parentId: this.item.id, payload: {
                    message: 'New',
                }})
            },
        },
        components: {
            // https://vuejs.org/v2/guide/components-edge-cases.html#Circular-References-Between-Components
            nested: () => import('./Nested'),
        },
        props: {
            item: {
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
                type: Number,
                default: null,
            },
        }
    };
</script>

<style lang="scss">
.item {
    border: 1px solid #2c3e50;
    background: rgba(30, 30, 30, 0.15);
    margin: 5px -1px 5px 0;
    padding: 3px 0 3px 5px;
    transition: .1s;

    .item-row {
        display: flex;
        justify-content: space-between;

        .item--name {

        }

        .item--buttons {

        }

        .item--edit {

        }
    }

    .btn {
        display: inline-block;
        margin: 0 5px;
    }

    &__ghost {
        border: 2px dotted red;
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

</style>
