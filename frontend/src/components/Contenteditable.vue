<template>
    <div>
        <div class="controls">
            <button @click="toggleH1">H1</button>
            <button @click="toggleCheckbox">Checkbox</button>
            <button @click="toggleOl">Ol</button>
        </div>

        <div
              class="task-message contenteditable-message placeholder"
              v-html="message"
              :id="'contenteditable-message'"
              :contenteditable="true"
              aria-placeholder="Текст записи..."
              @input="inputEvent"
          >
        </div>
    </div>
</template>

<script>
import moment from 'moment'
// import Parser from '@modules/parser'
import Parser from '../../modules/parser'

export default {
    name: 'contenteditable',
    props: {
        task: {
            type: Object,
            required: true,
        },
    },
    data: function () {
        return {
            waitingId: null,
            entities: null,
            parser: null,
            message: '', // Локальный текст, нужен чтобы фокус не слетал бы с div`а при изменении task.message
        }
    },
    watch: {
        'task.id': {
            immediate: true,
            handler() {
                this.setText()
            },
        },
        '$store.getters.todos/initialized': function() {
            this.setText()
        },
    },
    methods: {
        /**
         * Устанавливаем текст для редактирования
         */
        setText() {
            if (!this.parser) {
                return
            }

            let string = (this.task.updated && this.task.updated.message) ? this.task.updated.message : this.task.message
            this.message = this.parser.setText(string).toHtml()

            if (!string) {
                document.getElementById('contenteditable-message').innerHTML = ''
            }
        },
        inputEvent(value) {
            if (!value) {
                value = document.getElementById('contenteditable-message').childNodes
            } else {
                value = value.target.childNodes
            }

            if (this.waitingId) {
                window.clearTimeout(this.waitingId)
            }

            this.waitingId = setTimeout(() => {
                this.updateText(this.parser.toMDFromNodes(value))
            }, 700)
        },
        updateText(string) {
            // TODO: сохранять фокус
            // let range=window.getSelection().getRangeAt(0);
            // let sC=range.startContainer,eC=range.endContainer;
            let bE = document.getElementById('contenteditable-message')
            // function getNodeIndex(n){let i=0;while(n=n.previousSibling)i++;return i}
            //
            // let A=[];while(sC!==bE){A.push(getNodeIndex(sC));sC=sC.parentNode}
            // let B=[];while(eC!==bE){B.push(getNodeIndex(eC));eC=eC.parentNode}
            //
            // let rp = {"sC":A,"sO":range.startOffset,"eC":B,"eO":range.endOffset}

            this.$store.dispatch('todos/updateItem', {
              id: this.task.id,
              payload: {
                message: string,
              },
            }).then(() => {
              // TODO: восстанавливать сохранённый фокус
              bE.focus();
              // let sel=window.getSelection()
              // let range=sel.getRangeAt(0)
              // let x,C,sC=bE,eC=bE;
              //
              // C=rp.sC;x=C.length;while(x--)sC=sC.childNodes[C[x]];
              // C=rp.eC;x=C.length;while(x--)eC=eC.childNodes[C[x]];
              //
              // range.setStart(sC,rp.sO);
              // range.setEnd(eC,rp.eO);
              // sel.removeAllRanges();
              // sel.addRange(range)
            })
        },
        saveFocusRange() {

        },
        loadFocusRange() {

        },
        toggleH1() {
            this.$store.dispatch('range/init', '#contenteditable-message')

            this.$store.dispatch('range/save')

            let startContainerIndex = this.$store.getters['range/startContainerIndex']
            let endContainerIndex = this.$store.getters['range/endContainerIndex']
            let toText = false

            // Просматриваем все ноды и определяем что нужно делать
            for (let i = startContainerIndex; i <= endContainerIndex; i++) {
                // console.log(this.$store.getters['range/nodes'][i], i)
                if (this.$store.getters['range/nodes'][i] && this.$store.getters['range/nodes'][i].classList && this.$store.getters['range/nodes'][i].classList.contains('contenteditable-h')) {
                    toText = true
                }
            }

            // Каждая нода должна быть обработана
            for (let i = startContainerIndex; i <= endContainerIndex; i++) {
                if (toText
                    && this.$store.getters['range/nodes'][i]
                    && this.$store.getters['range/nodes'][i].classList
                    && this.$store.getters['range/nodes'][i].classList.contains('contenteditable-h')
                ) {
                    // Преобразуем в текст
                    let newNode = document.createElement('div')
                    newNode.innerHTML = this.$store.getters['range/nodes'][i].innerHTML + '<br>'.trim()

                    this.$store.getters['range/nodes'][i].after(newNode)
                    this.$store.getters['range/nodes'][i].parentNode.removeChild(this.$store.getters['range/nodes'][i])
                } else if (!toText) {
                    // Преобразуем в заголовок
                    let newNode = document.createElement('h1')
                    newNode.classList.add('contenteditable-h')

                    let text = this.$store.getters['range/nodes'][i].nodeValue || this.$store.getters['range/nodes'][i].innerText
                    newNode.textContent = text.trim()

                    this.$store.getters['range/nodes'][i].after(newNode)
                    this.$store.getters['range/nodes'][i].parentNode.removeChild(this.$store.getters['range/nodes'][i])
                }
            }

            this.$store.dispatch('range/load')

            this.inputEvent()
        },
        // TODO: toggleCheckbox ([] foobar)
        toggleCheckbox() {
            this.$store.dispatch('range/init', '#contenteditable-message')

            this.$store.dispatch('range/save')

            let startContainerIndex = this.$store.getters['range/startContainerIndex']
            let endContainerIndex = this.$store.getters['range/endContainerIndex']
            let toText = false

            // Просматриваем все ноды и определяем что нужно делать
            for (let i = startContainerIndex; i <= endContainerIndex; i++) {
                // console.log(this.$store.getters['range/nodes'][i], i)
                if (this.$store.getters['range/nodes'][i] && this.$store.getters['range/nodes'][i].classList && this.$store.getters['range/nodes'][i].classList.contains('contenteditable-checkbox')) {
                    toText = true
                }
            }

            // Каждая нода должна быть обработана
            for (let i = startContainerIndex; i <= endContainerIndex; i++) {
                // if (!this.$store.getters['range/nodes'][i]) {
                //   console.log(`contining to except error`)
                //   continue;
                // }

                if (toText && this.$store.getters['range/nodes'][i].classList && this.$store.getters['range/nodes'][i].classList.contains('contenteditable-checkbox')) {
                    // Преобразуем всё в текст
                    let newNode = document.createTextNode(this.$store.getters['range/nodes'][i].innerText.trim())
                    // newNode.innerHTML = this.$store.getters['range/nodes'][i].innerText + '<br>'.trim()

                    this.$store.getters['range/nodes'][i].after(newNode)
                    this.$store.getters['range/nodes'][i].parentNode.removeChild(this.$store.getters['range/nodes'][i])
                } else if (!toText) {
                    // Преобразуем всё в чекбоксы

                    if (!this.$store.getters['range/nodes'][i]) {
                        console.log(this.$store.getters['range/nodes'][i], `node ${i} in interval ${startContainerIndex} ${endContainerIndex}`)
                        continue
                    }

                    let text = this.$store.getters['range/nodes'][i].nodeValue || this.$store.getters['range/nodes'][i].innerText
                    text = text.trim()

                    if (text) {
                        let newNode = document.createElement('li')
                        newNode.classList.add('contenteditable-checkbox')

                        let inputNode = document.createElement('input')
                        inputNode.type = 'checkbox'

                        let textNode = document.createTextNode(text)

                        newNode.appendChild(inputNode)
                        newNode.appendChild(textNode)

                        this.$store.getters['range/nodes'][i].after(newNode)
                        this.$store.getters['range/nodes'][i].parentNode.removeChild(this.$store.getters['range/nodes'][i])
                    }
                }
            }

            if (toText) {
                this.$store.dispatch('range/load')
            } else {
                this.$store.dispatch('range/load', {
                  child : 1,
                })
            }

            this.inputEvent()
        },
        // TODO: toggleOl (1. foobar)
        toggleOl() {

        },
    },
    beforeMount() {
        this.parser = new Parser()
    },
    activated() {
        this.setText()
    },
}
</script>

<style lang="scss">
.contenteditable-h {
  margin: 0;
}

.contenteditable-checkbox {
  display: inline;
  list-style-type: none;
}

.node.green {
    color: #22bd22;
}

.task-message {
  border: 1px solid darkgrey;
  text-align: left;
  margin: 0 10px;
}

.word {
  border: 2px solid #bcbcbc;
  border-radius: 3px;
  padding-right: 5px;
}
</style>
