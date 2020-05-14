<template>
    <div>
        <h1>Calendar</h1>
        <h3>{{monthsTitleEng[focus.getMonth()]}} {{focus.getFullYear()}}</h3>

        <table class="calendar" v-wheel="handleWheel">
            <thead>
                <tr>
                    <td>Monday</td>
                    <td>Tuesday</td>
                    <td>Wednesday</td>
                    <td>Thursday</td>
                    <td>Friday</td>
                    <td>Saturday</td>
                    <td>Sunday</td>
                </tr>
            </thead>

            <tbody>
                <tr :key="index" v-for="(week, index) of calendarDays">
                    <td :key="index" v-for="(day, index) of week">{{day}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        created() {
            // this.today = new Date()
            // this.today.setFullYear(2020)
            // this.today.setMonth(3)
        },
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
            startOfMonth() {
                return new Date(
                    this.focus.getFullYear(),
                    this.focus.getMonth(),
                    1
                )
            },
            endOfMonth: function() {
                let end = new Date(
                    this.startOfMonth.getFullYear(),
                    this.startOfMonth.getMonth() + 1
                )
                end.setDate(end.getDate() - 1)

                return end
            },
            calendarDays: function() {
                let days = []

                while (this.startOfMonth.getMonth() === this.focus.getMonth()) {
                    if (this.startOfMonth.getDay() !== this.firstDayOfWeek && days.length) {
                        // Продолжаем неделю
                        days[days.length - 1].push(this.startOfMonth.getDate())
                    } else if (this.startOfMonth.getDay() === this.firstDayOfWeek) {
                        // Начинаем новую неделя
                        days.push([this.startOfMonth.getDate()])
                    } else {
                        // Первая итерация

                        // Заполняем пустые места null`ом в начале недели(это дни предыдущего месяца)
                        let week = []
                        let dayNumber = this.startOfMonth.getDay() === 0 ? 7 : this.startOfMonth.getDay()

                        for (let i = dayNumber - 1; i > 0; i--) {
                            week.push(null)
                        }
                        week.push(this.startOfMonth.getDate())

                        // Начинаем новую неделя
                        days.push(week)
                    }

                    this.startOfMonth.setDate(this.startOfMonth.getDate() + 1)
                }

                return days
            },
        },
        methods: {
            handleWheel: function (evt) {
                if (evt.deltaY > 0) {
                    this.focus = new Date(this.focus.getFullYear(), this.focus.getMonth() + 1)
                } else {
                    this.focus = new Date(this.focus.getFullYear(), this.focus.getMonth() - 1)
                }
            }
        },
    }
</script>

<style lang="scss">
.calendar {
    thead {
        font-weight: bold;
    }
}
</style>
