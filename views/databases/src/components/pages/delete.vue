<template lang="html">
  <div class="container">
    <h4>Deleting Database</h4>
    <div class="row">
      <a :href="baseUrl + 'databases/delete/' + base.id" class="pull-right btn btn-sm btn-danger">Delete</a>
      <p><b>Caution !</b> A deleted database cannot be recovered, all data will be lost.</p>
    </div>
  </div>
</template>

<script>
/* global baseUrl */

export default {
  data () { return { baseUrl } },
  computed: {
    base () { return this.$store.state.base }
  }
}
</script>

<style lang="css">
</style>
