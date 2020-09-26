<template>
<div class="nested">
    <draggable
            v-bind="dragOptions"
            tag="div"
            class="item-container"
            :value="value"
            @input="emitter"
            @change="change"
            @start="onStart"
            @end="onEnd"
    >
        <item
                :key="el.id"
                :item="el"
                :focusId="focusId"
                v-for="el in value"
                @focus="focusHandler"
        />
    </draggable>
</div>
</template>

<script>
    export default {
        name: "nested",
        methods: {
            change(value) {
                this.$emit("change", value)
            },
            onStart() {
                this.focusHandler(null)
            },
            onEnd(value) {
                this.$emit("end", value)
            },
            emitter(value) {
                this.$emit("input", value);
            },
            focusHandler(value) {
                this.$emit("focus", value);
            },
        },
        components: {
            draggable: () => import('vuedraggable'),
            item: () => import('./Item'),
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
        props: {
            value: {
                required: false,
                type: Array,
                default: null
            },
            focusId: {
                required: false,
                type: Number,
                default: null
            },
        }
    };
</script>

<style lang="scss">
.item-container {
    margin: 0 0 0 10px;
    padding: 0;
    text-align: left;
}
</style>
