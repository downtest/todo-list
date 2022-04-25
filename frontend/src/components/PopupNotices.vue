<template>
  <div>
    <div class="notice notice_error" @click="removeNotice(notice)" v-for="notice in $store.getters['popupNotices/errors']">{{notice.text}}</div>
    <div class="notice notice_warning" @click="removeNotice(notice)" v-for="notice in $store.getters['popupNotices/warnings']">{{notice.text}}</div>
    <div class="notice notice_success" @click="removeNotice(notice)" v-for="notice in $store.getters['popupNotices/success']">{{notice.text}}</div>
  </div>
</template>

<script>
export default {
  name: "PopupNotifications",
  methods: {
    removeNotice(notice) {
      this.$store.dispatch('popupNotices/remove', notice.id)
    },
  },
}
</script>

<style lang="scss" scoped>
.notice {
  margin: 10px;
  border-radius: 3px;

  &_error {
    background-color: #720707;
    color: #dedede;
  }

  &_warning {
    background-color: #e5833b;
    color: #232323;
  }

  &_success {
    background-color: #00850c;
    color: #e5e5e5;
  }
}
</style>
