<template>
<div id="app">
    <div class="current-user">
        <div class="user--name">
            {{this.$store.state.user.current.phone}}
            (#{{this.$store.state.user.current.id}})
        </div>
        <div class="user--avatar"></div>
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
            <router-link v-if="$route.name === 'task-list'" :to="{name: 'calendarMonth'}">
                <img class="control--icon" :src="$store.getters['icons/Calendar']" alt="calendar" title="calendar">
                <span class="control--name">Calendar</span>
            </router-link>
            <router-link v-else :to="{name: 'task-list'}">
                <img class="control--icon" :src="$store.getters['icons/Checklist']" alt="Todos" title="Todos">
                <span class="control--name">Todos</span>
            </router-link>
        </li>

        <li class="control">
            <img class="control--icon" :src="$store.getters['icons/BellWhite']" alt="Todos" title="Todos">
            <span class="control--name">Notifications</span>
        </li>

        <li class="control">
            <router-link to="/user">
                <img v-if="$route.name === 'profile' || $route.name === 'registration'" class="control--icon" :src="$store.getters['icons/Profile']" alt="profile" title="profile">
                <img v-else class="control--icon" :src="$store.getters['icons/ProfileWhite']" alt="profile" title="profile">
                <span class="control--name">User</span>
            </router-link>
        </li>
    </ul>

    <router-view v-slot="{ Component }">
        <keep-alive>
            <component :is="Component" />
        </keep-alive>
    </router-view>

    <div class="controls-push-up">to close controls</div>

</div>
</template>

<script>
export default {
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

.controls-push-up {height: 50px}
.control-buttons {
    display: flex;
    list-style-type: none;
    justify-content: center;
    position: fixed;
    padding: 15px 0 15px 0;
    margin: 0;
    bottom: -1px;
    left: 0;
    width: 100%;
    z-index: 5;
    background-color: #fff;

    .control {
        display: block;
        width: 25%;

        .control--icon {
            max-width: 30px;
            max-height: 30px;
        }

        .control--name {
            display: none;
        }

        a {
            text-decoration: none;
        }
    }
}

.current-user {
    //display: flex;
    flex-direction: row-reverse;
    display: none;

    .user--avatar {
      margin-right: 20px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: #2c3e50;
    }

    .user--name {
      line-height: 50px;
    }
}

</style>
