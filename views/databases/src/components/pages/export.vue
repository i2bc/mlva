<template lang="html">
  <div class="container">
    <h4>Export as CSV</h4>

    <form @subtmit.prevent="onSubmit">
      <div class="col-sm-6 col-xs-12">
        <div class="form-group">
          <label for="panel">Panel</label>
          <select class="form-control" v-model="panelId" id="panel">
            <option value="-1">All</option>
            <option v-for="panel in panels" :value="panel.id">{{ panel.name }}</option>
            <option value="-2">None</option>
          </select>
        </div>

        <div class="form-group">
          <div v-for="meta in metadata" class="checkbox">
            <label><input type="checkbox" v-model="meta.visible"/> {{ meta.name }}</label>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xs-12">
        <div class="form-group">
          <label class="radio-inline">
            <input type="radio" v-model="csvMode" value="fr"/> French system ( ';' )
          </label>
          <label class="radio-inline">
            <input type="radio" v-model="csvMode" value="eng"/>  English system ( ',' )
          </label>
        </div>

        <div class="form-group">
          <div class="checkbox">
            <div class="checkbox">
              <label><input type="checkbox" v-model="advanced"/> Advanced Export</label>
              <label><input type="checkbox" v-model="tempGN"/> Export Temp Genotype Number</label>
            </div>
          </div>
        </div>

        <button type="submit" @click.prevent="onSubmit" class="btn btn-default">Export</button>
      </div>
    </form>
  </div>
</template>

<script>
/* global Papa */

import { makeArray } from '../../lib/csv'
import { downloadFile } from '../../lib/files'

export default {
  data () {
    return {
      metadata: this.$store.state.headers.list.filter(h => h.type === 'meta'),
      panelId: this.$store.state.panels.panelId || -1,
      csvMode: 'eng',
      advanced: true,
      tempGN: false
    }
  },
  computed: {
    base () { return this.$store.state.base },
    panels () { return this.$store.state.panels.list }
  },
  methods: {
    onSubmit () {
      let metadata = this.metadata.filter(h => h.visible).map(h => h.name)
      let rows = makeArray(metadata, this.panelId, this.advanced, this.tempGN)
      let delimiter = this.csvMode === 'eng' ? ',' : ';'
      let str = Papa.unparse(rows, { delimiter, header: false, newline: '\r\n' })
      console.log(str)
      downloadFile(this.base.name + '.csv', 'csv', str)
    }
  }
}
</script>

<style lang="css">
</style>
