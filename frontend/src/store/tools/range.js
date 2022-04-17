const range = {
    namespaced: true,

    state: {
        selector: null,
        container: null,
        range: null,
        startContainerIndex: null,
        endContainerIndex: null,
        startPosition: null,
        endPosition: null,
    },
    mutations: {
        setSelector(state, value) {
            state.selector = value
        },
        setContainer(state, value) {
            state.container = value
        },
        setRange(state, value) {
            state.range = value
        },
        setStartContainerIndex(state, value) {
            state.startContainerIndex = value
        },
        setEndContainerIndex(state, value) {
            state.endContainerIndex = value
        },
        setStartPosition(state, value) {
            state.startPosition = value
        },
        setEndPosition(state, value) {
            state.endPosition = value
        },
    },

    getters: {
        selector(state) {
            return state.selector
        },
        container(state) {
            return state.container
        },
        nodes(state) {
            return Array.prototype.slice.call(state.container.childNodes)
        },
        nodeList(state) {
            return state.container.childNodes
        },
        range(state) {
            return state.range
        },
        startContainerIndex(state) {
            return state.startContainerIndex
        },
        endContainerIndex(state) {
            return state.endContainerIndex
        },
        startPosition(state) {
            return state.startPosition
        },
        endPosition(state) {
            return state.endPosition
        },
    },

    actions: {
        init({getters, commit}, selector) {
            commit('setSelector', selector)
            commit('setContainer', document.querySelector(getters.selector))
        },
        refreshNodes({getters, commit}) {
            commit('setContainer', document.querySelector(getters.selector))
        },
        save({getters, commit}) {
            let range = window.getSelection().getRangeAt(0)

            // let startContainer = range.startContainer.parentNode.classList.contains('contenteditable-message') ? range.startContainer : range.startContainer.parentNode
            let startContainer

            if (range.startContainer.classList && range.startContainer.classList.contains('contenteditable-message')) {
                startContainer = range.startContainer.childNodes[range.startOffset - 1]
                commit('setStartPosition', startContainer.length)
            } else {
                startContainer = range.startContainer
                commit('setStartPosition', range.startOffset)
            }

            // let startContainer = range.startContainer.classList && range.startContainer.classList.contains('contenteditable-message') ? range.startContainer.childNodes[range.startOffset] : range.startContainer
            // let startContainer = range.startContainer
            let endContainer   = range.endContainer.parentNode.classList.contains('contenteditable-message') ? range.endContainer : range.endContainer.parentNode

            // let endContainer   = range.endContainer.classList && range.endContainer.classList.contains('contenteditable-message') ? range.endContainer.childNodes[range.endOffset] : range.endContainer
            // let endContainer   = range.endContainer
            let nodes = getters.nodes
            let startContainerIndex = nodes.indexOf(startContainer)
            let endContainerIndex = nodes.indexOf(endContainer)

            commit('setRange', range)
            commit('setStartContainerIndex', startContainerIndex)
            commit('setEndContainerIndex', endContainerIndex)
            commit('setStartPosition', range.startOffset)
            commit('setEndPosition', range.endOffset)
        },
        load({getters, dispatch}, payload) {
            if (!payload) payload = {}

            let range = document.createRange()
            // range.setStartBefore(getters.nodes[getters.startContainerIndex])
            // range.setEndAfter(getters.nodes[getters.endContainerIndex])

            // range.setStart(getters.nodes[getters.startContainerIndex], getters.startPosition)

            if (payload.child) {
                range.setStart(getters.nodes[getters.startContainerIndex].childNodes[payload.child], getters.startPosition)
                range.setEnd(getters.nodes[getters.endContainerIndex].childNodes[payload.child], getters.endPosition)
            } else {
                if (['Text', 'Comment', 'CDataSection'].indexOf(typeof getters.nodes[getters.startContainerIndex]) !== -1) {
                    // 2ой аргумент должен быть кол-вом символов в ноде
                    range.setStart(getters.nodes[getters.startContainerIndex], getters.startPosition)
                } else {
                    // 2ой аргумент должен быть
                    range.setStart(getters.nodes[getters.startContainerIndex].childNodes[0], getters.endPosition)
                }

                if (['Text', 'Comment', 'CDataSection'].indexOf(typeof getters.nodes[getters.endContainerIndex]) !== -1) {
                    // 2ой аргумент должен быть кол-вом символов в ноде
                    range.setStart(getters.nodes[getters.endContainerIndex], getters.endPosition)
                } else {
                    // 2ой аргумент должен быть
                    range.setStart(getters.nodes[getters.endContainerIndex].childNodes[0], getters.endPosition)
                }
            }


            // range.startOffset = getters.startPosition
            // range.endOffset   = getters.endPosition

            window.getSelection().removeAllRanges()
            window.getSelection().addRange(range)
        },
    },
};

export default range;
