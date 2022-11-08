<template>
    <div class="list">
        <div class="justify-content-between row">

            <tasks-breadcrumb v-if="parentId" :id="parentId"></tasks-breadcrumb>

            <router-link v-if="$store.getters['collections/current']" to="/collections" class="collections-link">
                <img class="collections-link--icon" :src="$store.getters['icons/Briefcase']" alt="collections" title="collections">
                <span class="collections-link--name">{{$store.getters['collections/current']['name']}}</span>
            </router-link>

            <button v-if="$store.getters['todos/loading'] || this.$store.getters['todos/getChanges'].length" @click="this.$store.dispatch('todos/resetAllChanges')">Сбросить изменения</button>
            <button v-if="$store.getters['todos/loading'] || this.$store.getters['todos/getChanges'].length" @click="this.$store.dispatch('todos/save')">Сохранить все изменения ({{$store.getters['todos/unconfirmed'].length}})</button>


            <div v-if="$store.getters['todos/loading']">
                Loading...
            </div>

            <div v-else>
                <div v-if="elements.length <= 0" class="label_tip">
                    У Вас нет ни одной записи, нажмите на кнопку с плюсом ниже чтобы создать заметку, записать рецепт, поставить напоминание...
                </div>

                <nested
                    v-model="elements"
                    :parentId="parentId"
                />
            </div>

            <div class="btn_add" @click="createChild">
                <img class="btn__icon" :src="$store.getters['icons/PlusWhite']" alt="add" title="Add task">
            </div>

        </div>

        <button @click="showChanges = !showChanges" style="text-align: left">Показать изменения</button>
        <pre v-if="showChanges" style="text-align: left; width: 100%;">{{$store.getters['todos/getChanges']}}</pre>
    </div>
</template>

<script>
// Import Swiper Vue.js components
import { Swiper, SwiperSlide } from 'swiper/vue';
// Import Swiper styles
import 'swiper/css';

import nested from "./List/Nested";
import tasksBreadcrumb from "./List/Breadcrumb";
import contenteditable from "./Contenteditable";
import draggable from "vuedraggable"

    export default {
        components: {
            nested,
            tasksBreadcrumb,
            contenteditable,
            draggable,
            Swiper,
            SwiperSlide,
        },
        props: {
            title: {
                required: false,
                type: String,
                default: ''
            },
            // parentId: {
            //     required: false,
            //     type: [String, Number],
            // },
            value: {
                required: false,
                type: Array,
                default: null
            },
        },
        data() {
            return {showChanges: false,}
        },
        computed: {
            parentId() {
                if (!this.$route || !this.$route.params.parentId) {
                    return null
                }

                return this.$route.params.parentId
            },
            parent() {
                if (this.parentId) {
                    return this.$store.getters['todos/getById'](this.parentId);
                } else {
                    return null;
                }
            },
            parentMessage: {
                get() {
                    if (this.parent) {
                        return this.parent.message;
                    } else {
                        return null;
                    }
                },
                set(message) {
                    message = message.replace(/<\/?[^>]+(>|$)/g, "")
                    this.$store.dispatch('todos/updateItem', {
                        id: this.parent.id,
                        payload: {
                            message: message,
                        },
                    })
                },
            },
            parentDate: {
                get() {
                    if (this.parent) {
                        return this.parent.date;
                    } else {
                        return null;
                    }
                },
                set(date) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.parent.id,
                        payload: {
                            date: date,
                        },
                    })
                },
            },
            parentTime: {
                get() {
                    if (this.parent) {
                        return this.parent.time;
                    } else {
                        return null;
                    }
                },
                set(time) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.parent.id,
                        payload: {
                            time: time,
                        },
                    })
                },
            },
            elements: {
                get() {
                    if (this.parentId) {
                        return this.$store.getters['todos/children'](this.parentId).sort((a, b) => a.index - b.index)
                    } else {
                        return this.$store.getters['todos/firstLevel'].sort((a, b) => a.index - b.index)
                    }
                },
                set(payload) {
                    // this.$store.dispatch("todos/updateChildren", {
                    //     parentId: this.parentId ? this.parentId  : 0,
                    //     children: payload.map(child => child.id)
                    // });
                },
            },
        },
        methods: {
            createChild () {
                this.$store.dispatch('todos/createItem', {
                    parentId: this.parentId,
                    message: '',
                }).then((task) => {
                    this.$router.push({name: 'task-item', params: {itemId: task.id}})
                })
            },
            handleWordFunction(node) {
                if (node.firstChild.length > 3) {
                    node.classList.add(`green`)
                }
            },
            setTimeHandler(value) {
                this.parentDatetime = value
            },
        },
        // Swiper setup
        setup() {
            const onSlideChangeTransitionStart = (swiper) => {
                console.log(`trans start`)
            }
            const onSlideChangeTransitionEnd = (swiper) => {
                console.log(`trans end`)
            }
            return {
                onSlideChangeTransitionStart,
                onSlideChangeTransitionEnd,
            };
        },
    }
</script>

<style lang="scss">

.parent {

    &--input {
        width: 100%;
        margin-bottom: 10px;
    }
}

</style>
