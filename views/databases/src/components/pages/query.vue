<template lang="html">
  <div class="container">
    <form class="" @submit.prevent="onSubmit">
      <table id="strain-list" class="tablesorter table table-condensed database">
        <thead>
          <tr>
            <th>Key</th>
            <th v-for="mlva in mlvadata" class="rotate"><div><span>{{ mlva }}</span></div></th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td><i>Example of strain</i> <a @click.prevent="copy">(Copy)</a></td>
            <td class="marker" v-for="bd in mlvadata">{{ sampleStrain.data[bd] }}</td>
          </tr>
          <tr>
            <td>Strain to compare to</td>
            <td class="marker" v-for="mlva in mlvadata">
              <input class="form-control marker" :name="mlva" @change="parseAndFillInputs" v-model="form.ref[mlva]" type="text">
            </td>
          </tr>
        </tbody>
      </table>

      <br><br>

      <div class="row">
        <div class="col-sm-6 form-group">
          <label for="max_dist">Maximal distance</label> <input class="form-control" v-model="form.maxDist" type="number" min="0" step="1" />
        </div>
        <div class="col-sm-6 form-group">
          <label for="max_amount">Maximal number of strains</label> <input class="form-control" v-model="form.maxAmount" type="number" min="0" step="1" />
        </div>
      </div>

      <div class="row">
        <div class="col-sm-6 form-group">
          <input type="checkbox" v-model="form.uniq"> <label for="max_dist">Pick only one strain per genotype</label>
        </div>
        <div class="col-sm-6 form-group">
          <button type="submit" class="btn btn-default">Submit</button>
        </div>
      </div>

    </form>
  </div>
</template>

<script>
const delimiters = [/\t/, /\s/]

export default {
  data () {
    return { form: { ref: {}, maxDist: 0, maxAmount: 20, uniq: false } }
  },
  computed: {
    mlvadata () { return this.$store.getters.allMlvadata },
    sampleStrain () { return this.$store.getters.strains[0] }
  },
  methods: {
    copy () {
      this.form.ref = {}
      for (let bd of this.mlvadata) this.form.ref[bd] = this.sampleStrain.data[bd]
    },
    parseAndFillInputs (e) {
      let text = e.target.value
      let nbMaxElem = -1
      let tab = [] // Array containing the values for the inputs
      // Determine which delimiter we should consider
      for (let delimiter of delimiters) {
        let temp = text.split(delimiter)
        if (temp.length > nbMaxElem) {
          tab = temp
          nbMaxElem = temp.length
        }
      }
      // Fill the inputs until the tab is empty
      let nRef = {}
      let di = this.mlvadata.indexOf(e.target.name)
      for (let i = 0; i < this.mlvadata.length; i++) {
        let bd = this.mlvadata[i]
        nRef[bd] = i < di
          ? this.form.ref[bd]
          : nRef[bd] = tab[i - di] || this.form.ref[bd]
      }
      this.form.ref = nRef
    },
    onSubmit () {
      this.$store.commit('query', this.form)
      this.$router.push('/')
    }
  }
}
</script>

<style lang="css">
</style>
