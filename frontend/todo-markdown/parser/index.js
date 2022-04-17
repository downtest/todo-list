import moment from "moment";

export default class Parser {
    constructor(text) {
        this.setText(text)

        this.state = {
            listStarted: false,
        }

        this.globalRules = [
            // {
            //     regexp: /Послезавтра/i,
            //     handler: (matches, string) => {
            //         this.$emit('setTime', moment().add(2, 'days').hours(0).minutes(0).format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /Завтра/i,
            //     handler: (matches, string) => {
            //         this.$emit('setTime', moment().add(1, 'days').hours(0).minutes(0).format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /Сегодня/i,
            //     handler: (matches, string) => {
            //         this.$emit('setTime', moment().hours(0).minutes(0).format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /Послезавтра в (\d*)/i,
            //     handler: (matches, string) => {
            //         let hour = parseInt(matches[1])
            //         hour = hour > 0 && hour < 24 ? hour : 0
            //
            //         this.$emit('setTime', moment().add(2, 'days').hours(hour).minutes(0).format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /\sЗавтра в (\d*)/i,
            //     handler: (matches, string) => {
            //         let hour = parseInt(matches[1])
            //         hour = hour > 0 && hour < 24 ? hour : 0
            //
            //         this.$emit('setTime', moment().add(1, 'days').hours(hour).minutes(0).format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /Сегодня в (\d*)/i,
            //     handler: (matches, string) => {
            //         let hour = parseInt(matches[1])
            //         hour = hour > 0 && hour < 24 ? hour : 0
            //
            //         this.$emit('setTime', moment().hours(hour).minutes(0).format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /\d\d час/gi,
            //     handler: matches => {
            //         console.log(matches, `matches in \\d\\d handler`)
            //     },
            // },
            // {
            //     regexp: /в[o]? (пн|вт|ср|чт|пт|сб|вс|понедельник|вторник|среду|четверг|пятницу|субботу|воскресенье)/i,
            //     handler: (matches, string) => {
            //         console.log(matches[1], `last matches`)
            //         let desiredMoment = moment()
            //         let desiredDay
            //         switch (matches[1]) {
            //             case 'пн':
            //             case 'понедельник':
            //                 desiredDay = 1
            //                 break
            //             case 'вт':
            //             case 'вторник':
            //                 desiredDay = 2
            //                 break
            //             case 'ср':
            //             case 'среду':
            //                 desiredDay = 3
            //                 break
            //             case 'чт':
            //             case 'четверг':
            //                 desiredDay = 4
            //                 break
            //             case 'пт':
            //             case 'пятницу':
            //                 desiredDay = 5
            //                 break
            //             case 'сб':
            //             case 'субботу':
            //                 desiredDay = 6
            //                 break
            //             case 'вс':
            //             case 'воскресенье':
            //                 desiredDay = 7
            //                 break
            //         }
            //
            //
            //         if (desiredDay > desiredMoment.isoWeekday()) {
            //             // Устанавливаемый день после текущего дня идёт
            //             desiredMoment.add(desiredDay - desiredMoment.isoWeekday(), 'day')
            //         } else {
            //             // Устанавливаемый до текущего дня(поставим следующую неделю)
            //             desiredMoment.add(desiredMoment.isoWeekday() - desiredDay + 7, 'day')
            //         }
            //
            //         console.log(desiredMoment, 'pon ')
            //         // this.$emit('setTime', desiredMoment.format('YYYY-MM-DTHH:mm'))
            //
            //         return string
            //     },
            // },
            // {
            //     regexp: /(\S*)\s/gim,
            //     handler: (matches, string) => {
            //         // Каждое слово оборачиваем в span
            //
            //         return string
            //
            //         let items = string.split(/\s*/)
            //
            //         console.log(matches, string)
            //
            //         return matches.map(word => `<span class="word">${word}</span>`).join(' ')
            //     },
            // },
            // {
            //     regexp: /.*/i,
            //     handler: (matches, string) => {
            //         // Заменяем переносы на br`ы
            //         return string
            //             // .replaceAll('&nbsp;', ' ')
            //             // .replaceAll(' ', '&nbsp;')
            //             // .replaceAll('\n', '<br>')
            //             // .replaceAll(/\s/g, ' ')
            //     },
            // },
        ]

        /**
         * Выполняются для каждой строки при парсинге из памяти в html
         */
        this.lineRules = [
            {
                // 1. Список
                name: 'Список (начинается с "1. ")',
                regexp: /^\d+\.\s+(.+)/i,
                handler(matches, line, prevLine, nextLine) {
                    let result = ''

                    if (!prevLine || !this.regexp.test(prevLine)) {
                        // Первый пункт списка
                        result += '<ol class="contenteditable-ol">'
                    }

                    result += `<li>${matches[1]}</li>`

                    if (!nextLine || !this.regexp.test(nextLine)) {
                        // Последний пункт списка
                        result += '</ol>'
                    }

                    return result
                },
            },
            {
                // ## Заголовок
                name: 'Заголовок (начинается с #)',
                regexp: /^(#*)\s(.+)/i,
                handler(matches, line, prevLine, nextLine) {
                    return `<h${matches[1].length} class="contenteditable-h">${matches[2]}</h${matches[1].length}>`
                },
            },
            {
                // [] Checkbox
                name: 'Checkbox (начинается с [])',
                regexp: /^\[\]\s(.+)/i,
                handler(matches, line, prevLine, nextLine) {
                    return `<input type="checkbox" class="contenteditable-checkbox"> ${matches[1]}<br>`
                },
            },
            {
                // Если строка начинается с буквы или цифры, то это обычный текст, скорее всего и нужно поставить перенос строки
                name: 'Обычный текст (начинается с буквы или цифры)',
                regexp: /(^\s*$|^[^<])/i,
                handler(matches, line, prevLine, nextLine) {
                    // На последней строке перенос не нужен, поэтому проверяем, что это не последняя строка
                    if (typeof nextLine === 'string' || nextLine instanceof String) {
                        return line + '<br>'
                    }

                    return line
                },
            },
        ]
    }
    setText(text) {
        this.text = text

        return this
    }
    toHtml() {
        let string = this.text

        // Общая обработка для всего текста
        this.globalRules.forEach(rule => {
            let matches = string.match(rule.regexp)

            if (matches) {
                string = rule.handler(matches, string, this.text)
            }
        })

        // Построчная обработка
        let linesArr = string.split('\n')

        linesArr = linesArr.map((line, index) => {
            this.lineRules.forEach(rule => {
                let matches = line.match(rule.regexp)

                if (matches) {
                    // Шаблон найден в строке, строку надо обработать
                    let prevLine = (index > 0) ? linesArr[index - 1] : null
                    let nextLine = (linesArr.length > (index + 1)) ? linesArr[index + 1] : null

                    line = rule.handler(matches, line, prevLine, nextLine)
                }
            })

            return line
        })

        return linesArr.join('')
    }
    toMD(html) {
        if (!html) {
            console.error(`Не передан параметр html в метод toMD()`)
            return
        }

        console.log(html, `html параметр`)
    }
    toMDFromNodes(nodes) {
        if (!nodes) {
            console.error(`Не передан параметр nodes в метод toMDFromNodes()`)
            return
        }

        let result = ''

        nodes.forEach((node, index) => {
            let prevNode = nodes[index - 1] || null
            let nextNode = nodes[index + 1] || null

            switch (node.nodeName) {
                case '#text':
                    // result += node.nodeValue + '\n'
                    result += node.nodeValue

                    // if (nextNode && (nextNode.nodeName === 'DIV' || nextNode.nodeName === '#text')) {
                    if (nextNode && (nextNode.nodeName === 'DIV')) {
                        result += '\n'
                    }

                    break;
                case 'DIV':
                    // result += this.toMDFromNodes(node.childNodes) + '\n'
                    result += this.toMDFromNodes(node.childNodes)
                    break;
                case 'BR':
                    result += '\n'
                    break;
                case 'H1':
                    result += `# ${node.innerText}\n`
                    break;
                case 'H2':
                    result += `## ${node.innerText}\n`
                    break;
                case 'H3':
                    result += `### ${node.innerText}\n`
                    break;
                case 'H4':
                    result += `#### ${node.innerText}\n`
                    break;
                case 'H5':
                    result += `##### ${node.innerText}\n`
                    break;
                case 'H6':
                    result += `###### ${node.innerText}\n`
                    break;
                case 'INPUT':
                    if (node.className === 'contenteditable-checkbox') {
                        result += `[]`
                    } else {
                        result += node.innerHTML + '\n'
                    }
                    break;
                case 'OL':
                    // result += '\n'

                    node.childNodes.forEach(li => {
                        result +=  '1. ' + li.outerText + '\n'
                    })
                    break;
                default:
                    result += this.toMDFromNodes(node.childNodes)
                    break;
            }
        })

        return result
    }
}
