<template lang="html">
  <div class="btn-group pull-right" role="group">
    <router-link v-if="!short && !queried" tag="button" class="btn btn-default" to="/query">Query</router-link>

    <div class="btn-group" role="group" v-if="!short">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Views
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <li><router-link to="/exportCSV">Export as CSV</router-link></li>
        <li><router-link to="/map">Geolocalisation</router-link></li>
        <li><router-link v-if="queried" to="/matrix">Distance Matrix</router-link></li>
        <li><router-link v-if="queried" to="/tree">Newick Tree</router-link></li>
      </ul>
    </div>

    <div class="btn-group" role="group" v-if="!short && !queried && authLevel >= 2">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Options
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <li><router-link to="/import">Import CSV</router-link></li>
        <li v-if="authLevel >= 3"><router-link to="/edit">Edit Database</router-link></li>
        <li v-if="authLevel >= 3"><router-link to="/delete">Delete Database</router-link></li>
      </ul>
    </div>

    <div class="btn-group" role="group" v-if="!short && !queried">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Panel selection
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <li v-for="panel in sortedPanel">
          <a @click="togglePanel(panel.id)" :style="{ fontWeight: (panel.id === panels.panelId ? 'bold' : 'normal') }">{{ panel.name }}</a>
        </li>
        <li v-if="!panels.list.length"><a disabled="disabled"><i>No panel</i></a></li>
        <li v-if="authLevel >= 2" role="separator" class="divider"></li>
        <li v-if="authLevel >= 2"><router-link to="/editPanels">Handle Panels</router-link></li>
      </ul>
    </div>

    <button v-if="!short && queried" @click="unquery" class="btn btn-default">See all strains</button>
    <router-link v-if="short" tag="button" class="btn btn-default" to="/">Back</router-link>
  </div>
</template>

<script>
export default {
  computed: {
    short () { return !['', '/'].includes(this.$route.path) },
    authLevel () { return this.$store.state.user.authLevel },
    queried () { return this.$store.getters.queried },
    panels () { return this.$store.state.panels },
    sortedPanel () { return this.panels.list.sort((a, b) => a.name > b.name) }
  },
  methods: {
    unquery () { this.$store.commit('unquery') },
    togglePanel (panelId) { this.$store.commit('togglePanel', panelId) }
  }
}
</script>

<style lang="css">
.dropdown-menu li a {
  cursor: pointer;
}
</style>
