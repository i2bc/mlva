import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)

/* global baseUrl, databaseInfos */
let base = baseUrl.replace(/^https?:\/\/[\w\d.-]+/, '') + 'databases/view/' + databaseInfos.id

export default new VueRouter({
  base,
  mode: 'history',
  routes: [
    { path: '/map', component: require('./components/pages/map.vue').default },
    { path: '/tree', component: require('./components/pages/tree.vue').default },
    { path: '/edit', component: require('./components/pages/edit.vue').default },
    { path: '/query', component: require('./components/pages/query.vue').default },
    { path: '/import', component: require('./components/pages/import.vue').default },
    { path: '/delete', component: require('./components/pages/delete.vue').default },
    { path: '/matrix', component: require('./components/pages/matrix.vue').default },
    { path: '/exportCSV', component: require('./components/pages/export.vue').default },
    { path: '/editPanels', component: require('./components/pages/panels.vue').default },
    { path: '*', component: require('./components/pages/view.vue').default }
  ]
})
