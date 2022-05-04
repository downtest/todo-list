<template>
<div id="app">
    <div class="top"></div>

    <popup-notices></popup-notices>

    <div class="top-menu">
        <search></search>
    </div>

    <ul class="control-buttons">
        <li class="control">
            <router-link :to="{name: 'calendarMonth'}">
                <img class="control--icon" :src="$store.getters['icons/Calendar']" alt="calendar" title="calendar">
                <span class="control--name">Calendar</span>
            </router-link>
        </li>

        <li class="control">
          <router-link :to="{name: 'task-list'}">
            <img class="control--icon" :src="$store.getters['icons/Checklist']" alt="Todos" title="Todos">
            <span class="control--name">Todos</span>
          </router-link>
        </li>

        <li class="control">
          <router-link :to="{name: 'profile'}">
            <img v-if="$route.name === 'profile'" class="control--icon" :src="$store.getters['icons/Profile']" alt="profile" title="profile">
            <img v-else class="control--icon" :src="$store.getters['icons/ProfileWhite']" alt="profile" title="profile">
            <span class="control--name">Profile</span>
          </router-link>
        </li>
    </ul>

    <router-view v-slot="{ Component }">
        <keep-alive>
            <component :is="Component" />
        </keep-alive>
    </router-view>

    <div class="bottom"></div>

</div>
</template>

<script>
import search from "./components/List/Search"
import PopupNotices from "./components/PopupNotices"

export default {
    components: {
        search,
        PopupNotices,
    },
    created() {
        // console.log(this.$route, `this.$route`)
        // console.log(this.$router, `this.$router`)

        if (this.$store.getters['user/current']['id']) {
          this.$store.dispatch('collections/load').then((data) => {
            this.$store.dispatch('todos/load', {
              clientId: data.user ? data.user.id : null,
              collectionId: data.currentCollection ? data.currentCollection : null,
            })
          })
        }

        this.$store.dispatch('user/current')
        // document.title = this.$route.matched[0]['meta']['title'];
    },
    props: ['title'],
    computed: {
        count: function () {
            return this.$store.state.counter.count;
        },
    },
}
</script>

<style lang="scss">
@import './scss/global.scss';

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}
</style>
