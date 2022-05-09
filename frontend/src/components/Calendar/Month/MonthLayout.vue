<template>
    <div>
        <h1>{{monthsTitleEng[today.month()]}} {{today.year()}}</h1>
        <button @click="scrollToCurrent()">scrollToCurrent</button>

        <div class="calendar-wrapper" @scroll="handleWheel">
            <div id="calendar">
                <div style="position: absolute; width: 100%;">
                    <table class="calendar">
                        <tr id="calendar--start"><td colspan="9999"></td></tr>

                        <month :month="month.month" :year="month.year" :key="index" v-for="(month, index) in prevCalendarDays"></month>
                        <month id="currentMonth" :month="calendarDays.month" :year="calendarDays.year"></month>
                        <month :month="month.month" :year="month.year" :key="index" v-for="(month, index) in nextCalendarDays"></month>

                        <tr id="calendar--end"><td colspan="9999"></td></tr>
                    </table>
                </div>
            </div>
        </div>


    </div>
</template>

<script>
import Month from "./Month"
import moment from "moment"

    export default {
        name: 'MonthLayout',
        components: {
            Month,
        },
        props: {
            month: {
                type: String, // Приходит router`ом из url
                required: false,
                default: () => {
                    return moment().format('YYYY-MM')
                },
            },
        },
        data: function () {
            return {
                addedMonths: 12,
                subMonths: 12,
                firstDayOfWeek: 1,
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
                scrollingEvent: null, // Указатель на setTimeout
                debugData: {
                    monthHeight: null,
                    fromTopEdge: null,
                    fromBottomEdge: null,
                },
            }
        },
        computed: {
            today: function() {
                let moment = this.$moment(this.month, 'YYYY-MM')

                return moment.isValid() ? moment : this.$moment()
            },
            calendarDays: function() {
                return {month: this.today.month(), year: this.today.year()}
            },
            prevCalendarDays: function() {
                let result = []
                let prevPeriod = this.today.clone()

                for (let i = 0; i < this.subMonths; i++) {
                    prevPeriod.subtract(1, 'month')

                    result.push({
                        month: parseInt(prevPeriod.month()),
                        year: parseInt(prevPeriod.year()),
                    })
                }

                return result.reverse()
            },
            nextCalendarDays: function() {
                let result = []
                let nextPeriod = this.today.clone()

                for (let i = 0; i < this.addedMonths; i++) {
                    nextPeriod.add(1, 'month')

                    result.push({
                        month: parseInt(nextPeriod.month()),
                        year: parseInt(nextPeriod.year()),
                    })
                }

                return result
            },
        },
        watch: {
            prevCalendarDays() {
                document.getElementById('currentMonth').scrollTo({
                    top: 450,
                    left: 0,
                    behavior: 'auto' // smooth
                })
            },
        },
        methods: {
            handleWheel: function (evt) {
                if (this.scrollingEvent) {
                    return
                }

                let calendarWrapper = document.querySelector('.calendar-wrapper')
                let calendarBlock = document.getElementById('calendar')
                let calendarSheet = document.querySelector('table.calendar')
                let calendarOffsetTop = calendarBlock.getBoundingClientRect().top
                let calendarOffsetBottom = calendarBlock.getBoundingClientRect().bottom
                let calendarStartBlock = document.getElementById('calendar--start')
                let calendarEndBlock = document.getElementById('calendar--end')

                let todayBlock = document.getElementById('currentMonth')
                let monthHeight = todayBlock.clientHeight // Высота одного месяца

                // let fromTopEdge = (calendarStartBlock.getBoundingClientRect().bottom - calendarOffsetTop) * -1
                let fromTopEdge = (calendarOffsetTop) * -1
                // let fromBottomEdge = calendarEndBlock.getBoundingClientRect().top - calendarOffsetBottom
                let fromBottomEdge = calendarEndBlock.getBoundingClientRect().top - calendarBlock.clientHeight
                let startOffsetTop = todayBlock.offsetTop - calendarBlock.offsetTop
                let minDistance = monthHeight * 3

                this.debugData.calendarSheet = Math.floor(calendarSheet.clientHeight)
                this.debugData.monthHeight = Math.floor(monthHeight)
                this.debugData.fromTopEdge = Math.floor(fromTopEdge)
                this.debugData.fromBottomEdge = Math.floor(fromBottomEdge)

                if (calendarSheet.clientHeight > 1000 && fromTopEdge < minDistance) {
                    // При добавлении пунктов в начало скролинг от верха не меняется, поэтому его надо увеличивать на высоту добавленных элементов
                    this.subMonths += 12

                    this.scrollingEvent = setTimeout(() => {
                        let top = fromTopEdge + (14 * monthHeight)

                        calendarWrapper.scrollTo({
                            // Текущий скролинг + добавленные месяцы
                            top: top,
                            behavior: 'auto'
                        })

                        this.scrollingEvent = false
                    }, 0)
                }

                if (fromBottomEdge < minDistance) {
                    this.addedMonths += 12
                }
            },
            scrollToCurrent(fast) {
                // let calendarWrapper = document.querySelector('.calendar-wrapper')
                // let calendarBlock = document.getElementById('calendar')
                let todayBlock = document.getElementById('currentMonth')
                let monthHeight = todayBlock.clientHeight // Высота одного месяца
                let monthsOnScreen = window.innerHeight / monthHeight // Кол-во влезающих в экран месяцев
                let scroll = todayBlock.offsetTop - ((monthsOnScreen - 1) * monthHeight / 5)

                document.querySelector('.calendar-wrapper').scrollTo({
                // document.getElementById('calendar').scrollTo({
                    top: scroll,
                    left: 0,
                    behavior: fast ? 'auto' : 'smooth'
                })
            },
        },
    }
</script>

<style lang="scss" scoped>
.calendar-wrapper {
    position: relative;
    overflow: scroll;
    height: 100%;

    #calendar {
        height: 1000px;
        position: relative;
        width: 100%;

        .calendar {
            width: 100%;
        }
    }
}
</style>
