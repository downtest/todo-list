<template>
    <div class="list">
        <div class="justify-content-between row">

            <tasks-breadcrumb v-if="parentId" :id="parentId"></tasks-breadcrumb>

            <search></search>

            <div v-if="this.$store.getters['todos/getChanges'].length" @click="this.$store.dispatch('todos/save')">save ({{$store.getters['todos/getChanges'].length}})</div>

            <div v-if="parent">
                <h1>{{parent.message.split('\n')[0]}}</h1>

                <div>
                    <contenteditable
                        :content="parentMessage"
                        :handleWordFunction="handleWordFunction"
                        @setTime="setTimeHandler"
                    ></contenteditable>
                </div>

                <label>
                    <input class="parent--input" type="date" v-model="parentDate">
                </label>

                <label>
                    <input class="parent--input" type="time" v-model="parentTime">
                </label>

                <router-link v-if="parentDate" :to="{name: 'calendarDay', params: {day: parentDate}}">
                    Посмотреть в календаре
                </router-link>
            </div>

            <nested
                v-model="elements"
                :parentId="parentId"
            />

            <div class="btn_add" @click="createChild">
                <img class="btn__icon" :src="$store.getters['icons/PlusWhite']" alt="add" title="Add task">
            </div>

        </div>
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
import search from "./List/Search"

    export default {
        components: {
            nested,
            tasksBreadcrumb,
            contenteditable,
            draggable,
            search,
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
            return {}
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
        activated() {
            this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
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
