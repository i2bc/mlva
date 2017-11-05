<template lang="html">
  <div class="row">
    <div :class="{'form-group': true, 'has-error': formErrors.has('name') }">
      <label for="name">Database Name</label>
      <input v-validate="{ required: true, regex: /^[\w\d ]+$/ }" type="text" class="form-control" name="name" v-model="base.name" placeholder="Database Name"/>
      <small v-show="formErrors.has('name')" class="form-text text-danger">{{ formErrors.first('name') }}</small>
    </div>

    <div class="form-group">
      <label for="website">Database Support Website</label>
      <input type="text" class="form-control" id="website" v-model="base.website" placeholder="Database Support Website"/>
    </div>

    <div class="form-group">
      <label for="description">Database Description</label>
      <textarea class="form-control" id="description" v-model="base.description" rows="3"></textarea>
    </div>

    <div class="form-group" v-if="isOwner">
      <label for="groupId">Database Type</label>
      <select class="form-control" v-model="base.groupId" id="groupId">
        <option :value="-2">Make a new group</option>
        <option :value="-1">Personal</option>
        <option v-for="group of user.groups" :value="group.id">{{ group.name }}</option>
      </select>
    </div>

    <div class="form-group" v-if="isOwner && base.groupId == -2">
      <label for="groupName">Group of the Database</label>
      <input type="text" class="form-control" id="groupName" v-model="base.groupName" placeholder="Group Name"/>
    </div>

    <div class="form-group">
      <div class="checkbox">
        <label>
          <input type="checkbox" v-model="base.state">
          Is that database public ?
        </label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['base', 'isOwner'],
  computed: {
    user () { return this.$store.state.user }
  }
}
</script>

<style lang="css">
</style>
