<template>
  <div id="app">
    <div>Count: {{count}}</div>

    <div class="user">
      <div class="user--name">
        {{this.$store.state.user.name}}
        (#{{this.$store.state.user.id}})
      </div>
      <div class="user--avatar"></div>
    </div>

    <ul>
      <li><router-link to="/" exact>Main</router-link></li>
      <li><router-link to="/user">User</router-link></li>
      <li><router-link to="/counter">Counter</router-link></li>
      <li><router-link to="/list">List</router-link></li>
      <li><router-link to="/404">404</router-link></li>
    </ul>

    <router-view class="view"></router-view>
  </div>
</template>

<script>
export default {
  created() {
    document.title = this.$route.matched[0]['meta']['title'];
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
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
  ul {
    list-style-type: none;
    display: flex;
    justify-content: center;

    li {
      display: inline-block;
      margin-right: 20px;

      a {
        color: #2c3e50;
        text-decoration: none;

        &.link__active {
          color: green;
        }
      }
    }
  }

  .user {
    display: flex;
    flex-direction: row-reverse;

    &--avatar {
      margin-right: 20px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: #2c3e50;
    }

    &--name {
      line-height: 50px;
    }
  }

</style>
