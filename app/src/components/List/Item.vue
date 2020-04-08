<template>
    <div class="item">
        <div class="item--name" @click="toggleFocus">{{ item.name }} #{{item.id}}</div>
        <textarea v-if="isActive" rows="1" v-model="message"></textarea>
        <nested v-model="children" @input="emitter" @focus="focusHandler" @change="onChange" />
    </div>
</template>

<script>
    export default {
        name: "item",
        data() {
            return {
                isActive: false,
                message: '',
            };
        },
        computed: {
            children: {
                get() {
                    return this.$store.getters['todos/children'](this.item.id);
                },
                set(payload) {
                    // TODO: Обновлять parent_id

                    // Обновляем дочерние пункты
                    this.$store.dispatch("todos/updateChildren", {
                        parentId: this.item.id,
                        children: payload.map(child => child.id)
                    });
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
            toggleFocus () {
                this.$emit('focus', this.item);
                this.isActive = true;
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
            }
        }
    };
</script>

<style lang="scss" scoped>
.item {
    border: 1px solid saddlebrown;
    background: rgba(202,202,202,0.5);
    margin: 10px 0;

    &__ghost {
        border: 2px dotted red;
    }

    &:first-child {
        margin-top: 0;
    }
    &:last-child {
        margin-bottom: 0;
    }
}
</style>
