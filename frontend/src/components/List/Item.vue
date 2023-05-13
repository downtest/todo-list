<template>
    <div :class="{item: true, hover: hover, updated: !isChanged && !isNew}" :data-id="modelValue.id">

        <swiper
            :slides-per-view="'auto'"
            @swiper="onSwiper"
            @snapIndexChange="onSlideChange"
        >
            <swiper-slide>
<!--                <div class="item&#45;&#45;handle" :style="`background-image: ${$store.getters['icons/Move']}`">-->
<!--                    <span class="handle&#45;&#45;bg" :style="`background-image: url(${$store.getters['icons/Move']})`"></span>-->
<!--&lt;!&ndash;                    <img :src="$store.getters['icons/Move']" alt="=" :title="date" @click.prevent="" @touchend.prevent="" @touchstart.prevent="">&ndash;&gt;-->
<!--                </div>-->

                <div class="item--content">
<!--                    <div class="item&#45;&#45;dots">-->
<!--                        <div-->
<!--                            v-if="$store.getters['todos/parents'](modelValue.id).length > 1"-->
<!--                            v-for="i in $store.getters['todos/parents'](modelValue.id).length - subDots"-->
<!--                            class="dot"-->
<!--                        ></div>-->
<!--                    </div>-->

                    <div style="width: 100%;">
                        <div style="display: flex;">
                            <div class="item--name" :title="modelValue.message" @click="$router.push({name: 'task-item', params: {itemId: modelValue.id}})">
                            <span v-if="isChanged || isNew">
                                <img class="icon__small" :src="$store.getters['icons/NotesCloudCrossedNo2']" alt="modified" title="has unsaved changes">
                            </span>

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

                            <span class="btn">
                                <img class="btn__icon" v-if="!isMoreOpened" @click="slideNext()" :src="$store.getters['icons/DotsWhite']" alt="reset" title="Undo made changes">
                                <img class="btn__icon" v-else @click="slidePrev()" :src="$store.getters['icons/Dots']" alt="reset" title="Undo made changes">
                            </span>
                        </div>


                    </div>
                </div>


                <div class="item--children">
                    <div v-if="children.length > 0" class="children--buttons">
                        <span @click="toggleShowChildren" class="children--button">
                            <template v-if="!showChildren">Вложенных: {{children.length}}</template>
                            <template v-else>Скрыть</template>
                        </span>
                    </div>

                    <nested v-model="children"
                            @input="emitter"
                            @change="onChange"
                            :parentId="modelValue.id"
                            :show="showChildren"
                            :subDots="subDots"
                    />
                </div>


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

        <div class="item--edit" v-if="false">
            <labels :task="modelValue"></labels>

            <div style="display: flex; align-items: center;">
                <span class="close-btn">
                    <img class="btn-icon" :src="$store.getters['icons/Plus']" alt="close" title="Close edit window">
                </span>
            </div>
        </div>

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
            subDots: {
                required: false,
                type: Number,
                default: 1
            },
        },
        data() {
            return {
                localData: {},
                hover: false,
                swiper: null,
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
            isNew() {
                return this.modelValue.isNew
            },
            isChanged() {
                if (!this.modelValue.updated) {
                    return false
                }

                // Ключи, которые проверяются. И если они изменились, то таска считается изменённой
                let searchableKeys = [
                    'message',
                    'labels',
                    'date',
                    'time',
                ]
                let intersections = Object.keys(this.modelValue.updated).filter(value => searchableKeys.includes(value))

                return intersections.length > 0
            },
            showChildren() {
                if (this.modelValue.updated && this.modelValue.updated.hasOwnProperty('showChildren')) {
                    return this.modelValue.updated.showChildren
                } else if (this.modelValue.hasOwnProperty('showChildren')) {
                    return this.modelValue.showChildren
                }

                return true
            },
            isMoreOpened() {
                return this.$store.getters['todos/moreId'] === this.modelValue.id
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
                if (!this.isMoreOpened) {
                    this.slideNext()
                } else {
                    this.slidePrev()
                }
            },
            slidePrev() {
                this.swiper.slidePrev()
            },
            slideNext() {
                this.swiper.slideNext()
            },
            // Событие от слайдера
            onSlideChange(swiper) {
                if (swiper.progress > 0) {
                    this.$store.dispatch('todos/setMoreId', this.modelValue.id)
                } else {
                    if (this.isMoreOpened) {
                        this.$store.dispatch('todos/setMoreId', null)
                    }
                }
            },
            toggleShowChildren() {
                this.$store.dispatch('todos/updateItem', {
                    id: this.modelValue.id,
                    payload: {
                        showChildren: !this.showChildren,
                    },
                })
            },
        },
        activated() {
            // Костыль: При создании записи и возврате к списку, Сбрасываем свайп записи
            setTimeout(() => {
                this.swiper.slidePrev(0)
            }, 0)
        },
    };
</script>
