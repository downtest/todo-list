<template>
<div class="nested">
    <draggable
        v-model="nodes"
        item-key="id"
        v-bind="dragOptions"
        tag="div"
        handle=".item--content"
        class="item-container"
        :data-parent-id="parentId"
        @input="emitter"
        @change="onChange"
        @start="onStart"
        @end="onEnd"
    >
        <template #item="{element}">
            <item
                :modelValue="element"
                :subDots="subDots"
            />
        </template>
    </draggable>
</div>
</template>

<script>
import draggable from "vuedraggable"

    export default {
        name: "nested",
        components: {
            draggable,
        },
        props: {
            modelValue: {
                required: true,
                type: Array,
                default: null
            },
            parentId: {
                required: false,
                type: String,
                default: null
            },
            subDots: {
                required: false,
                type: Number,
                default: 1
            },
            show: {
                required: false,
                type: Boolean,
                default: true
            },
        },
        data() {
            return {
                dragOptions: {
                    animation: 150,
                    group: "description",
                    disabled: false,
                    ghostClass: "item__ghost",
                    chosenClass: "item__chosen",
                    dragClass: "item__drag",
                    delay: 100,
                    delayOnTouchOnly: true,
                    forceFallback: true,
                },
            }
        },
        computed: {
            nodes() {
                if (this.show) {
                    return this.modelValue
                }

                return []
            }
        },
        methods: {
            onChange(value) {
                // console.log(value, `changes in nested`)
                // this.$emit("change in Nested", value)
                if (value.added || value.moved) {
                    let eventElem = value.added || value.moved

                    if (!eventElem.element.isNew) {
                        // Отправляем запрос на сервер для изменения позиции таски только если таска уже сохранена на сервере
                        this.$store.dispatch('todos/dragItem', {
                            id: eventElem.element.id, // ID таски
                            parentId: this.parentId, // ID родителя
                            index: eventElem.newIndex // индекс в новом родителе
                        });
                    }
                }
            },
            onStart(value) {
                return

                if (this.$store.state.todos.focusId) {
                    // При начале перетаскивания сворачиваем редактирование таски
                    this.$store.commit('todos/setFocusId', null)
                }
            },
            onEnd(value) {
                // Обновляем старого родителя
                this.$store.dispatch("todos/updateChildren", {
                    id: value.item.dataset.id,
                    oldParentId: value.from.dataset.parentId || null,
                    newParentId: value.to.dataset.parentId || null,
                    oldIndex: value.oldIndex,
                    newIndex: value.newIndex,
                });

                if (value.from.dataset.parentId !== value.to.dataset.parentId) {
                    // Обновляем нового родителя
                    this.$store.dispatch("todos/updateChildren", {
                        parentId: value.to.dataset.parentId,
                        children: Array.from(value.to.childNodes).map(node => this.$store.getters['todos/getById'](node.dataset.id)),
                    });
                }

                let element = this.$store.getters['todos/getById'](value.clone.dataset.id)

                if (element.isNew) {
                    // Для новых елементов сохраняем порядок сортировки
                    this.saveChangesToLS()
                }

                this.$emit("end", value)
            },
            saveChangesToLS() {
                window.localStorage.setItem('ls_todos_unconfirmed_items', JSON.stringify(this.$store.getters['todos/getChanges']))
            },
            emitter(value) {
                // console.log(value, `emitter`)
                this.$emit("input", value)
            },
        },
    };
</script>

<style lang="scss">
.item-container {
    padding: 0;
    text-align: left;
    min-height: 1px;
}

.item__chosen {
    background: #efefef;
}

.item__ghost {
    opacity: .6;

    .item--dots {
        display: none;
    }

    .item--name {
        padding-left: 10px;
    }
}
</style>
