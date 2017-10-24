import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)

/* global baseUrl, databaseInfos */
let base = baseUrl.replace(/^https?:\/\/[\w\d.-]+/, '') + 'databases/view/' + databaseInfos.id

export default new VueRouter({
  base,
  mode: 'history',
  routes: [
    { path: '/map', component: require('./components/pages/map.vue') },
    { path: '/tree', component: require('./components/pages/tree.vue') },
    { path: '/edit', component: require('./components/pages/edit.vue') },
    { path: '/query', component: require('./components/pages/query.vue') },
    { path: '/import', component: require('./components/pages/import.vue') },
    { path: '/delete', component: require('./components/pages/delete.vue') },
    { path: '/matrix', component: require('./components/pages/matrix.vue') },
    { path: '/exportCSV', component: require('./components/pages/export.vue') },
    { path: '/editPanels', component: require('./components/pages/panels.vue') },
    { path: '*', component: require('./components/pages/view.vue') }
  ]
})
