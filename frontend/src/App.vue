 <template>
<div id="app">
    <div class="top"></div>

    <popup-notices></popup-notices>

    <div class="top-menu">
        <search></search>
        <push-notifications></push-notifications>
    </div>

    <ul class="control-buttons">
        <li class="control">
            <router-link :class="{'control--link': true, 'control--link__active': $route.name === 'calendarMonth'}" :to="{name: 'calendarMonth'}">
                <img v-if="$route.name === 'calendarMonth'" class="link--icon" :src="$store.getters['icons/CalendarWhite']" alt="calendar">
                <img v-else class="link--icon" :src="$store.getters['icons/Calendar']" alt="calendar">
                <span class="link--name">Calendar</span>
            </router-link>
        </li>

        <li class="control">
          <router-link :class="{'control--link': true, 'control--link__active': $route.name === 'task-list'}" :to="{name: 'task-list'}">
            <img v-if="$route.name === 'task-list'" class="link--icon" :src="$store.getters['icons/ChecklistWhite']" alt="Todos">
            <img v-else class="link--icon" :src="$store.getters['icons/Checklist']" alt="Todos">
            <span class="link--name">Todos</span>
          </router-link>
        </li>

        <li class="control">
          <router-link :class="{'control--link': true, 'control--link__active': $route.name === 'profile'}" :to="{name: 'profile'}">
            <img v-if="$route.name === 'profile'" class="link--icon" :src="$store.getters['icons/ProfileWhite']" alt="profile">
            <img v-else class="link--icon" :src="$store.getters['icons/Profile']" alt="profile">
            <span class="link--name">Profile</span>
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
import PushNotifications from "./components/PushNotifications"

export default {
    components: {
        search,
        PopupNotices,
        PushNotifications,
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
