<template>
    <div>
        <div class="controls">
            <button @click="inspectElement">?</button>
            <button @click="toggleH1">H1</button>
        </div>
        <div
            class="task-message contenteditable-message"
            v-html="content"
            :id="'contenteditable-message'"
            :contenteditable="true"
            @input="inputEvent"
        >
        </div>
    </div>
</template>

<script>
import moment from 'moment'
// import Parser from '@todo-markdown/parser'
import Parser from '../../todo-markdown/parser'

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
        }
    },
    computed: {
        content() {
            return this.parser ? this.parser.toHtml() : ''
        },
    },
    methods: {
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
            let range=window.getSelection().getRangeAt(0);
            let sC=range.startContainer,eC=range.endContainer;
            let bE = document.getElementById('contenteditable-message')
            function getNodeIndex(n){let i=0;while(n=n.previousSibling)i++;return i}

            let A=[];while(sC!==bE){A.push(getNodeIndex(sC));sC=sC.parentNode}
            let B=[];while(eC!==bE){B.push(getNodeIndex(eC));eC=eC.parentNode}

            let rp = {"sC":A,"sO":range.startOffset,"eC":B,"eO":range.endOffset}

            this.$store.dispatch('todos/updateItem', {
              id: this.task.id,
              payload: {
                message: string,
              },
            }).then(() => {
              // TODO: восстанавливать сохранённый фокус
              bE.focus();
              let sel=window.getSelection()
              let range=sel.getRangeAt(0)
              let x,C,sC=bE,eC=bE;

              C=rp.sC;x=C.length;while(x--)sC=sC.childNodes[C[x]];
              C=rp.eC;x=C.length;while(x--)eC=eC.childNodes[C[x]];

              range.setStart(sC,rp.sO);
              range.setEnd(eC,rp.eO);
              sel.removeAllRanges();
              sel.addRange(range)
            })
        },
        inspectElement() {
            let range=window.getSelection().getRangeAt(0);
            let sC=range.startContainer,eC=range.endContainer;
            let bE = document.getElementById('contenteditable-message')

          console.log(sC.nodeName, `node`)
          console.log(sC.parentNode, `parent ${sC.parentNode.nodeName}`)
        },
        saveFocusRange() {

        },
        loadFocusRange() {

        },
        toggleH1() {
            this.$store.dispatch('range/init', '#contenteditable-message')

            this.$store.dispatch('range/save')

            let bE = this.$store.getters['range/container']
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
                if (toText && this.$store.getters['range/nodes'][i].classList && this.$store.getters['range/nodes'][i].classList.contains('contenteditable-h')) {
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
    },
    mounted() {
        if (this.task && this.task.message) {
            this.parser = new Parser(this.task.message)
        }
    },
}
</script>

<style lang="scss">
.contenteditable-h {
  margin: 0;
}

.node.green {
    color: #22bd22;
}

.task-message {
  border: 1px solid darkgrey;
  text-align: left;
}

.word {
  border: 2px solid #bcbcbc;
  border-radius: 3px;
  padding-right: 5px;
}
</style>
