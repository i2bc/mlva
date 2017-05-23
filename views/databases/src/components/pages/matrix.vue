<template lang="html">
  <div class="container">
  	<div class="row">
  		<div class="col-xs-12">
  			<h4>
          <a @click.prevent="exportMEGA" class="pull-right btn btn-large btn-primary">Export to MEGA Format</a>
          Export to MEGA Matrix Format
        </h4>
  		</div>
  	</div>

    <br>

  	<div class="row">
  		<div class="col-xs-12">
  			<h4>Distance Matrix</h4>

        <table class="table table-striped table-bordered text-center">
          <thead><tr>
            <th>Key</th>
            <th v-for="strain in strains">{{ strain.name }}</th>
            <th><i>Queried <br> strain</i></th>
          </tr></thead>
          <tbody>
            <tr v-for="strain in strains">
              <td class="colKey">{{ strain.name }}</td>
              <td v-for="other in strains" :style="{ opacity: strain.name === other.name ? 0.5 : 1 }">{{ distance(strain, other) }}</td>
              <td>{{ strain.deltaDist }}</td>
            </tr>
            <tr>
              <td class="colKey"><i>Queried <br> strain</i></td>
              <td v-for="strain in strains">{{ strain.deltaDist }}</td>
              <td style="opacity: 0.5;">0</td>
            </tr>
          </tbody>
        </table>

  		</div>
  	</div>
  </div>
</template>

<script>
import { downloadFile } from '../../lib/files'
import { distanceGeno, maskGeno, getMask } from '../../lib/query'

export default {
  computed: {
    queryRef () { return this.$store.state.strains.query.ref },
    strains () { return this.$store.getters.strains },
    base () { return this.$store.state.base }
  },
  methods: {
    distance (strainA, strainB) {
      let dataA = maskGeno(strainA.data, getMask(this.queryRef))
      let dataB = maskGeno(strainB.data, getMask(this.queryRef))
      return distanceGeno(dataA, dataB)
    },
    exportMEGA () {
      let str = ''
      str += '#mega\r\n[if file does not open with MEGA, check MEGA manual for taxa names rules.]\r\n'
      str += '!Title: ' + this.base.name + ';\r\n'
      str += '!Format DataType=Distance DataFormat=LowerLeft NTaxa=' + this.strains.length + ';\r\n'
      str += '!Description\r\n'
      str += 'Distance Matrix of queried database : ' + this.base.name + ';\r\n\r\n'
      // replace incompatibles characters
      // And print strains
      for (let strain of this.strains) {
        str += '#' + strain.name + '\r\n'
      }
      // Print the matrix
      for (let strain of this.strains) {
        let dists = this.strains.map(other => this.distance(strain, other))
        str += dists.join(' ') + '\r\n'
      }

      downloadFile(this.base.name + '.meg', 'txt', str)
    }
  }
}
</script>

<style lang="css" scoped>
.table > thead > tr > th {
  text-align: center;
  vertical-align: middle;
}

.colKey { font-weight: bold; }
</style>
