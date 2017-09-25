<template lang="html">
  <div>
    <div class="container">
      <div class="row">
        <h4>Handle panels</h4>
      </div>
    </div>

    <div class="database">
      <table id="strain-list" class="table table-condensed database">
        <thead><tr>
          <th style="min-width: 200px;">Name</th>
          <th v-for="mlva in mlvadata" class="rotate"><div><span>{{ mlva }}</span></div></th>
          <th style="min-width: 320px;"></th>
        </tr></thead>
        <tbody>
          <tr v-for="panel in panels">
            <td><input class="form-control" v-model="panel.name" placeholder="Panel Name" type="text" /></td>
            <td v-for="mlva in mlvadata" class="marker-checkbox"><input type="checkbox" v-model="panel.data[mlva]"/></td>
            <td>
              <button class="btn btn-default" @click.prevent="updatePanel(panel)">Update</button>
              <button class="btn btn-default" @click.prevent="generateGN(panel)">Generate Geno Num</button>
              <button class="btn btn-default" @click.prevent="deletePanel(panel)">Delete</button>
            </td>
          </tr>
          <tr>
            <td><input class="form-control" v-model="nPanel.name" placeholder="New Panel"/></td>
            <td v-for="mlva in mlvadata" class="marker-checkbox"><input type="checkbox" v-model="nPanel.data[mlva]"/></td>
            <td><button @click.prevent="createPanel" class="btn btn-default">Submit</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import Request from '../../lib/request'
import { generateTempGN } from '../../lib/genonums'

let baseId, allBasedata

function convertPanel (panel) { return Object.assign({}, panel, { data: filterA2O(panel.data) }) }
function filterO2A (obj) { return allBasedata.filter(bd => obj[bd]) }
function filterA2O (arr) {
  let data = {}
  for (let bd of allBasedata) data[bd] = arr.includes(bd)
  return data
}

function emptyPanel () { return { name: '', data: filterA2O(allBasedata) } }

export default {
  data () {
    baseId = this.$store.state.base.id
    allBasedata = this.$store.getters.allMlvadata
    return { panels: this.$store.state.panels.list.map(convertPanel), nPanel: emptyPanel() }
  },
  computed: {
    mlvadata () { return this.$store.getters.allMlvadata }
  },
  methods: {
    createPanel () {
      let data = filterO2A(this.nPanel.data)
      let name = this.nPanel.name
      if (name.trim() === '' || data.length === 0) return
      Request.post('panels/make', { baseId, name, data })
        .then(p => {
          let panel = convertPanel(p)
          this.panels.push(panel)
          this.$store.commit('addPanel', p)
          this.nPanel = emptyPanel()
        })
    },
    updatePanel (panel) {
      let data = filterO2A(panel.data)
      let name = panel.name
      if (name.trim() === '' || data.length === 0) return
      Request.post('panels/update/' + panel.id, { name, data })
        .then(p => this.$store.commit('updatePanel', p))
    },
    deletePanel (panel) {
      Request.post('panels/delete/' + panel.id)
        .then(() => {
          this.panels = this.panels.filter(p => p !== panel)
          this.$store.commit('deletePanel', panel)
        })
    },
    generateGN (panel) {
      let nData = generateTempGN(panel.id)
      if (nData.length === 0) return
      Request.postBlob('panels/addGN/' + panel.id, nData)
        .then(gnList => {
          for (let gn of gnList) this.$store.commit('addGN', { panelId: panel.id, gn })
        })
    }
  }
}
</script>

<style lang="scss">
.marker-checkbox {
  padding: 10px !important;
  text-align: center;
}
</style>
