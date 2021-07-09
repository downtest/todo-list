<template>
<div class="nested">
    <draggable
        v-model="modelValue"
        item-key="id"
        v-bind="dragOptions"
        tag="div"
        handle=".item--handle"
        class="item-container"
        :data-parent-id="parentId"
        @input="emitter"
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
            onEnd(value) {
                // Обновляем старого родителя
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

                this.saveChangesToLS()
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
    margin: 0 0 0 10px;
    padding: 0;
    text-align: left;
}
</style>
