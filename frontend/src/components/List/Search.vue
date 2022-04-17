<template>
<div class="search">
    <img class="icon__search" @click="hidden = !hidden" :src="$store.getters['icons/Search']" alt="Search">

    <div :class="{'search-input': true, hidden: hidden}">
        <input v-model="phrase" type="text">
        <img @click="erase" class="icon__erase" :src="$store.getters['icons/Plus']" alt="Clear">
    </div>

    <div class="search-items" v-show="showSearchResults">
        <div class="search-items__item" v-for="task in foundTasks" :key="task.id">
            <a class="search-items__link" @click="move(task)">{{task.message.split('\n')[0]}}</a>
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
            hidden: true,
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
            this.hidden = true
        },
        move(task) {
            this.phrase = null
            this.hidden = true
            this.$router.push({name: 'task-item', params: {itemId: task.id}})
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
