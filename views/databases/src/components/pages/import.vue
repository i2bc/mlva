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
import { convertPanel, convertHeaders } from '@/lib/csv'
import { redirect, Request } from '@/lib/request'
import Importer from '@/lib/import'

import headersTable from '@/components/partials/headersTable.vue'
import csvForm from '@/components/partials/csvForm.vue'

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
        h.type.match(/\[?(k|K)ey]?/)
      )
      doEmptyArray(this.oStrains)
      doEmptyArray(this.nStrains)
      for (let row of data) {
        if (['key', '[key]'].includes(row[key].trim().toLowerCase())) continue
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
    async onSubmit () {
      this.message = 'Sending informations...'
      try {
        // Get database id
        let id = this.$store.state.base.id
        // Update columns
        let mlvadata = this.nHeaders.filter(h => h.type === 'mlva').map(h => h.name)
        let metadata = this.nHeaders.filter(h => h.type === 'info').map(h => h.name)
        if (mlvadata.length + metadata.length > 0) {
          await Request.post('databases/addColumns/' + id, { mlvadata, metadata })
          for (let mlva of mlvadata) this.$store.commit('addMlvadata', mlva)
          for (let meta of metadata) this.$store.commit('addMetadata', meta)
        }
        // Get database headers
        let headers = this.$store.state.headers.list.map(h => {
          let type = h.type
          if (type === '[key]') type = 'key'
          if (type === 'meta') type = 'info'
          return { name: h.name, import: h.name, type }
        })
        // Import new strains
        let nStrains = []
        if (this.options.addStrains) {
          nStrains = await Importer.sendStrains(this.nStrains, id, headers, this.geolocalisation)
        }
        // Import old strains
        let oStrains = []
        if (this.options.updateStrains) {
          oStrains = await Importer.sendStrains(this.oStrains, id, headers, this.geolocalisation, this.strains)
        }
        // Import panels
        let panels
        if (this.options.addPanels) {
          panels = await Importer.sendPanels(this.panels, id)
          console.log(panels)
        }
        // Import GN
        if (this.options.addGN) {
          let strains = [].concat(nStrains, oStrains)
          await Importer.sendGNs(strains, this.panels, id)
        }
        // Reset the store
        /* global databaseInfos, ownerInfos */
        this.$store.dispatch('initialize', { base: databaseInfos, owner: ownerInfos, panels: this.panels })
        // Redirect to the database
        redirect('databases/view/' + id)
      } catch (e) {
        // Handle errors
        console.warn(e)
        this.errors = e
      }
    }
  }
}
</script>
