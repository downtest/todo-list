<template>
<div class="nested">
    <draggable
        v-model="modelValue"
        item-key="id"
        v-bind="dragOptions"
        tag="div"
        handle=".handle"
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
        },
        data() {
            return {
                dragOptions: {
                    animation: 0,
                    group: "description",
                    disabled: false,
                    ghostClass: "item__ghost",
                    chosenClass: "item__chosen",
                    dragClass: "item__drag",
                    delay: 200,
                    delayOnTouchOnly: true,
                    forceFallback: true,
                },
            }
        },
        methods: {
            onChange(value) {
                // console.log(value, `changes in nested`)
                // this.$emit("change in Nested", value)
                if (value.added || value.moved) {
                    let eventElem = value.added || value.moved

                    this.$store.dispatch('todos/updateItem', {
                        id: eventElem.element.id,
                        payload: {
                            parentId: this.parentId,
                            index: eventElem.newIndex,
                        },
                    });

                    // this.$store.dispatch("todos/updateParent", {
                    //     id: value.added.element.id,
                    //     parentId: this.parentId ? this.parentId  : 0,
                    //     newIndex: value.added.newIndex,
                    // });
                }
            },
            onStart(value) {
                if (this.$store.state.todos.focusId) {
                    // При начале перетаскивания сворачиваем редактирование таски
                    this.$store.commit('todos/setFocusId', null)
                }
            },
            onEnd(value) {
                this.$store.dispatch("todos/updateChildren", {
                    parentId: value.from.dataset.parentId,
                    children: this.modelValue,
                });

                if (value.from.dataset.parentId !== value.to.dataset.parentId) {
                    this.$store.dispatch("todos/updateChildren", {
                        parentId: value.to.dataset.parentId,
                        children: Array.from(value.to.childNodes).map(node => this.$store.getters['todos/getById'](node.dataset.id)),
                    });
                }

                this.$emit("end", value)
            },
            emitter(value) {
                // console.log(value, `emitter`)
                this.$emit("input", value);
            },
        },
    };
</script>

<style lang="scss">
.item-container {
    margin: 0 0 0 10px;
    padding: 0;
    text-align: left;
}
</style>
