<template lang="html">
<div class="container">
  <div>
    <form class="form-inline">

      <div class="form-group form-group-sm">
        <div class="input-group">
          <div class="input-group-addon">Strains per page</div>
          <select @change="goTo(1)" v-model="page.perPage" class="form-control">
            <option>50</option>
            <option>100</option>
            <option>200</option>
            <option>500</option>
          </select>
        </div>
      </div>

      <nav v-if="nbPages > 1"><ul class="pagination pagination-sm">
        <li v-if="page.current > 1">
          <a @click="goTo(page.current - 1)" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        <li v-if="page.current > 2"><a @click="goTo(page.current - 2)">{{ page.current - 2 }}</a></li>
        <li v-if="page.current > 1"><a @click="goTo(page.current - 1)">{{ page.current - 1 }}</a></li>
        <li class="active"><a>{{ page.current }}</a></li>
        <li v-if="page.current <= nbPages - 1"><a @click="goTo(page.current + 1)">{{ page.current + 1 }}</a></li>
        <li v-if="page.current <= nbPages - 2"><a @click="goTo(page.current + 2)">{{ page.current + 2 }}</a></li>
        <li v-if="page.current < nbPages">
          <a @click="goTo(page.current + 1)" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul></nav>

      <div class="form-group form-group-sm">
        <div class="input-group">
          <div class="input-group-addon">Go to page</div>
          <select @change="goTo($event.target.value)" class="form-control">
            <option v-for="i in nbPages">{{ i }}</option>
          </select>
        </div>
      </div>

    </form>
  </div>
</div>
</template>

<script>
export default {
  props: ['page', 'total'],
  computed: {
    nbPages () { return 1 + Math.floor((this.total - 1) / this.page.perPage) }
  },
  methods: {
    goTo (i) { this.page.current = Math.min(Math.max(1, i), this.nbPages) }
  }
}
</script>

<style lang="scss">*
.pagination {
  cursor: pointer;
}

.form-inline {
  padding: 20px 0;
  text-align: center;

  nav {
    display: inline-block;
    margin-bottom: 0;
    vertical-align: middle;
    margin-top: 5px;
  }

  .pagination { margin: 0; }

  .form-group {
    margin: 0;
    margin-right: 10px;
  }
}
</style>
