<template>
    <div>
        <div
            v-html="content.replaceAll('\n', '<br>')"
            :id="'parentMessage'"
            :contenteditable="true"
            @input="inputEvent"
        >
        </div>

        <textarea>{{content}}</textarea>
    </div>
</template>

<script>
import moment from 'moment'

export default {
    name: 'contenteditable',
    props: {
        content: '',
        handleWordFunction: {
            type: Function,
            required: false,
            default() {}
        }
    },
    data: function () {
        return {
            waitingId: null,
            entities: null,
        }
    },
    methods: {
        inputEvent(value) {
            if (this.waitingId) {
                window.clearTimeout(this.waitingId)
            }

            this.waitingId = setTimeout(() => {
                this.parseEntities(value.target.innerText)
                this.updateText(value.target.innerText)
            }, 700)
        },
        /**
         * Цель функции: собрать все сущности из введённого пользователем текста
         * @param string
         */
        parseEntities(string) {
            let rules = [
                {
                    regexp: /Послезавтра/i,
                    handler: matches => {
                        this.$emit('setTime', moment().add(2, 'days').hours(0).minutes(0).format('YYYY-MM-DTHH:mm'))
                    },
                },
                {
                    regexp: /Завтра/i,
                    handler: matches => {
                        this.$emit('setTime', moment().add(1, 'days').hours(0).minutes(0).format('YYYY-MM-DTHH:mm'))
                    },
                },
                {
                    regexp: /Сегодня/i,
                    handler: matches => {
                        this.$emit('setTime', moment().hours(0).minutes(0).format('YYYY-MM-DTHH:mm'))
                    },
                },
                {
                    regexp: /Послезавтра в (\d*)/i,
                    handler: matches => {
                        let hour = parseInt(matches[1])
                        hour = hour > 0 && hour < 24 ? hour : 0

                        this.$emit('setTime', moment().add(2, 'days').hours(hour).minutes(0).format('YYYY-MM-DTHH:mm'))
                    },
                },
                {
                    regexp: /\sЗавтра в (\d*)/i,
                    handler: matches => {
                        let hour = parseInt(matches[1])
                        hour = hour > 0 && hour < 24 ? hour : 0

                        this.$emit('setTime', moment().add(1, 'days').hours(hour).minutes(0).format('YYYY-MM-DTHH:mm'))
                    },
                },
                {
                    regexp: /Сегодня в (\d*)/i,
                    handler: matches => {
                        let hour = parseInt(matches[1])
                        hour = hour > 0 && hour < 24 ? hour : 0

                        this.$emit('setTime', moment().hours(hour).minutes(0).format('YYYY-MM-DTHH:mm'))
                    },
                },
                // {
                //     regexp: /\d\d час/gi,
                //     handler: matches => {
                //         console.log(matches, `matches in \\d\\d handler`)
                //     },
                // },
                {
                    regexp: /в[o]? (пн|вт|ср|чт|пт|сб|вс|понедельник|вторник|среду|четверг|пятницу|субботу|воскресенье)/i,
                    handler: matches => {
                        console.log(matches[1], `last matches`)
                        let desiredMoment = moment()
                        let desiredDay
                        switch (matches[1]) {
                            case 'пн':
                            case 'понедельник':
                                desiredDay = 1
                                break
                            case 'вт':
                            case 'вторник':
                                desiredDay = 2
                                break
                            case 'ср':
                            case 'среду':
                                desiredDay = 3
                                break
                            case 'чт':
                            case 'четверг':
                                desiredDay = 4
                                break
                            case 'пт':
                            case 'пятницу':
                                desiredDay = 5
                                break
                            case 'сб':
                            case 'субботу':
                                desiredDay = 6
                                break
                            case 'вс':
                            case 'воскресенье':
                                desiredDay = 7
                                break
                        }


                        if (desiredDay > desiredMoment.isoWeekday()) {
                            // Устанавливаемый день после текущего дня идёт
                            desiredMoment.add(desiredDay - desiredMoment.isoWeekday(), 'day')
                        } else {
                            // Устанавливаемый до текущего дня(поставим следующую неделю)
                            desiredMoment.add(desiredMoment.isoWeekday() - desiredDay + 7, 'day')
                        }

                        console.log(desiredMoment, 'pon ')
                        this.$emit('setTime', desiredMoment.format('YYYY-MM-DTHH:mm'))
                    },
                },
            ]

            rules.forEach(rule => {
                let matches = string.match(rule.regexp)

                if (!matches) {
                    return
                }

                rule.handler(matches)
            })

            // this.entities = string.match(/Послезавтра в (\d*)|Завтра|\d\d/gi)
        },
        updateText(string) {
            console.log(string, `request on Server to update text`)
        },
    },
}
</script>

<style lang="scss">
.node.green {
    color: #22bd22;
}
</style>
