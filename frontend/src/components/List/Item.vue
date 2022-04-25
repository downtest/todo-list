<template>
    <div :class="{item: true, active: isActive, hover: hover, updated: !modelValue.updated && !modelValue.isNew}" :data-id="modelValue.id">

        <swiper
            :slides-per-view="'auto'"
            :initial-slide="0"
            @swiper="onSwiper"
            @snapIndexChange="onSlideChange"
        >
            <swiper-slide class="item--content">
                <div class="item--handle" :style="`background-image: ${$store.getters['icons/Move']}`">
                    <span class="handle--bg" :style="`background-image: url(${$store.getters['icons/Move']})`"></span>
<!--                    <img :src="$store.getters['icons/Move']" alt="=" :title="date" @click.prevent="" @touchend.prevent="" @touchstart.prevent="">-->
                </div>

                <div class="item--name" :title="modelValue.message" @click="$router.push({name: 'task-item', params: {itemId: modelValue.id}})">
                    {{ name }}

                    <div class="item--time-block">
                        <span v-if="date" class="time-block--date">
                            <img class="btn-icon" :src="$store.getters['icons/Calendar']" alt="datetime" :title="date">
                            {{date.format('DD.MM.YYYY')}}
                        </span>

                        <span v-if="time" class="time-block--time">
                            <img class="btn-icon" :src="$store.getters['icons/Clock']" alt="datetime" :title="time">
                            {{time}}
                        </span>
                    </div>
                </div>

                <div class="item--labels" v-if="labels">
                    <div class="label" key="index" v-for="(label, index) in labels" :style="`border: 1px solid ${label.color.border}; background-color: ${label.color.background};`">
                        <span class="label-name">{{label.name}}</span>
                    </div>
                </div>

                <span class="btn" @click="toggleMore">
                    <img class="btn__icon" v-if="!showMore" :src="$store.getters['icons/DotsWhite']" alt="reset" title="Undo made changes">
                    <img class="btn__icon" v-else :src="$store.getters['icons/Dots']" alt="reset" title="Undo made changes">
                </span>
            </swiper-slide>

            <swiper-slide class="item--buttons">
                <span class="btn" @click="createChild">
                    <img class="btn__icon" :src="$store.getters['icons/Plus']" alt="add" title="Add child">
                    <span class="btn__title">Add</span>
                </span>

                <span class="btn" @click="deleteTask">
                    <img class="btn__icon" :src="$store.getters['icons/Trash']" alt="delete" title="Delete">
                    <span class="btn__title">Delete</span>
                </span>

                <span class="btn" @click="reset" v-if="isChanged">
                    <img class="btn__icon" :src="$store.getters['icons/Undo']" alt="reset" title="Undo made changes">
                    <span class="btn__title">Reset</span>
                </span>
            </swiper-slide>
        </swiper>


        <div class="item--edit" v-if="isActive">
            <labels :task="modelValue"></labels>

            <div style="display: flex; align-items: center;">
                <span class="close-btn">
                    <img class="btn-icon" :src="$store.getters['icons/Plus']" alt="close" title="Close edit window">
                </span>
            </div>
        </div>

        <nested v-model="children"
                @input="emitter"
                @change="onChange"
                :parentId="modelValue.id"
        />
    </div>
</template>

