<template>
  <div id="app">
      <div class="container">
        <div class="row">
          <div class="title-border blue">
            <navigation></navigation>

            <h3 class="blue">{{ base.name }}</h3>
            <h5 class="gray">Owned by {{ base.owner.name }}</h5>
          </div>
        </div>
        <div class="row" v-if="!shortHeader">
          <div v-if="base.description" class="col-xs-12 col-sm-6">
            <p>{{ autolink(base.description) }}</p>
          </div>
          <div v-if="base.website" class="col-xs-12 col-sm-6">
            <b>Support Website:</b> {{ autolink(base.website) }}
          </div>
          <br><br>
        </div>
      </div>

      <router-view></router-view>
  </div>
</template>

<script>
import { autolink } from '../lib/utils'
import navigation from './partials/navigation.vue'

export default {
  name: 'app',
  components: { navigation },
  computed: {
    shortHeader () { return false },
    base () { return this.$store.state.base }
  },
  methods: {
    autolink
  }
}
</script>

<style lang="scss">
@import url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
</style>
