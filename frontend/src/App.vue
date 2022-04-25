<template>
<div id="app">
    <div class="top"></div>

    <popup-notices></popup-notices>

    <div class="top-menu">
      <div class="current-user">
          <template v-if="$store.state.user.current.id">
              <router-link to="/user" class="user--avatar">
                <img :src="$store.getters['icons/ProfileWhite']" alt="profile" title="profile">
              </router-link>

              <div class="user--name" v-if="$store.state.user.current.id">
                {{$store.state.user.current.name || $store.state.user.current.email || $store.state.user.current.phone}}
              </div>
          </template>
          <template v-else>
              <router-link :to="{name: 'profile'}">Войти</router-link>
          </template>

          <search></search>
      </div>
    </div>

    <ul class="control-buttons">
        <li class="control">
            <router-link to="/collections">
                <img v-if="$route.name === 'collections'" class="control--icon" :src="$store.getters['icons/Briefcase']" alt="collections" title="collections">
                <img v-else class="control--icon" :src="$store.getters['icons/BriefcaseWhite']" alt="collections" title="collections">
                <span class="control--name">Calendar</span>
            </router-link>
        </li>

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
        console.log(this.$route, `this.$route`)
        console.log(this.$router, `this.$router`)

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
