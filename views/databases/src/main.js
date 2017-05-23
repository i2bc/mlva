import Vue from 'vue'

import Main from './components/Main.vue'
import Create from './components/Create.vue'

import store from './store'
let data = { store, el: '#app' }

/* global databaseInfos, ownerInfos, panels */
if (databaseInfos) {
  store.dispatch('initialize', { base: databaseInfos, owner: ownerInfos, panels })
  data.render = h => h(Main)
  data.router = require('./router').default
} else {
  store.dispatch('initializeLight')
  data.render = h => h(Create)
}

/* eslint-disable no-new */
new Vue(data)
