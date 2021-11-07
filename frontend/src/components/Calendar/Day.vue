<template>
<div>
    <h1>
        <span class="month-header" @click="$router.push({name: 'calendarMonth', params: {month: today.format('YYYY-MM')}})">{{monthsTitleEng[today.month()]}}</span>
        {{today.year()}}
    </h1>

    <button @click="scrollToCurrent(false)">Scroll</button>

    <template v-if="$store.getters['todos/getChanges'].length">
        <button @click="$store.dispatch('todos/resetAllChanges')">Сброс изменений ({{$store.getters['todos/getChanges'].length}})</button>
        <button @click="$store.dispatch('todos/save')">Сохранение ({{$store.getters['todos/getChanges'].length}})</button>
    </template>

    <div class="days-row" @scroll="handleWheel">
        <div class="days-wrapper" id="days">
            <div :class="{
                    'day': true,
                    'current-day': today.isSame(neighborDay) ? true : false,
                    'weekend': [6, 0].includes(neighborDay.day()),
                }"
                 :key="index" v-for="(neighborDay, index) in calendarDays"
                 @click="$router.push({name: 'calendarDay', params: {day: neighborDay.format('YYYY-MM-DD')}})"
            >
                <div :class="{
                        'day__date': true,
                        'weekend': [6, 0].includes(neighborDay.day()),
                     }"
                >
                    {{neighborDay.date()}}
                </div>
                <div class="day__month">{{monthsTitleEng[neighborDay.month()]}}</div>

                <label class="day__tasksLabel" v-if="groupedByDatesTasks[`${neighborDay.year()}.${neighborDay.month()}.${neighborDay.date()}`]">
                    {{groupedByDatesTasks[`${neighborDay.year()}.${neighborDay.month()}.${neighborDay.date()}`].length}}
                </label>
            </div>
        </div>
    </div>

    <div class="day-content">
        <todos-list :value="groupedByDatesTasks[`${today.year()}.${today.month()}.${today.date()}`]" :createChild="createChild"></todos-list>
    </div>

</div>
</template>

<script>
import moment from "moment"
import TodosList from "../List/TodosList"

export default {
    name: "Day",
    components: {
        TodosList,
    },
    props: {
        day: {
            type: String,
            required: false,
            default: () => {
                return moment().format('YYYY-MM-DD')
            },
        },
    },
    data() {
        return {
            monthsTitleEng: [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ],
            daysTitleEng: [
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
            ],
            subDays: 30,
            addDays: 30,
        }
    },
    computed: {
        today() {
            let moment = this.$moment(this.day, 'YYYY-MM-DD')

            return moment.isValid() ? moment : this.$moment()
        },
        startPeriod() {
            return this.$moment(this.$route.params.day, 'YYYY-MM-DD').subtract(this.subDays, 'day')
        },
        endPeriod() {
            return this.$moment(this.$route.params.day, 'YYYY-MM-DD').add(this.addDays, 'day')
        },
        groupedByDatesTasks() {
            return this.$store.getters['todos/groupedByDates']
        },
        calendarDays: function() {
            let result = []
            let day = this.startPeriod.clone()

            do {
                result.push(day.clone())

                day.add(1, 'day')
            } while (day <= this.endPeriod)

            return result
        },
    },
    watch: {
        day() {
            this.reloadTasks()
        },
    },
    methods: {
        handleWheel: function () {
            if (this.scrollingEvent) {
                return
            }

            let wrapperBlock = document.getElementById('days')
            let calendarOffsetLeft = wrapperBlock.getBoundingClientRect().left * -1
            let calendarOffsetRight = wrapperBlock.getBoundingClientRect().right

            let todayBlock = document.querySelector('.current-day')
            let dayWidth = todayBlock.clientWidth // Ширина одного дня
            //
            // // let fromLeftEdge = (todayBlock.getBoundingClientRect().left - calendarOffsetTop) * -1
            let todayFromLeftEdge = todayBlock.getBoundingClientRect().left
            let todayFromRightEdge = todayBlock.getBoundingClientRect().right
            // let startOffsetTop = todayBlock.offsetLeft - wrapperBlock.offsetLeft

            document.getElementById('days').scrollLeft = todayFromLeftEdge

            return

            if (calendarOffsetLeft < 500) {
                // При добавлении пунктов в начало скролинг от верха не меняется, поэтому его надо увеличивать на высоту добавленных элементов
                this.subDays += 30

                this.scrollingEvent = setTimeout(() => {
                    let scroll = todayFromLeftEdge + (30 * dayWidth)

                    wrapperBlock.scrollTo({
                        // Текущий скролинг + добавленные месяцы
                        top: scroll,
                        behavior: 'auto'
                    })

                    this.scrollingEvent = false
                }, 100)
            }

            if (calendarOffsetRight < 500) {
                this.addDays += 30
            }
        },
        scrollToCurrent(fast) {
            let todayBlock = document.querySelector('.current-day')
            let dayWidth = todayBlock.clientWidth // Ширина одного дня
            let scroll = todayBlock.offsetLeft - window.innerWidth / 2 + dayWidth / 2

            document.getElementById('days').scrollLeft = scroll
            document.getElementById('days').scrollTo({
                left: scroll,
                behavior: fast ? 'auto' : 'smooth' // auto
            })
        },
        createChild: function() {
            let realToday = this.$moment()
            let moment = this.today.clone().hours(realToday.hours()).minutes(realToday.minutes())

            this.$store.dispatch('todos/createItem', {
                parentId: null,
                date: moment.format('YYYY-MM-DD'),
                message: '',
            })
        },
        reloadTasks() {
            this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
                .then(() => {
                    this.scrollToCurrent(true)
                })
        },
    },
    activated() {
        this.reloadTasks()
    },
}
</script>

<style lang="scss" scoped>
.month-header {
    cursor: pointer;

    &:hover {
        color: #415e7c;
        text-decoration: underline;
    }
}

.days-row {

    .days-wrapper {
        overflow-x: scroll;
        display: flex;

        .day {
            cursor: pointer;
            border: 1px solid black;
            position: relative;
            flex: none;
            width: 120px;

            .day__date {

            }

            &.weekend {
                background-color: red;
            }

            &.current-day {
                cursor: inherit;
                border-color: inherit;
            }

            &__tasksLabel {
                position: absolute;
                right: 5px;
                top: -3px;
                background-color: #22bd22;
                border-radius: 50%;
                width: 15px;
                height: 15px;
            }

            &:hover:not(.current-day) {
                border-color: red;
            }
        }
    }
}

.day-content {
    margin: 20px 0;
}

</style>