<template>
  <div id="app">
    <div class="container">
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
              <button type="submit" @click.prevent="onSubmit" class="btn btn-primary btn-lg">Create the new database</button>
            </p>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { postRequest, redirect } from '../lib/request'
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
      options: { strains: true, panels: true }
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
        if (row.key && row.key === 'key') continue
        if (row.key && row.key.startsWith('[panel]')) {
          this.panels.push(convertPanel(row))
          continue
        }
        this.strains.push(row)
      }
    },
    onSubmit () {
      let strains = this.strains.map(s => convertStrain(s, this.headers))
      if (this.geolocalisation) strains = strains.map(s => setLocation(s, this.geolocalisation))
      postRequest('databases/createForm', {
        name: this.base.name,
        groupId: this.base.groupId,
        groupName: this.base.groupName || '',
        mlvadata: this.mlvaHeaders,
        metadata: this.infoHeaders,
        key: this.keyHeader,
        state: this.base.state
      }, ({ id, errors }) => {
        if (errors) {
          this.errors = errors
        } else {
          if (this.options.strains) postRequest('strains/add/' + id, { strains })
          if (this.options.panels) for (let p of this.panels) postRequest('panels/make', { baseId: id, name: p.name, data: p.data })
          redirect('databases/view/' + id)
        }
      })
    }
  }
}
</script>
