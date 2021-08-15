<template>
<div>
    <h1>
        <span class="month-header" @click="$router.push({name: 'calendarMonth', params: {month: day.format('YYYY-MM')}})">{{monthsTitleEng[day.month()]}}</span>
        {{day.year()}}
    </h1>

    <div class="days-row">
        <div :class="{
                'day': true,
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

    <div class="day-content">
        <pre>{{`${day.year()}.${day.month()}.${day.date()}`}}</pre>
        <label class="tasksAmount" v-if="groupedByDatesTasks[`${day.year()}.${day.month()}.${day.date()}`]" v-for="task in groupedByDatesTasks[`${day.year()}.${day.month()}.${day.date()}`]">
            {{task}}
            <br>
            <a @click="$router.push({name: 'task-list', params: {parentId: task.id}})"></a>
            {{task.message}}
        </label>
    </div>

</div>
</template>

<script>
import moment from "moment"

export default {
    name: "Day",
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
        day() {
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
    methods: {

    },
    mounted() {
        this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
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
    display: flex;

    .day {
        border: 1px solid black;
        position: relative;
        cursor: pointer;

        .day__date {

        }

        &.weekend {
            background-color: red;
        }

        &__tasksLabel {
            position: absolute;
            right: -5px;
            top: -5px;
            background-color: #22bd22;
            border-radius: 50%;
            width: 15px;
            height: 15px;
        }

        &:hover {
            border-color: red;
        }
    }
}
</style>