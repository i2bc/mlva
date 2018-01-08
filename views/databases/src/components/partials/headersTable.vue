<template lang="html">
  <table class="table table-condensed table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th class="rotate" v-if="showKey"><div><span>Key - Name</span></div></th>
        <th class="rotate"><div>Information</div></th>
        <th class="rotate"><div>MLVA Data</div></th>
        <th class="rotate"><div>Ignore</div></th>
        <th></th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="header in headers">
        <th><input type="text" class="form-control input-sm" v-model="header.name" /></th>
        <th class="marker" v-if="showKey"><input type="radio" value="key" v-model="header.type" @click="setKey(header)"></th>
        <th class="marker"><input type="radio" value="info" v-model="header.type"></th>
        <th class="marker"><input type="radio" value="mlva" v-model="header.type"></th>
        <th class="marker"><input type="radio" value="ignore" v-model="header.type"></th>
        <th class="marker"><span @click="extendFrom(header)" class="glyphicon glyphicon-circle-arrow-down"></span></th>
      </tr>
      <tr>
        <th><input type="text" class="form-control input-sm" v-model="nHeader.name" /></th>
        <th class="marker" v-if="showKey"><input type="radio" value="key" v-model="nHeader.type" @click="setKey(header)"></th>
        <th class="marker"><input type="radio" value="info" v-model="nHeader.type"></th>
        <th class="marker"><input type="radio" value="mlva" v-model="nHeader.type"></th>
        <th class="marker"><input type="radio" value="ignore" v-model="nHeader.type"></th>
        <th class="marker"><span @click="addNewHeader" class="glyphicon glyphicon-plus-sign"></span></th>
      </tr>
    </tbody>
  </table>
</template>

<script>
const emptyHeader = () => { return { name: '', import: '', type: 'ignore' } }

export default {
  props: {
    headers: { type: Array, required: true },
    showKey: { type: Boolean, default: true }
  },
  data () {
    return { nHeader: emptyHeader() }
  },
  methods: {
    extendFrom (header) {
      if (header.type === 'key') return
      for (let i = this.headers.indexOf(header); i < this.headers.length; i++) {
        this.headers[i].type = header.type
      }
    },
    setKey (nHeader) {
      for (let header of this.headers.filter(h => h.type === 'key')) {
        if (nHeader === header) continue
        header.type = 'ignore'
      }
    },
    addNewHeader () {
      this.headers.push(this.nHeader)
      this.nHeader = emptyHeader()
    }
  }
}
</script>

<style lang="scss" scoped>
.marker {
  width: 40px;
  text-align: center;
  vertical-align: middle;
}

.rotate {
  cursor: pointer;
  white-space: nowrap;
  vertical-align: unset;
  text-align: -webkit-center;

  border-left: 1px solid #ccc;

  & > div {
    // writing-mode: vertical-rl;
    // text-orientation: upright;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    letter-spacing: 0.2em;
    margin-bottom: 0.5rem;
  }
}
</style>
