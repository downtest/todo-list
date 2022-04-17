<template>
  <div class="item--labels" v-if="labels">
    <draggable
        class="labels-list"
        v-model="labels"
        item-key="id"
        tag="div"
        handle=".label"
    >
      <template #item="{element}">
        <div class="label" :data-id="element.name" :style="`border: 1px solid ${element.color.border}; background-color: ${element.color.background}; color: ${element.color.color};`">
          <span class="label-name">{{element.name}}</span>
          <span class="close-btn" @click="deleteLabel(index)">
              <img class="btn-icon" :src="$store.getters['icons/Plus']" alt="delete" title="Delete label">
          </span>
        </div>
      </template>
    </draggable>

    <div>
      <label class="label">
        <input class="label__input" placeholder="Текст лейбла..." type="text" v-model="labelInput" :style="`border-color: ${labelColor.background}`">
        <div class="label__name" style="text-align: center"><button class="label-add__btn" @click="addLabel">Добавить</button></div>
      </label>

      <div class="label-colors">
        <div v-for="colorObj in labelColors"
             @click="labelColor = colorObj"
             class="color"
             :style="`background-color: ${colorObj.background}; color: ${colorObj.color}; border: 1px solid ${colorObj.border}; --label-color-shadow: ${colorObj.background};`"
        ></div>
      </div>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable"

export default {
  name: "Labels",
  components: {
    draggable,
  },
  props: {
    task: {
      type: Object,
      required: true,
    },
  },
  computed: {
    labels: {
      get() {
        if (this.task.updated && this.task.updated.labels) {
          return this.task.updated.labels
        }

        return this.task.labels || []
      },
      set(value) {
        this.$store.dispatch('todos/changeLabelsOrder', {
          id: this.task.id,
          labels: value,
        })
      },
    },
  },
  data() {
    return {
      labelInput: '',
      labelColor: '',
      labelColors: [
        {background: 'red', color: 'black', border: 'red'},
        {background: 'orange', color: 'black', border: 'orange'},
        {background: 'yellow', color: 'black', border: 'orange'},
        {background: 'green', color: '#eee', border: 'green'},
        {background: 'darkblue', color: '#eee', border: 'darkblue'},
        {background: 'violet', color: 'black', border: 'violet'},
      ],
    }
  },
  methods: {
    addLabel() {
      this.$store.dispatch('todos/addLabel', {
        id: this.task.id,
        label: {
          name: this.labelInput,
          color: this.labelColor,
        },
      })

      this.labelInput = ''
      this.labelColor = ''
    },
    deleteLabel(index) {
      this.$store.dispatch('todos/deleteLabel', {
        id: this.task.id,
        index: index,
      })
    },
  }
}
</script>

<style lang="scss" scoped>
  @import "./../../scss/variables";
  @import "../../scss/Task/label";

  .item--labels {
    display: block;
    //display: flex;
    //align-content: flex-start;
    //flex-wrap: wrap;
    margin-bottom: 30px;

    .labels-list {

      .label {
        display: flex;
        border-radius: 3px;
        margin-bottom: 5px;
        margin-top: 5px;
        padding: 0;
        color: $colorWhite;
        height: 60px;

        .label-name {
          margin: 5px;
          width: 100%;
        }

        .close-btn {
          //float: right;
          height: 100%;
          width: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
      }
    }

    .label-add {
      display: flex;
      margin-top: 30px;

      .label-add__input {
        width: 100%;
      }

      .label-add__btn {

      }
    }

    .label-text-input {
      border-radius: 5px;
      border: 2px solid $colorDark;
    }

    .label-colors {
      display: flex;
      flex-wrap: wrap;

      .color {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin: 10px;

        &:hover{
          box-shadow: 0 0 10px var(--label-color-shadow);
        }
      }
    }
  }
</style>
