<template>
<div class="component">
    <img class="icon__search" :src="$store.getters['icons/Search']" alt="Search">
    <input class="search-input" v-model="phrase" type="text">
    <img @click="erase" class="icon__erase" :src="$store.getters['icons/Plus']" alt="Clear">

    <div class="search-items" v-show="showSearchResults">
        <div class="search-items__item" v-for="task in foundTasks" :key="task.id">
            <a class="search-items__link" @click="$router.push({name: 'task-list', params: {parentId: task.id}})">{{task.message.split('\n')[0]}}</a>
        </div>
    </div>
</div>
</template>

<script>
export default {
    name: "Search",
    data() {
        return {
            phrase: null,
            foundTasks: [],
            showSearchResults: false,
        }
    },
    computed: {
        tasks() {
            return this.$store.getters['todos/all']
        },
    },
    watch: {
        phrase(value) {
            if (!value || value.length < 2) {
                this.foundTasks = []
                this.showSearchResults = false

                return
            }

            this.showSearchResults = true
            let regExp = new RegExp(value, 'i')
            this.foundTasks = this.tasks.filter(task => regExp.test(task.message))
        }
    },
    methods: {
        erase() {
            this.phrase = null
        },
    },
    mounted() {
        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('search-input')) {
                // Клик по инпуту
                this.showSearchResults = true
            } else if (event.target.classList.contains('search-items__link')) {
                // Клик по ссылке
                this.showSearchResults = false
            } else {
                // focus out
                this.showSearchResults = false
            }
        })
    },
}
</script>

<style lang="scss" scoped>
.component {
    display: flex;
    justify-content: center;
}

.icon__erase, .icon__search {
    max-width: 25px;
    max-height: 25px;
}

.icon__erase {
    transform: rotate(45deg);
}

.search-input {
    margin: 0 10px;
}

.search-items {
    width: 60%;
    left: 20%;
    position: absolute;
    background-color: #bcbcbc;
    text-align: left;
}

.search-items__item {
    border: 1px solid red;
    padding: 5px 10px;
}
.search-items__item:hover {
    background-color: rgba(33, 193, 33, 0.5);
    color: #ededed;
}
.search-items__link {
    cursor: pointer;
}
</style>
