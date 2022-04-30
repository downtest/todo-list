<template>
  <div class="list">
    <div class="justify-content-between row">

      <breadcrumb v-if="itemId" :id="itemId"></breadcrumb>

      <template v-if="this.$store.getters['todos/getTaskChanges'](itemId)">
        <button @click="this.$store.dispatch('todos/resetChanges', itemId)">Сбросить изменения</button>
        <button @click="this.$store.dispatch('todos/save')">Сохранить</button>
      </template>

      <div v-if="item">
        <div>
          <contenteditable-component
              :task="item"
              @update="updateMessage"
              @setTime="setTimeHandler"
          ></contenteditable-component>
        </div>

        <label class="label">
          <div class="label__name">Дата</div>
          <input class="label__input" type="date" v-model="itemDate">
        </label>

        <label class="label">
          <div class="label__name">Время</div>
          <input class="label__input" type="time" v-model="itemTime">
        </label>

        <router-link v-if="itemDate" :to="{name: 'calendarDay', params: {day: itemDate}}">
          Посмотреть в календаре
        </router-link>

        <h3>Лейблы</h3>

        <labels :task="item"></labels>
      </div>

      <nested
          v-model="children"
          :parentId="itemId"
      />

      <div class="btn_add" @click="createChild">
        <img class="btn__icon" :src="$store.getters['icons/PlusWhite']" alt="add" title="Add task">
      </div>

    </div>
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
        if (this.item && this.item.updated) {
          return this.item.updated.date
        } else if (this.item) {
          return this.item.date
        } else {
          return null
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
        if (this.item && this.item.updated) {
          return this.item.updated.time
        } else if (this.item) {
          return this.item.time
        } else {
          return null
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
    children: {
      get() {
        return this.$store.getters['todos/children'](this.itemId)
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
        parentId: this.itemId,
        message: '',
      }).then((task) => {
        this.$router.push({name: 'task-item', params: {itemId: task.id}})
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
    this.$store.dispatch('todos/load', {clientId: this.$store.getters['user/current']['id']})
    this.$store.dispatch('todos/resetFocus')
  },
}
</script>

<style lang="scss">

.item {

  &--input {
    width: 100%;
    margin-bottom: 10px;
  }
}

</style>
