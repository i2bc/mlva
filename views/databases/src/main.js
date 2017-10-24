import Vue from 'vue'

import Main from './components/Main.vue'
import Create from './components/Create.vue'

import store from './store'
let data = { store, el: '#app' }

import VeeValidate from 'vee-validate'
Vue.use(VeeValidate, {
  errorBagName: 'formErrors', // change if property conflicts.
  fieldsBagName: 'fields',
  delay: 0,
  locale: 'en',
  dictionary: null,
  strict: true,
  classes: false,
  classNames: {
    touched: 'touched', // the control has been blurred
    untouched: 'untouched', // the control hasn't been blurred
    valid: 'valid', // model is valid
    invalid: 'invalid', // model is invalid
    pristine: 'pristine', // control has not been interacted with
    dirty: 'dirty' // control has been interacted with
  },
  events: 'input|blur',
  inject: true,
  validity: false,
  aria: true
})

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
