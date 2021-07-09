<template>
    <div>
        <nested
            v-model="elements"
            :parentId="null"
        />

        <pre>{{elements}}</pre>
    </div>
</template>

<script>
import nested from "./components/List/Nested";

export default {
    components: {
        nested,
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
    data() {
        return {
            elements: [],
        }
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