<script>
import draggable from "vuedraggable"
import { Swiper, SwiperSlide } from 'swiper/vue'
import Labels from "../Item/Labels"

    export default {
        name: "item",
        components: {
            Swiper,
            SwiperSlide,
            draggable,
            Labels,
        },
        props: {
            modelValue: {
                required: true,
                type: Object,
            },
            active: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                localData: {},
                hover: false,
                swiper: null,
                showMore: false,
            };
        },
        watch: {
            isMoreOpened(value) {
                if (!value) {
                    // Закрываем кнопки управления, т.к. они были открыты у другой задачи
                    this.swiper.slidePrev()
                }
            },
        },
        computed: {
            isActive() {
                return this.$store.state.todos.focusId === this.modelValue.id
            },
            isChanged() {
                return this.modelValue.updated
            },
            isMoreOpened() {
                return this.$store.state.todos.moreId === this.modelValue.id
            },
            labels() {
                if (this.modelValue.updated && this.modelValue.updated.labels) {
                    return this.modelValue.updated.labels
                }

                return this.modelValue.labels || []
            },
            children: {
                get() {
                    return this.$store.getters['todos/children'](this.modelValue.id).sort((a, b) => a.index - b.index)
                },
                set(payload) {
                    // Обновляем дочерние пункты
                    // Перенёс в Nested
                    // console.log(payload, `update children`)
                    //
                    // this.$store.dispatch("todos/updateChildren", {
                    //     parentId: this.modelValue.id,
                    //     children: payload
                    // });
                },
            },
            message: {
                get() {
                    if (this.modelValue.updated && this.modelValue.updated.message) {
                        return this.modelValue.updated.message
                    }

                    return this.modelValue.message || ''
                },
                set(value) {
                    this.$store.dispatch('todos/updateItem', {
                        id: this.modelValue.id,
                        payload: {
                            message: value,
                        },
                    })
                },
            },
            name() {
                let name = this.message.split("\n")[0]
                let cutName = name.substr(0, 45)

                if (cutName.length < name.length) {
                    return cutName + '...'
                } else {
                    return cutName
                }
            },
            date() {
                if (this.modelValue.updated && this.modelValue.updated.date) {
                    return this.$moment(this.modelValue.updated.date, 'YYYY-MM-DD')
                } else if (this.modelValue.date) {
                    return this.$moment(this.modelValue.date, 'YYYY-MM-DD')
                }

                return null
            },
            time: {
                get() {
                    if (this.modelValue.updated && this.modelValue.updated.time) {
                        return this.modelValue.updated.time
                    }

                    return this.modelValue.time
                },
                set(time) {
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
            onChange(value) {
                // console.log(value, `changes in item`)

                if (value.added) {
                    // console.log(`set parentId=${this.modelValue.id} on #${value.added.element.id}`)
                    // this.$store.dispatch("todos/updateParent", {
                    //     id: value.added.element.id,
                    //     parentId: this.modelValue.id,
                    //     newIndex: value.added.newIndex,
                    // });
                }
            },
            onEnd(value) {
                console.log(value, `onEnd on ${this.modelValue.id}`)
            },
            emitter(value) {
                // console.log(value, `emitter in ${this.modelValue.id}`)
                this.$emit("input", value);
            },
            // toggleFocus (value) {
            //     this.$store.commit('todos/setFocusId', value)
            // },
            reset () {
                this.$store.dispatch('todos/resetChanges', this.modelValue.id)
            },
            goto() {
                if (!this.$router) {
                    return
                }

                this.$router.push({name: 'task-list', params: {parentId: this.modelValue.id}})
            },
            deleteTask() {
                // Удаление пункта из родительского списка дочерей
                this.$store.dispatch('todos/deleteItem', this.modelValue.id)
            },
            createChild() {
                this.$store.dispatch('todos/createItem', {
                    parentId: this.modelValue.id,
                    message: '',
                }).then((task) => {
                    this.$router.push({name: 'task-item', params: {itemId: task.id}})
                })
            },
            onSwiper(swiper) {
                this.swiper = swiper
            },
            toggleMore() {
                if (!this.showMore) {
                    this.swiper.slideNext()
                } else {
                    this.swiper.slidePrev()
                }
            },
            // Событие от слайдера
            onSlideChange(swiper) {
                if (swiper.progress > 0) {
                    this.$store.commit('todos/setMoreId', this.modelValue.id)
                    this.showMore = true
                } else {
                    this.showMore = false
                }
            },
        },
        activated() {
            // Сбрасываем свайп записи
            this.showMore = false
            this.swiper.slidePrev()
        },
    };
</script>
