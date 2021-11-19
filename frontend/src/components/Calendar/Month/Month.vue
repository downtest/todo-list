<template>
    <tbody>
    <tr><td colspan="7" class="calendar--title">{{monthsTitleEng[month]}} {{year}}</td></tr>

    <tr class="calendar--annotation">
        <td class="annotation--day" v-for="day in daysOfWeek">{{day}}</td>
    </tr>

    <tr :key="index" v-for="(week, index) in calendarDays">
        <td v-if="index === 0 && week.length < 7" :colspan="emptyDays(week.length)"></td>

        <td :key="index" v-for="(day, index) in week">
            <div v-if="day" :class="{
                    day: true,
                    today: day.date() === today.date() && day.month() === today.month() && day.year() === today.year(),
                    current_month: day.month() === today.month() && day.year() === today.year(),
                    focus_month: focus && day.month() === focus.month() && day.year() === focus.year(),
                    weekend: [6, 0].includes(day.day()),
                    with_tasks: groupedByDatesTasks[`${day.year()}.${day.month()}.${day.date()}`],
                 }"
                 :title="`${day}`"
                 @click="$router.push({name: 'calendarDay', params: {day: day.format('YYYY-MM-DD')}})"
            >
                <span class="day--date">{{day.date()}}</span>

                <label class="tasksAmount" v-if="groupedByDatesTasks[`${day.year()}.${day.month()}.${day.date()}`]">
                    {{groupedByDatesTasks[`${day.year()}.${day.month()}.${day.date()}`].length}}
                </label>
            </div>
        </td>
    </tr>
    </tbody>
</template>

<script>
import moment from "moment"

export default {
    name: "Month",
    props: {
        year: {
            type: Number,
            required: true,
        },
        month: {
            type: Number,
            required: true,
        },
        focus: {
            required: false,
            default: moment(),
        },
        firstDayOfWeek: {
            type: Number,
            required: false,
            default: 1,
        },
    },
    data() {
        return {
            today: this.$moment(),
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
            // daysTitleEng: [
            //     'Sunday',
            //     'Monday',
            //     'Tuesday',
            //     'Wednesday',
            //     'Thursday',
            //     'Friday',
            //     'Saturday',
            // ],
            daysTitleEng: [
                'Sun',
                'Mon',
                'Tue',
                'Wed',
                'Thur',
                'Fr',
                'Sat',
            ],
            // startPeriod: this.$moment([this.year, this.month]),
            // startPeriod: this.$moment([this.year, this.month]),
            // endPeriod: this.$moment([this.year , this.month]).endOf(`month`),
        }
    },
    computed: {
        startPeriod() {
            return this.$moment([this.year, this.month])
        },
        endPeriod() {
            return this.$moment([this.year, this.month]).endOf(`month`)
        },
        daysOfWeek() {
            let result = []
            let dayNumber = this.firstDayOfWeek

            for (let i = 0; i < 7; i++, dayNumber++) {
                if (dayNumber > 6) {
                    dayNumber = 0
                }

                result.push(this.daysTitleEng[dayNumber])
            }

            return result
        },
        groupedByDatesTasks() {
            return this.$store.getters['todos/groupedByDates']
        },
        calendarDays: function() {
            let result = []
            let day = this.startPeriod.clone()

            do {
                // День
                if (day.day() !== this.firstDayOfWeek && result.length) {
                    // Продолжаем неделю
                    result[result.length - 1].push(day.clone())
                // } else if (this.startPeriod.day() === this.firstDayOfWeek) {
                //     // Начинаем новую неделя
                //     result.push([dayObj])
                } else {
                    // Первая итерация, либо начинаем новую неделю
                    result.push([day.clone()])
                }

                day.add(1, 'day')
            } while (day <= this.endPeriod)

            return result
        },
    },
    methods: {
        emptyDays(filledDays) {
            // Заполняем пустые места null`ом в начале недели(это дни предыдущего месяца)
            return 7 - filledDays
        },
        getTasksByDay(year, month, day, hour) {
            return [{}, {}]
        },
    },
}
</script>

<style lang="scss" scoped>
.calendar {
    .calendar--title {
        font-weight: bold;
    }
    .calendar--annotation {
        font-size: .8em;

        .annotation--day {
            width: 10px;
        }
    }

    tbody {
        .day {
            color: #bcbcbc;
            cursor: pointer;
            padding: 2px;
            border-radius: 2px;

            &.today {
                background-color: #cd2533;

                .day--date {
                    color: #ffffff;
                }
            }

            &.current_month {
                color: #676767;
            }

            &.focus_month {
                color: #111;
            }

            &.weekend {
                color: #ef1111;
            }

            &.with_tasks {
                position: relative;

                .tasksAmount {
                    position: absolute;
                    z-index: 10;
                    width: 13px;
                    height: 13px;
                    line-height: 13px;
                    border-radius: 50%;
                    color: #ddd;
                    font-size: 10px;
                    background-color: #841048;
                }
            }

            &:hover {
                background-color: #2c3e50;
                color: #dddddd;
            }

            .day--date {
                background-color: inherit;
            }
        }
    }
}
</style>