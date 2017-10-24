<template lang="html">
  <div class="container">
    <h4>Newick tree</h4>

    <br>

    <div class="col-sm-12">
    	<div id="svgCanvas"></div>

      <br>

      <div class="row">
        <div class="col-sm-5 col-xs-12">
          <div class="form-group">
            <label for="panel">Shape</label>
            <select class="form-control" v-model="shape">
              <option value="radial">Radial</option>
              <option value="diagonal">Diagonal</option>
              <option value="circular">Circular</option>
              <option value="rectangular">Rectangular</option>
              <option value="hierarchical">Hierarchical</option>
            </select>
          </div>
        </div>

        <div class="col-sm-5 col-xs-12">
          <div class="form-group">
            <label for="panel">Labels</label>
            <select class="form-control" v-model="label">
              <option value="[key]">Key</option>
              <option v-for="md in metadata" :value="md">{{ md }}</option>
            </select>
          </div>
        </div>

        <div class="col-sm-2 col-xs-12">
          <label for="panel">Download</label>
          <div class="form-group">
            <a @click.prevent="download" class="btn btn-large btn-primary">As PNG</a>
          </div>
        </div>
      </div>

      <br><br>

    	<div class="form-group">
    		<label for="code">Newick Tree code</label>
    		<textarea class="form-control" rows="3" name="code">{{ newickTree }}</textarea>
    	</div>

    	<div class="col-xs-12">
    		<!-- <a target="_blank" href="http://cgi-www.cs.au.dk/cgi-chili/phyfi/go" class="btn btn-large btn-primary">Phyfi Website</a> -->
    		<!-- <a target="_blank" href="http://www.trex.uqam.ca/index.php?action=newick" class="btn btn-large btn-primary">UQAM Website</a> -->
    		<a target="_blank" href="http://etetoolkit.org/treeview/" class="btn btn-large btn-primary">ETE Website</a>
    	</div>
    </div>
  </div>
</template>

<script>
import { getNewickTree } from '../../lib/newick'
import { downloadRawFile } from '../../lib/files'
import Phylocanvas from 'phylocanvas'

export default {
  data () {
    return {
      newickTree: '',
      shape: 'rectangular',
      label: '[key]',
      keys: [],
      tree: null
    }
  },
  computed: {
    strains () { return this.$store.getters.strains },
    metadata () { return this.$store.getters.metadata }
  },
  mounted () {
    let ref = { name: 'Query', data: this.$store.state.strains.query.ref }
    this.newickTree = getNewickTree([ref, ...this.strains])
    this.tree = Phylocanvas.createTree('svgCanvas')
    this.tree.setTreeType('rectangular')
    this.tree.load(this.newickTree)
    for (let i = 0; i < this.tree.leaves.length; i++) {
      this.keys[i] = this.tree.leaves[i].label
    }
    this.tree.draw()
  },
  watch: {
    shape (val) { this.tree.setTreeType(val) },
    label (val) {
      for (let i = 0; i < this.tree.leaves.length; i++) {
        if (this.keys[i] === 'Query') {
          this.tree.leaves[i].label = 'Query'
        } else {
          this.tree.leaves[i].label = val === '[key]'
            ? this.keys[i]
            : this.strains.find(s => s.name === this.keys[i]).metadata[val]
        }
      }
      this.tree.draw()
    }
  },
  methods: {
    download () {
      let canvas = document.getElementById('svgCanvas__canvas')
      let image = canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream')
      downloadRawFile('query three.png', image)
    }
  }
}
</script>

<style lang="css" scoped>
#svgCanvas { background: #eee; }
</style>
