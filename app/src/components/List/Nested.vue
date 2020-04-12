<template>
<div class="nested">
    <draggable
            v-bind="dragOptions"
            tag="div"
            class="item-container"
            :value="value"
            @input="emitter"
            @change="change"
            @end="onEnd"
            @update="update"
    >
        <item
                :key="el.id"
                :item="el"
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
            update(value) {
                console.log(value, 'update event')
            },
            change(value) {
                // console.log(value, 'change event')
                this.$emit("change", value)
            },
            onEnd(value) {
                this.$emit("end", value)

                console.log(value, `onEnd event, emit on parent`)


            },
            emitter(value) {
                console.log(value, 'input event')
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
        computed: {
            dragOptions() {
                return {
                    animation: 0,
                    group: "description",
                    disabled: false,
                    ghostClass: "item__ghost"
                };
            },
        },
        props: {
            value: {
                required: false,
                type: Array,
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
