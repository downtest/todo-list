<template>
    <div>
        <div
            v-html="localContent"
            :id="'parentMessage'"
            :contenteditable="true"
            @input="inputEvent"
        >
        </div>

        {{waitingId}}
    </div>
</template>

<script>
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
                localContent: this.parseEntities(this.content),
                waitingId: null,
            }
        },
        methods: {
            inputEvent(value) {
                // alert('gsdsgdsdg')
                // return


                this.wrapEntities(value)

                if (this.waitingIsd) {
                    window.clearTimeout(this.waitingId)
                }

                this.waitingId = setTimeout(this.addClasses(document.activeElement.childNodes), 0, value)

                // this.addClasses(document.activeElement.childNodes)
            },
            wrapEntities(value) {
                // Цель метода: обернуть все слова в span-тэги
                // Ключевая сущность тут- пробел. Нам надо чтобы всё между пробелами было бы обёрнуто в отдельные тэги

                // let trimmedWords = value.target.innerText.trim().split(/\s+/)
                // let shouldBeNodesCnt = trimmedWords.length * 2 - 1
                //
                // if (shouldBeNodesCnt === value.target.childNodes.length) {
                //     // Новых пробелов не появилось- делать нечего
                //     return
                // }

                // console.log(value.target, `target value`)

                let textarea = value.target
                let childrenAmountBeforeParse = textarea.childNodes.length
                let focusRange = window.getSelection().getRangeAt(0)
                let startContainer = focusRange.startContainer
                var focusOffset = focusRange.startOffset
                var focusNode, nodeIndex

                if (startContainer.parentNode.id !== 'parentMessage') {
                    // Текст уже обёрнут в html тэг
                    focusNode = startContainer.parentNode
                } else {
                    // Текст без обёртки
                    focusNode = startContainer
                }
                focusNode = startContainer

                textarea.childNodes.forEach((item, index)=>{
                    if (focusNode === item || focusNode === item.firstChild) {
                        nodeIndex = index
                    }
                })

                console.log(`index ${nodeIndex} offset ${focusOffset}`)

                this.localContent = this.parseEntities(value.target.innerText)

                // Трюк, насколько я понимаю браузерный JS, такая
                setTimeout(() => {
                    let childrenAmountAfterParse = textarea.childNodes.length

                    if (childrenAmountAfterParse > childrenAmountBeforeParse) {
                        nodeIndex++
                        focusOffset-- // При изменении кол-ва нод, каретка сдвигается почему-то
                    } else if (childrenAmountAfterParse < childrenAmountBeforeParse) {
                        nodeIndex--
                        focusOffset++ // При изменении кол-ва нод, каретка сдвигается почему-то
                    }

                    var lengthAfterParse
                    if (textarea.childNodes[nodeIndex].hasChildNodes()) {
                        lengthAfterParse = textarea.childNodes[nodeIndex].firstChild.length
                    } else {
                        lengthAfterParse = textarea.childNodes[nodeIndex].length
                    }

                    if (focusOffset > lengthAfterParse) {
                        focusOffset = lengthAfterParse
                    }

                    console.log(textarea.childNodes[nodeIndex], `node ${nodeIndex}, offset ${focusOffset}`)

                    var childNode
                    if (textarea.childNodes[nodeIndex] && textarea.childNodes[nodeIndex].hasChildNodes()) {
                        childNode = textarea.childNodes[nodeIndex].firstChild
                    } else {
                        childNode = textarea.childNodes[nodeIndex]
                    }

                    let range = document.createRange()
                    range.setStart(childNode, focusOffset)
                    // range.setEnd(textarea.childNodes[nodeIndex].childNodes[0], 5)

                    let selection = window.getSelection()
                    selection.removeAllRanges()
                    selection.addRange(range)
                }, 0)
            },
            parseEntities(string) {
                let result = string
                    .replace(/([^\s]+)/gmiu, '<span class="node">$1</span>')
                    // .replace(/\d+,\d+/gmiu, '<span color="red">%1</span>')
                    .split('\n').join('<br>')

                return result
            },
            /**
             * Добавляет классы выделения цветом нужным span`ам(по сути нужным словам)
             * @param nodes
             */
            addClasses(nodes) {
                nodes.forEach(node => {
                    if (!node.hasChildNodes()) {
                        return
                    }
                    this.handleWordFunction(node)
                })
            }
        },
    }
</script>

<style lang="scss">
.node.green {
    color: #22bd22;
}
</style>
