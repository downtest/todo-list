<template>
  <div class="list">
    <div class="justify-content-between row">

      <breadcrumb v-if="itemId" :id="itemId"></breadcrumb>

      <div v-if="this.$store.getters['todos/getChanges'].length" @click="this.$store.dispatch('todos/save')">save ({{$store.getters['todos/getChanges'].length}})</div>

      <div v-if="item">
        <div>
          <contenteditable-component
              :task="item"
              @update="updateMessage"
              @setTime="setTimeHandler"
          ></contenteditable-component>
        </div>

        <label>
          <input class="parent--input" type="date" v-model="itemDate">
        </label>

        <label>
          <input class="parent--input" type="time" v-model="itemTime">
        </label>

        <router-link v-if="itemDate" :to="{name: 'calendarDay', params: {day: itemDate}}">
          Посмотреть в календаре
        </router-link>

        <h3>Лейблы</h3>

        <labels :task="item"></labels>
      </div>

    </div>

    <div class="bottom"></div>
  </div>
</template>

<script>
import nested from "../List/Nested";
import contenteditableComponent from "../Contenteditable";
import Breadcrumb from "../List/Breadcrumb";
import Labels from "./Labels";

export default {
  components: {
    nested,
    Breadcrumb,
    contenteditableComponent,
    Labels,
  },
  props: {
    itemId: {
      required: true,
      type: String,
      default: ''
    },
  },
  data() {
    return {
      itemDatetime: false,
    }
  },
  computed: {
    // itemId() {
    //   if (!this.$route || !this.$route.params.itemId) {
    //     return null
    //   }
    //
    //   return this.$route.params.itemId
    // },
    item() {
      if (this.itemId) {
        return this.$store.getters['todos/getById'](this.itemId);
      } else {
        return null;
      }
    },
    itemMessage: {
      get() {
        if (this.item) {
          return this.item.message;
        } else {
          return null;
        }
      },
      set(message) {
        message = message.replace(/<\/?[^>]+(>|$)/g, "")
        this.$store.dispatch('todos/updateItem', {
          id: this.item.id,
          payload: {
            message: message,
          },
        })
      },
    },
    itemDate: {
      get() {
        if (this.item) {
          return this.item.date;
        } else {
          return null;
        }
      },
      set(date) {
        this.$store.dispatch('todos/updateItem', {
          id: this.item.id,
          payload: {
            date: date,
          },
        })
      },
    },
    itemTime: {
      get() {
        if (this.item) {
          return this.item.time;
        } else {
          return null;
        }
      },
      set(time) {
        this.$store.dispatch('todos/updateItem', {
          id: this.item.id,
          payload: {
            time: time,
          },
        })
      },
    },
    elements: {
      get() {
        if (this.itemId) {
          return this.$store.getters['todos/children'](this.itemId).sort((a, b) => a.index - b.index)
        } else {
          return this.$store.getters['todos/firstLevel'].sort((a, b) => a.index - b.index)
        }
      },
      set(payload) {
        // this.$store.dispatch("todos/updateChildren", {
        //     itemId: this.itemId ? this.itemId  : 0,
        //     children: payload.map(child => child.id)
        // });
      },
    },
  },
  methods: {
    createChild () {
      this.$store.dispatch('todos/createItem', {
        itemId: this.itemId,
        message: '',
      })
    },
    setTimeHandler(value) {
      this.itemDatetime = value
    },
    updateMessage(value) {
      console.log(value, `updateMessage in Task component`)
      this.itemMessage = value
    },
  },
  activated() {
    console.log(this.itemId, `item ID`)

    this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
  },
}
</script>

<style lang="scss">
@import "../../scss/variables";

.bottom {
  height: 40px;
}

.item {

  &--input {
    width: 100%;
    margin-bottom: 10px;
  }
}

.btn--icon {
  max-width: 25px;
  max-height: 25px;
  margin: auto;
}

.btn__add {
  background-color: $colorMain;
  width: 40px;
  height: 40px;
  align-items: center;
  display: flex;
  border-radius: 50%;
  cursor: pointer;
  margin-bottom: 50px;
  bottom: 0;
  position: fixed;
  left: 50%;
  margin-left: -20px;
  z-index: 6;
}
</style>
