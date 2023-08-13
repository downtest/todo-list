<template>
    <img class="icon__small" @click="menuShown = !menuShown" :src="$store.getters['icons/Burger']" alt="menu">

    <div v-if="menuShown" class="item-menu-layout" @click="menuShown = false"></div>
    <div v-if="menuShown" class="item-menu">
        <!-- Дата/время -->
        <div class="menu-item" @click.prevent="shownItems = ['datetime']">Дата/время</div>
        <div v-if="shownItems.indexOf('datetime') !== -1">
            <label class="label">
                <div class="label__name" :title="modelValue.date_utc">Дата</div>
                <input class="label__input" type="date" v-model="itemDate">
                &nbsp;&nbsp;
                <img class="btn-icon" :src="$store.getters['icons/Trash']" @click="itemDate = null" alt="clear"
                     title="Clear">
            </label>

            <label class="label">
                <div class="label__name" :title="modelValue.time_utc">Время</div>
                <input class="label__input" type="time" v-model="itemTime">

                <img class="btn-icon" :src="$store.getters['icons/Trash']" @click="itemTime = null" alt="clear"
                     title="Clear">
            </label>
        </div>

        <!-- Лейблы -->
        <div class="menu-item" @click.prevent="shownItems = ['labels']">Лейблы</div>

        <div v-if="shownItems.indexOf('labels') !== -1">
            <div class="item--description">
                Лейблы - это текстовые ярлыки, чтобы выделить или визуально сгруппировать задачи.
                Например, "Срочно" или "Домашние дела".
            </div>

            <labels :task="modelValue"></labels>
        </div>

        <!-- Дочерняя запись -->
        <div class="menu-item" @click="createChild">Создать дочернюю запись</div>

        <!-- Удалить -->
        <div class="menu-item" @click="deleteItem">Удалить</div>
    </div>
</template>

<script>
import Labels from "./Labels";

export default {
    name: "TaskMenu",
    components: {
        Labels,
    },
    props: {
        modelValue: {
            required: true,
            type: Object,
            default: {},
        },
        menuShown: {
            required: false,
            type: Boolean,
            default: false,
        },
        shownItems: {
            required: false,
            type: Array,
            default: [],
        },
    },
    data() {
        return {
        }
    },
    computed: {
        computedMenuShown: {
            get() {
                return this.menuShown
            },
            set(value) {
                this.$emit('menuShown', value)
            },
        },
        computedShownItems: {
            get() {
                return this.shownItems
            },
            set(value) {
                this.$emit('shownItems', value)
            },
        },
        itemDate: {
            get() {
                if (this.modelValue && this.modelValue.updated && 'date' in this.modelValue.updated) {
                    return this.modelValue.updated.date ?? null
                } else if (this.modelValue) {
                    return this.modelValue.date ?? null
                } else {
                    return null
                }
            },
            set(date) {
                this.$store.dispatch('todos/updateItem', {
                    id: this.modelValue.id,
                    payload: {
                        date: date,
                    },
                })
            },
        },
        itemTime: {
            get() {
                if (this.modelValue && this.modelValue.updated && 'time' in this.modelValue.updated) {
                    return this.modelValue.updated.time ?? null
                } else if (this.modelValue) {
                    return this.modelValue.time ?? null
                } else {
                    return null
                }
            },
            set(time) {
                console.log(time, `setting!`)
                this.$store.dispatch('todos/updateItem', {
                    id: this.modelValue.id,
                    payload: {
                        time: time,
                    },
                })
            },
        },
    },
    methods: {
        createChild() {
            this.$store.dispatch('todos/createItem', {
                parentId: this.modelValue.id,
                message: '',
            }).then((task) => {
                this.$router.push({name: 'task-item', params: {itemId: task.id}})
            }).then(() => {
                this.menuShown = false
            })
        },
        deleteItem() {
            if (this.modelValue.message.length > 2) {
                if (!window.confirm(`Удалить запись?\n${this.modelValue.message}`)) {
                    return
                }
            }

            let taskIdToFocus = this.modelValue.parentId

            this.$store.dispatch('todos/deleteItem', this.modelValue.id)

            if (taskIdToFocus) {
                this.$router.push({name: 'task-item', params: {itemId: taskIdToFocus}})
            } else {
                this.$router.push({name: 'task-list'})
            }
        },
    },
}
</script>

<style scoped>

</style>