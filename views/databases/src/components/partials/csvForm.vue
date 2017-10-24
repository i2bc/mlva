<template lang="html">
  <div class="form-group">
    <label for="exampleInputFile">Upload a CSV FIle</label>
    <input type="file" @change="onFile">
    <p v-if="error" class="help-block danger">{{ error }}</p>
  </div>
</template>

<script>
/* global Papa */

export default {
  data () { return { error: '' } },
  methods: {
    onFile (e) {
      let file = e.target.files[0]
      Papa.parse(file, {
        header: true,
        comments: '',
        skipEmptyLines: true,
        encoding: 'ascii',
        complete: ({ data, errors }, file) => {
          if (errors.length > 0) {
            console.warn(errors)
            this.error = 'Something wrong happened'
            return
          }
          this.error = ''
          this.$emit('file', { file, data })
        }
      })
    }
  }
}
</script>
