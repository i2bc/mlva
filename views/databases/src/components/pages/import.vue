<template lang="html">
  <div class="container">
    <h4>Import CSV</h4>

    <form @subtmit.prevent="onSubmit">
      <div class="row">
        <div class="col-xs-12 col-sm-6">
          <csv-form @file="onFile"></csv-form>

          <div class="form-group" v-if="nStrains.length">
            <div class="checkbox">
              <label>
                <input type="checkbox" v-model="options.addStrains">
                Add new strains ({{ nStrains.length }} strain(s))
              </label>
            </div>
          </div>

          <div class="form-group" v-if="oStrains.length">
            <div class="checkbox">
              <label>
                <input type="checkbox" v-model="options.updateStrains">
                Update old strains ({{ oStrains.length }} strain(s))
              </label>
            </div>
          </div>

          <div class="form-group" v-if="panels.length">
            <div class="checkbox">
              <label>
                <input type="checkbox" v-model="options.addPanels">
                Add new panels ({{ panels.length }} panels)
              </label>
            </div>
          </div>

          <div class="form-group">
            <div class="checkbox">
              <label>
                <input type="checkbox" v-model="options.addGN">
                Import genotypes
              </label>
            </div>
          </div>

          <br>

          <div class="form-group">
            <label for="group">Geolocalisation colum</label>
            <select class="form-control" v-model="geolocalisation">
              <option value="">Use Lon/Lat</option>
              <option v-for="meta of allMetadata">{{ meta }}</option>
              <option v-for="meta of infoHeaders">{{ meta }}</option>
            </select>
          </div>
        </div>

        <div class="col-xs-12 col-sm-6">
          <headers-table :showKey="false" :headers="nHeaders" :mlva="allMlvadata" :meta="allMetadata"></headers-table>
        </div>
      </div>

      <div class="row">
        <div class="text-danger" v-if="errors" v-html="errors"></div>
        <div class="text-success" v-if="message" v-html="message"></div>

        <div class="col-xs-12">
          <p class="text-center">
            <br>
            <button type="submit" @click.prevent="onSubmit" class="btn btn-primary btn-lg">Import the file</button>
          </p>
        </div>
      </div>
    </form>
	</div>
</template>


<script>
import { maskGeno } from '../../lib/query'
import { getGN } from '../../lib/genonums'
import { postRequest, redirect } from '../../lib/request'
import { convertPanel, convertStrain, convertHeaders, setLocation } from '../../lib/csv'
import headersTable from '../partials/headersTable.vue'
import csvForm from '../partials/csvForm.vue'

const doEmptyArray = arr => arr.splice(0, arr.length)

export default {
  components: { headersTable, csvForm },
  data () {
    return {
      errors: '',
      message: '',
      nHeaders: [],
      geolocalisation: '',
      panels: [],
      nStrains: [], // new
      oStrains: [], // old
      options: { addStrains: true, updateStrains: true, addPanels: true, addGN: true }
    }
  },
  computed: {
    user () { return this.$store.state.user },
    strains () { return this.$store.getters.strains },
    allMetadata () { return this.$store.getters.allMetadata },
    allMlvadata () { return this.$store.getters.allMlvadata },
    infoHeaders () { return this.nHeaders.filter(h => h.name.trim() !== '' && h.type === 'info').map(h => h.name) },
    mlvaHeaders () { return this.nHeaders.filter(h => h.name.trim() !== '' && h.type === 'mlva').map(h => h.name) },
    keyHeader () { return this.headers.find(h => h.type === 'key').name }
  },
  methods: {
    onFile ({ data }) {
      let key = Object.keys(data[0]).find(h => h.toLowerCase() === 'key')
      this.key = key
      let headers = convertHeaders(data[0])
      let panelNames = this.$store.state.panels.list.map(p => p.name)
      this.nHeaders = headers.filter(h =>
        !panelNames.includes(h.name) &&
        !this.allMlvadata.includes(h.name) &&
        !this.allMetadata.includes(h.name) &&
        !this.allMetadata.includes(h.name) &&
        h.type !== 'key'
      )
      doEmptyArray(this.oStrains)
      doEmptyArray(this.nStrains)
      for (let row of data) {
        if (row[key] === 'key') continue
        if (row.key && row.key.startsWith('[panel]')) {
          this.panels.push(convertPanel(row))
          continue
        }
        if (this.strains.find(s => s.name === row[key])) {
          this.oStrains.push(row)
        } else {
          this.nStrains.push(row)
        }
      }
    },
    onSubmit () {
      let id = this.$store.state.base.id
      let mlvadata = this.nHeaders.filter(h => h.type === 'mlva').map(h => h.name)
      let metadata = this.nHeaders.filter(h => h.type === 'info').map(h => h.name)
      if (this.geolocalisation) {
        if (!this.allMetadata.includes('lat')) metadata.push('lat')
        if (!this.allMetadata.includes('lon')) metadata.push('lon')
      }
      if (mlvadata.length + metadata.length) {
        postRequest('databases/addColumns/' + id, { mlvadata, metadata })
        for (let mlva of mlvadata) this.$store.commit('addMlvadata', mlva)
        for (let meta of metadata) this.$store.commit('addMetadata', meta)
      }
      let headers = this.$store.state.headers.list.map(h => {
        let type = h.type
        if (type === '[key]') type = 'key'
        if (type === 'meta') type = 'info'
        return { name: h.name, import: h.name, type }
      })
      headers.find(h => h.type === 'key').import = this.key
      let nStrains = this.nStrains.map(s => convertStrain(s, headers))
      let oStrains = this.oStrains.map(s => convertStrain(s, headers, this.strains.find(a => s[this.key] === a.name)))
      if (this.geolocalisation) {
        nStrains = nStrains.map(s => setLocation(s, this.geolocalisation))
        oStrains = oStrains.map(s => setLocation(s, this.geolocalisation))
      }
      if (!this.options.addStrains) nStrains = []
      if (!this.options.updateStrains) oStrains = []
      let addStrains = () => {
        let strains = nStrains.splice(0, 10)
        if (strains.length) postRequest('strains/add/' + id, { strains }, addStrains)
        else this.message = 'The informations have been saved'
      }
      let updateStrains = () => {
        let strains = oStrains.splice(0, 10)
        if (strains.length) postRequest('strains/update/' + id, { strains }, updateStrains)
        else addStrains()
      }
      this.message = 'Sending informations...'
      updateStrains()
      if (this.options.addGN) {
        let genonums = {}
        let panels = this.$store.state.panels.list
        for (let s of this.oStrains) {
          for (let panel of panels) {
            let gn = s[panel.name]
            if (gn == null || gn.endsWith('temp')) continue
            let strain = convertStrain(s, headers)
            let data = maskGeno(strain.data, panel.data)
            let oGN = getGN(panel, strain)
            genonums[panel.id] = genonums[panel.id] || []
            genonums[panel.id].push({ data, nValue: gn, oValue: oGN ? oGN.value : null })
          }
        }
        for (let panelId in genonums) {
          postRequest('panels/updateGN/' + panelId, { 'GN': genonums[panelId] }, () => {
            this.$store.dispatch({ panelId, listGN: genonums[panelId] })
          })
        }
      }
      setTimeout(() => this.$store.dispatch('initStrains', { base: this.$store.state.base }), 1e3)
      redirect('databases/view/' + id)
    }
  }
}
</script>
