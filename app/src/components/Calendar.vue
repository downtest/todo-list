<template>
    <div>
        <h1>Calendar</h1>

        <table class="calendar" v-wheel="handleWheel">
            <tbody :key="index" v-for="(month, index) in calendarDays">
                <tr><td colspan="7" class="calendar--title">{{monthsTitleEng[month[1][1]['month']]}} {{month[1][1]['year']}}</td></tr>

                <tr class="calendar--annotation">
                    <td>Monday</td>
                    <td>Tuesday</td>
                    <td>Wednesday</td>
                    <td>Thursday</td>
                    <td>Friday</td>
                    <td>Saturday</td>
                    <td>Sunday</td>
                </tr>

                <tr :key="index" v-for="(week, index) in month">
                    <td v-if="index === 0 && week.length < 7" :colspan="emptyDays(week.length)"></td>

                    <td :key="index" v-for="(day, index) in week">
                        <div v-if="day" :class="{
                            day: true,
                            current_month: day.monthId === `${today.getMonth()}-${today.getFullYear()}`,
                            focus_month: day.monthId === `${focus.getMonth()}-${focus.getFullYear()}`,
                            weekend: day.weekend,
                        }">{{day.day}}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                today: new Date(),
                focus: new Date(),
                firstDayOfWeek: 1,
                daysTitleEng: [
                    'Sunday',
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                ],
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
            }
        },
        computed: {
            startPeriod() {
                let date = new Date(
                    this.focus.getFullYear(),
                    this.focus.getMonth(),
                    1
                )
                date.setMonth(date.getMonth() - 1)

                return date
            },
            endPeriod: function() {
                let date = new Date(
                    this.startPeriod.getFullYear(),
                    this.startPeriod.getMonth()
                )
                date.setMonth(date.getMonth() + 3)
                date.setSeconds(date.getSeconds() - 1)

                return date
            },
            calendarDays: function() {
                let result = []

                while (this.startPeriod < this.endPeriod) {
                    let dayObj = {
                        day: this.startPeriod.getDate(),
                        month: this.startPeriod.getMonth(),
                        year: this.startPeriod.getFullYear(),
                        monthId: `${this.startPeriod.getMonth()}-${this.startPeriod.getFullYear()}`,
                        weekend: ([6, 0]).includes(this.startPeriod.getDay()) ? true : false,
                    }
                    let month = null

                    // Месяц
                    if (!result.length) {
                        // Первая итерация
                        result.push([])
                        month = result[0]
                    } else if (result[result.length - 1][0][0]['month'] !== dayObj.month) {
                        // Новый месяц
                        result.push([])
                        month = result[result.length - 1]
                    } else {
                        // Продолжаем месяц
                        month = result[result.length - 1]
                    }

                    // День
                    if (this.startPeriod.getDay() !== this.firstDayOfWeek && month.length) {
                        // Продолжаем неделю
                        month[month.length - 1].push(dayObj)
                    } else if (this.startPeriod.getDay() === this.firstDayOfWeek) {
                        // Начинаем новую неделя
                        month.push([dayObj])
                    } else {
                        // Первая итерация
                        let week = []

                        week.push(dayObj)
                        month.push(week)
                    }

                    this.startPeriod.setDate(this.startPeriod.getDate() + 1)
                }

                return result
            },
        },
        methods: {
            handleWheel: function (evt) {
                if (evt.deltaY > 40) {
                    this.focus = new Date(this.focus.getFullYear(), this.focus.getMonth() + 1)
                } else if (evt.deltaY < -40) {
                    this.focus = new Date(this.focus.getFullYear(), this.focus.getMonth() - 1)
                }
            },
            emptyDays(filledDays) {
                // Заполняем пустые места null`ом в начале недели(это дни предыдущего месяца)
                return 7 - filledDays
            },
        },
    }
</script>

<style lang="scss">
.calendar {
    .calendar--title {
        font-weight: bold;
    }
    .calendar--annotation {
        font-size: .8em;
    }

    tbody {
        .day {
            color: #bcbcbc;

            &.current_month {
                color: #676767;
            }

            &.focus_month {
                color: #111;
            }

            &.weekend {
                color: #ef1111;
            }
        }
    }
}
</style>
