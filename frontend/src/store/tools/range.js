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
            let startContainer = range.startContainer.parentNode.classList.contains('contenteditable-message') ? range.startContainer : range.startContainer.parentNode
            let endContainer   = range.endContainer.parentNode.classList.contains('contenteditable-message') ? range.endContainer : range.endContainer.parentNode
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
            let range = document.createRange()
            // range.setStartBefore(getters.nodes[getters.startContainerIndex])
            // range.setEndAfter(getters.nodes[getters.endContainerIndex])

            console.log(getters.nodes[getters.startContainerIndex].firstChild, `start container`)

            // range.setStart(getters.nodes[getters.startContainerIndex], getters.startPosition)
            // TODO не понимаю как выделить с середины
            range.setStart(getters.nodes[getters.startContainerIndex].firstChild, getters.startPosition)
            range.setEnd(getters.nodes[getters.endContainerIndex].firstChild, getters.endPosition)

            // range.startOffset = getters.startPosition
            // range.endOffset   = getters.endPosition

            window.getSelection().removeAllRanges()
            window.getSelection().addRange(range)
        },
    },
};

export default range;
