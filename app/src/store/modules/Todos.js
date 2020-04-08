const todos = {
    namespaced: true,
    state: {
        items_old: {
            0: [
                {
                    id: 1,
                    name: "task 1",
                    message: "",
                },
                {
                    id: 3,
                    name: "task 3",
                    message: "",
                },
                {
                    id: 5,
                    name: "task 5",
                    message: "",
                }
            ],
            1: [
                {
                    id: 2,
                    name: "task 2",
                    message: "",
                }
            ],
            3: [
                {
                    id: 4,
                    name: "task 4",
                    message: "",
                }
            ]
        },
        items: {
            0: {
                tasks: [1,3,5],
            },
            1: {
                id: 1,
                name: "task 1",
                message: "",
                parent_id: 0,
                tasks: [2], // to save the order of children
            },
            2: {
                id: 2,
                name: "task 2",
                message: "",
                parent_id: 1,
                tasks: [],
            },
            3: {
                id: 3,
                name: "task 3",
                message: "",
                parent_id: 0,
                tasks: [4],
            },
            4: {
                id: 4,
                name: "task 4",
                message: "",
                parent_id: 3,
                tasks: [],
            },
            5: {
                id: 5,
                name: "task 5",
                message: "",
                parent_id: 0,
                tasks: [],
            },
        },
        logs: ['Init'],
    },
    getters: {
        firstLevel: state => {
            let result = []

            for (let id of state.items[0]['tasks']) {
                if (state.items[id]['parent_id'] === 0) {
                    result.push(state.items[id])
                }
            }

            console.log(result)

            return result
        },
        children: state => id => {
            let result = []

            if (!state.items[id] || !state.items[id].tasks) {
                return result
            }

            let children = state.items[id].tasks

            for (let childId of children) {
                if (state.items[childId]) {
                    result.push(state.items[childId])
                }
            }

            return result
        }
    },
    mutations: {
        log(state, message) {
            state.log.append(message)
        },
        updateChildren (state, {parentId, children}) {
            // `state` указывает на локальное состояние модуля
            state.items[parentId]['tasks'] = children
        },
        updateParent (state, {id, parentId}) {
            // `state` указывает на локальное состояние модуля
            state.items[id]['parent_id'] = parentId
        },
        replaceByFunction (state, {checker, payload, items}) {
            console.log(this, 'replaceByFunction mutation')
            if (!items) {
                items = state.items
            }

            items.map((node) => {
                if (checker(node)) {
                    console.log(`found #${node.id} ${node.name}`)

                    node = payload
                } else {
                    console.log(`not passed #${node.id} ${node.name}`)
                }

                if (node.tasks) {
                    // TODO: Рекурсивно вызвать себя же
                    // let result = this._mutations['todos/replaceByFunction']( {checker, payload, items: node.tasks})

                    // if (result) {
                    //     let childIndex = node.tasks.indexOf(result)
                    //     console.log(`found By index ${childIndex}`)
                    // }
                }
            })

            console.log('end')
        },
    },
    actions: {
        updateParent ({ commit }, {id, parentId, newIndex}) {
            console.log(`Acting updateParent on #${id} to ${parentId} to index ${newIndex}`)
            commit('updateParent', {id, parentId})
        },
        updateChildren ({ commit }, {parentId, children}) {
            commit('updateChildren', {parentId, children})
        },
        updateItem ({commit}, payload) {
            console.log(payload, 'payload in action update')
            commit('replaceByFunction', {checker: function(node) {
                console.log(`comparing ${node.id} and ${payload.id}`)
                return node.id === payload.id
            }, payload})
        },
    }
};

export default todos
