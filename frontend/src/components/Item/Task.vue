<template>
    <div class="list">
        <div class="justify-content-between row">

            <breadcrumb v-if="itemId" :id="itemId"></breadcrumb>

            <div class="item-status-bar" v-if="isChanged || isNew">
                <img class="icon__small" :src="$store.getters['icons/NotesCloudCrossedNo2']" alt="modified" title="has unsaved changes">
            </div>

            <div v-if="this.$store.getters['todos/getTaskChanges'](itemId)">
                <button @click="this.$store.dispatch('todos/resetChanges', itemId)">Сбросить изменения</button>
                <button @click="this.$store.dispatch('todos/save')">Сохранить</button>
            </div>

            <div v-if="item">
                <div>
                    <contenteditable-component
                        :task="item"
                        @update="updateMessage"
                        @setTime="setTimeHandler"
                    ></contenteditable-component>
                </div>

                <label class="label">
                    <div class="label__name" :title="item.date_utc">Дата</div>
                    <input class="label__input" type="date" v-model="itemDate">
                    &nbsp;&nbsp;
                    <img class="btn-icon" :src="$store.getters['icons/Trash']" @click="itemDate = null" alt="clear"
                         title="Clear">
                </label>

                <label class="label">
                    <div class="label__name" :title="item.time_utc">Время</div>
                    <input class="label__input" type="time" v-model="itemTime">
                    &nbsp;&nbsp;
                    <img class="btn-icon" :src="$store.getters['icons/Trash']" @click="itemTime = null" alt="clear"
                         title="Clear">
                </label>

                <div v-if="item.informed">
                    <small><i>Уведомление отправлено: {{ item.informed }}</i></small>
                </div>

                <router-link v-if="itemDate" :to="{name: 'calendarDay', params: {day: itemDate}}">
                    Посмотреть в календаре
                </router-link>

                <h3>Лейблы</h3>

                <labels :task="item"></labels>
            </div>

            <nested
                v-model="children"
                :parentId="itemId"
            />

            <div class="btn_add" @click="createChild">
                <img class="btn__icon" :src="$store.getters['icons/PlusWhite']" alt="add" title="Add task">
            </div>

        </div>
    </div>
</template>

<script>
import nested from "../List/Nested";
import contenteditableComponent from "../Contenteditable";
import Breadcrumb from "../List/Breadcrumb";
import Labels from "./Labels";

export default {
    components: {
        nested,
        Breadcrumb,
        contenteditableComponent,
        Labels,
    },
    props: {
        itemId: {
            required: true,
            type: String,
            default: ''
        },
    },
    data() {
        return {
            itemDatetime: false,
        }
    },
    computed: {
        isNew() {
            if (!this.item) {
                return false
            }

            return this.item.isNew
        },
        isActive() {
            if (!this.item) {
                return false
            }

            return this.$store.state.todos.focusId === this.item.id
        },
        isChanged() {
            if (!this.item || !this.item.updated) {
                return false
            }

            // Ключи, которые проверяются. И если они изменились, то таска считается изменённой
            let searchableKeys = [
                'message',
                'labels',
                'date',
                'time',
            ]
            let intersections = Object.keys(this.item.updated).filter(value => searchableKeys.includes(value))

            return intersections.length > 0
        },
        item() {
            if (this.itemId) {
                return this.$store.getters['todos/getById'](this.itemId);
            } else {
                return null;
            }
        },
        itemMessage: {
            get() {
                if (this.item) {
                    return this.item.message;
                } else {
                    return '';
                }
            },
            set(message) {
                message = message.replace(/<\/?[^>]+(>|$)/g, "")
                this.$store.dispatch('todos/updateItem', {
                    id: this.item.id,
                    payload: {
                        message: message,
                    },
                })
            },
        },
        itemDate: {
            get() {
                if (this.item && this.item.updated && 'date' in this.item.updated) {
                    return this.item.updated.date ?? null
                } else if (this.item) {
                    return this.item.date ?? null
                } else {
                    return null
                }
            },
            set(date) {
                this.$store.dispatch('todos/updateItem', {
                    id: this.item.id,
                    payload: {
                        date: date,
                    },
                })
            },
        },
        itemTime: {
            get() {
                if (this.item && this.item.updated && 'time' in this.item.updated) {
                    return this.item.updated.time ?? null
                } else if (this.item) {
                    return this.item.time ?? null
                } else {
                    return null
                }
            },
            set(time) {
                this.$store.dispatch('todos/updateItem', {
                    id: this.item.id,
                    payload: {
                        time: time,
                    },
                })
            },
        },
        children: {
            get() {
                return this.$store.getters['todos/children'](this.itemId)
            },
            set(payload) {
                // this.$store.dispatch("todos/updateChildren", {
                //     itemId: this.itemId ? this.itemId  : 0,
                //     children: payload.map(child => child.id)
                // });
            },
        },
    },
    watch: {
        itemMessage(value) {
            document.title = this.$store.getters['constants/titlePrefix'] + value.substr(0, 20)
        },
    },
    methods: {
        createChild() {
            this.$store.dispatch('todos/createItem', {
                parentId: this.itemId,
                message: '',
            }).then((task) => {
                this.$router.push({name: 'task-item', params: {itemId: task.id}})
            })
        },
        setTimeHandler(value) {
            this.itemDatetime = value
        },
        updateMessage(value) {
            this.itemMessage = value
        },
    },
    activated() {
        if (this.$store.getters['user/current']['id']) {
            this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
        }
        // this.$store.dispatch('todos/resetFocus')
    },
}
</script>

<style lang="scss">

.item {

    &--input {
        width: 100%;
        margin-bottom: 10px;
    }
}

</style>
