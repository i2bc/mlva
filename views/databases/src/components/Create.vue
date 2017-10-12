<template>
  <div id="app">
    <div class="container" v-if="!sending">
      <div class="row">
        <div class="title-border blue">

          <h3 class="blue">Create a new database</h3>
          <!-- <h5 class="gray"></h5> -->
        </div>
      </div>

      <form @subtmit.prevent="onSubmit">
        <h4>General information</h4>
        <edit-form :base="base" :isOwner="true"></edit-form>

        <br>

        <h4>Database Content</h4>
        <div class="row">
          <div class="col-xs-12 col-sm-6">
            <csv-form @file="onFile"></csv-form>

            <br>

            <div class="form-group" v-if="panels.length">
              <div class="checkbox">
                <label>
                  <input type="checkbox" checked v-model="options.panels">
                  Import Panels ({{ panels.length }} panels)
                </label>
              </div>
            </div>

            <div class="form-group" v-if="strains.length">
              <div class="checkbox">
                <label>
                  <input type="checkbox" checked v-model="options.strains">
                  Import Strains ({{ strains.length }} strains)
                </label>
              </div>
            </div>

            <br>

            <div class="form-group" v-if="infoHeaders.length">
              <label for="group">Geolocalisation colum</label>
              <select class="form-control" v-model="geolocalisation">
                <option value="">Use Lon/Lat</option>
                <option v-for="info of infoHeaders">{{ info }}</option>
              </select>
            </div>

            <br>

            <button type="submit" @click.prevent="onSubmit" class="btn btn-primary btn-lg" :disabled="!headers.length">Create the new database</button>
          </div>

          <div class="col-xs-12 col-sm-6">
            <headers-table :headers="headers"></headers-table>
          </div>
        </div>

        <div class="row">
          <div class="text-danger" v-if="errors" v-html="errors"></div>
          <div class="text-success" v-if="message" v-html="message"></div>

          <div class="col-xs-12">
            <p class="text-center">
              <br>
              <button type="submit" @click.prevent="onSubmit" class="btn btn-primary btn-lg" :disabled="!headers.length">Create the new database</button>
            </p>
          </div>
        </div>
      </form>
    </div>
    <div v-else>
      <br>
      <div class="loader"></div>
      <h5 class="text-center">Sending data...</h5>
      <br>
    </div>
  </div>
</template>

<script>
import { maskGeno } from '../lib/query'
import { default as Request, redirect } from '../lib/request'
import { convertPanel, convertStrain, convertHeaders, setLocation } from '../lib/csv'
import headersTable from './partials/headersTable.vue'
import editForm from './partials/editForm.vue'
import csvForm from './partials/csvForm.vue'

const doEmptyArray = arr => arr.splice(0, arr.length)

export default {
  name: 'app',
  components: { headersTable, editForm, csvForm },
  data () {
    return {
      base: { name: '', description: '', website: '', groupId: -1, state: false },
      errors: '',
      message: '',
      headers: [],
      geolocalisation: '',
      panels: [],
      strains: [],
      options: { strains: true, panels: true },
      sending: false
    }
  },
  computed: {
    user () { return this.$store.state.user },
    infoHeaders () { return this.headers.filter(h => h.name.trim() !== '' && h.type === 'info').map(h => h.name) },
    mlvaHeaders () { return this.headers.filter(h => h.name.trim() !== '' && h.type === 'mlva').map(h => h.name) },
    keyHeader () { return this.headers.find(h => h.type === 'key').name }
  },
  methods: {
    onFile ({ file, data }) {
      this.base.name = file.name.replace(/\.(csv|txt)/, '')
      this.headers = convertHeaders(data[0])
      doEmptyArray(this.strains)
      doEmptyArray(this.panels)
      for (let row of data) {
        if (row.key && row.key === '[key]') continue
        if (row.key && row.key.startsWith('[panel]')) {
          this.panels.push(convertPanel(row))
          continue
        }
        this.strains.push(row)
      }
    },
    onSubmit () {
      let allStrains = this.strains.map(s => convertStrain(s, this.headers))
      if (this.geolocalisation) allStrains = allStrains.map(s => setLocation(s, this.geolocalisation))
      this.sending = true
      Request.post('databases/createForm', {
        name: this.base.name,
        groupId: this.base.groupId,
        groupName: this.base.groupName || '',
        mlvadata: this.mlvaHeaders,
        metadata: this.infoHeaders,
        key: this.keyHeader,
        state: this.base.state
      }).then(({ id, errors }) => {
        if (errors) {
          console.warn(errors)
          this.errors = errors
        } else {
          // let panels = []
          if (this.options.panels) {
            let panelPosts = this.panels.map(p => Request.post('panels/make', { baseId: id, name: p.name, data: p.data }))
            Promise.all(panelPosts).then(panels => {
              if (!this.options.strains) return
              let genonums = {}
              for (let s of this.strains) {
                for (let panel of panels) {
                  let gn = s[panel.name]
                  if (gn == null || gn.endsWith('temp') || !gn.trim()) continue
                  let strain = convertStrain(s, this.headers)
                  let data = maskGeno(strain.data, panel.data)
                  genonums[panel.id] = genonums[panel.id] || []
                  genonums[panel.id].push({ value: gn, data })
                }
              }
              for (let panelId in genonums) Request.postBlob('panels/addGN/' + panelId, genonums[panelId])
            })
          }
          if (this.options.strains) {
            Request.postBlob('strains/add/' + id, allStrains)
              .then(() => redirect('databases/view/' + id))
          } else { redirect('databases/view/' + id) }
        }
      })
    }
  }
}
</script>

<style lang="scss">
@import url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

.loader {
  margin: auto;
  border: 16px solid #f3f3f3; /* Light grey */
  border-top: 16px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 120px;
  height: 120px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
