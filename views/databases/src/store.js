import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

import Request from 'lib/request'
import { maskGeno, query } from 'lib/query'

const findById = (list, id) => list.indexOf(list.find(a => a.id === id))
const findByName = (list, name) => list.indexOf(list.find(a => a.name === name))
const getNames = (list, f) => list.filter(a => f(a)).map(a => a.name)
// const intersect = (A, B) => ([].concat(A, B))
//   .filter(a => A.indexOf(a) >= 0)
//   .filter(b => B.indexOf(b) >= 0)

// = BASE ==========
const base = {
  state: { id: -1, name: '', state: -1, website: '', description: '', userId: -1, groupId: -1, owner: null },
  mutations: {
    setOwner (state, owner) { state.owner = owner },
    updateBase (state, base) {
      if (base.id) state.id = base.id | 0
      if (base.name) state.name = base.name
      if (base.state) state.state = !!(base.state | 0)
      if (base.userId) state.userId = base.userId | 0
      if (base.user_id) state.userId = base.user_id | 0
      if (base.groupId) state.groupId = base.groupId | 0
      if (base.group_id) state.groupId = base.group_id | 0
      if (base.website) state.website = base.website
      if (base.description) state.description = base.description
    }
  },
  actions: {
    initBase ({ commit }, { base, owner }) {
      commit('setOwner', owner)
      commit('updateBase', base)
    }
  }
}

// = HEADERS ==========
const headers = {
  state: { list: [] },
  mutations: {
    addHeader ({ list }, { name, type }) { list.push({ name, type, visible: true }) },
    addMetadata ({ list }, name) { list.push({ name, type: 'meta', visible: true }) },
    addMlvadata ({ list }, name) { list.push({ name, type: 'mlva', visible: true }) },
    toggleHeader ({ list }, name) {
      let i = findByName(list, name)
      if (i < 0) return
      list[i].visible = !list[i].visible
    }
  },
  actions: {
    initHeaders ({ commit }, { base }) {
      commit('addHeader', { name: 'key', type: '[key]' })
      for (let mlva of base.data || []) commit('addMlvadata', mlva)
      for (let meta of base.metadata || []) commit('addMetadata', meta)
    }
  },
  getters: {
    allMetadata: ({ list }) => getNames(list, h => h.type === 'meta'),
    allMlvadata: ({ list }) => getNames(list, h => h.type === 'mlva'),
    metadata: ({ list }) => getNames(list, h => h.type === 'meta' && h.visible),
    mlvadata: ({ list }, { allMlvadata, panelFilter }) => panelFilter || allMlvadata,
    isKeyVisible: ({ list }) => list[findByName(list, 'key')] && list[findByName(list, 'key')].visible
  }
}

// = PANELS ==========
const panels = {
  state: { panelId: -1, list: [] },
  mutations: {
    addPanel ({ list }, panel) { list.push(Object.assign(panel, { id: panel.id | 0, listGN: [] })) },
    updatePanel ({ list }, panel) { list[findById(list, panel.id)] = panel },
    deletePanel ({ list }, panel) { list.splice(findById(list, panel.id), 1) },
    togglePanel (state, panelId) { state.panelId = state.panelId === panelId ? -1 : panelId },
    addGN ({ list }, { panelId, gn }) {
      let i = findById(list, panelId | 0)
      if (i < 0) return
      list[i].listGN.push(gn)
    },
    updateGN ({ list }, { panelId, oValue, nValue }) {
      let i = findById(list, panelId | 0)
      if (i < 0) return
      let gn = list[i].listGN.find(gn => gn.value === oValue)
      if (!gn) return
      gn.value = nValue
    }
  },
  actions: {
    initPanels ({ commit }, { base, panels }) {
      for (let panel of panels) commit('addPanel', panel)
      Request.get('databases/genonums/' + base.id)
        .then(data => {
          for (let panelId in data) for (let gn of data[panelId]) commit('addGN', { panelId, gn })
        })
    },
    updateGN ({ commit }, { panelId, listGN }) {
      for (let gn of listGN) {
        if (gn.oValue) {
          commit('updateGN', { panelId, gn: { data: gn.data, value: gn.nValue } })
        } else {
          commit('addGN', { panelId, gn: { data: gn.data, value: gn.nValue } })
        }
      }
    }
  },
  getters: {
    currentPanel: ({ list, panelId }) => list[findById(list, panelId)],
    panelFilter: ({ list, panelId }) => findById(list, panelId) >= 0 ? list[findById(list, panelId)].data : null
  }
}

// = STRAINS ==========
function safeSatrain (strain) {
  strain.data = strain.data || {}
  strain.metadata = strain.metadata || {}
  return strain
}

const strains = {
  state: { list: [], query: null },
  mutations: {
    emptyStrains ({ list }) { list.splice(0, list.length) },
    addStrain ({ list }, strain) { list.push(safeSatrain(strain)) },
    updateStrain ({ list }, strain) { list[findById(list, strain.id)] = strain },
    deleteStrain ({ list }, strain) { list.splice(findById(list, strain.id), 1) },
    query (state, { ref, maxDist, maxAmount, uniq }) {
      let nRef = maskGeno(ref)
      state.query = {
        list: query(state.list, nRef, maxDist, maxAmount, uniq),
        ref: nRef
      }
    },
    unquery (state) { state.query = null }
  },
  actions: {
    initStrains ({ commit }, { base }) {
      store.commit('emptyStrains')
      let offset = 0
      let getStrains = function () {
        Request.get('databases/strains/' + base.id + '?offset=' + offset)
          .then(strains => {
            for (let strain of strains) store.commit('addStrain', strain)
            offset += strains.length
            if (strains.length) getStrains()
          })
      }
      getStrains()
    }
  },
  getters: {
    queried: state => state.query != null,
    strains: state => state.query ? state.query.list : state.list
  }
}

// = USER ==========
const user = {
  state: { id: -1, groups: [], authLevel: -1 },
  mutations: {
    setAuthLevel (state, authLevel) { state.authLevel = authLevel },
    setUser (state, { id, groups }) { state.id = id | 0; state.groups = groups }
  },
  actions: {
    initUser ({ commit }, { base }) {
      Request.get('ajax/user').then(userData => store.commit('setUser', userData))
      Request.get('ajax/authLevel/' + base.id).then(({ level }) => store.commit('setAuthLevel', level))
    },
    initLightUser ({ commit }) {
      Request.get('ajax/user').then(userData => store.commit('setUser', userData))
    }
  }
}

const store = new Vuex.Store({
  modules: { headers, panels, base, strains, user },
  actions: {
    initialize ({ commit, dispatch }, data) {
      // console.log(data)
      dispatch('initStrains', data)
      dispatch('initHeaders', data)
      dispatch('initPanels', data)
      dispatch('initBase', data)
      dispatch('initUser', data)
    },
    initializeLight ({ commit, dispatch }) {
      dispatch('initLightUser')
    }
  }
})

export default store
