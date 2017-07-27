<template lang="html">
  <div class="container">
    <h4>Edit Database</h4>

    <form @subtmit.prevent="onSubmit">
      <edit-form :base="nBase" :isOwner="isOwner"></edit-form>

      <div class="row">
        <div class="text-danger" v-if="errors" v-html="errors"></div>
        <div class="text-success" v-if="message" v-html="message"></div>

        <div class="col-xs-12">
          <p class="text-center">
            <br>
            <button type="submit" @click.prevent="onSubmit" class="btn btn-primary">Edit the database</button>
          </p>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import Request from '../../lib/request'
import editForm from '../partials/editForm.vue'

export default {
  components: { editForm },
  data () {
    return { nBase: this.$store.state.base, errors: '', message: '' }
  },
  computed: {
    user () { return this.$store.state.user },
    base () { return this.$store.state.base },
    isOwner () { return this.base.userId === this.user.id }
  },
  methods: {
    onSubmit () {
      let form = this.nBase
      form.group_id = this.nBase.groupId
      form.group_name = this.nBase.groupName || ''
      Request.post('databases/edit/' + this.base.id, form)
        .then(data => {
          if (data.errors) {
            this.message = ''
            this.errors = data.errors
          } else {
            this.errors = ''
            this.message = 'The informations have been saved'
            this.$store.commit('updateBase', data)
          }
        })
    }
  }
}
</script>

<style lang="css">
</style>
