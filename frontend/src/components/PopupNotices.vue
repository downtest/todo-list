<template>
  <div class="popup-notices">
    <div :class="{
        'notice' : true,
        'notice_error': notice.type === 'error',
        'notice_warning': notice.type === 'warning',
        'notice_success': notice.type === 'success',
        'deprecated': notice.deprecated,
      }" v-for="notice in $store.getters['popupNotices/all']">
      <div class="notice--text">{{notice.text}}</div>
      <div class="notice--cross" @click="removeNotice(notice)">X</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "PopupNotifications",
  methods: {
    removeNotice(notice) {
      if (notice.deprecated) {
        // Запись уже помечена на удаление
        return
      }

      this.$store.dispatch('popupNotices/remove', notice.id)
    },
  },
}
</script>
