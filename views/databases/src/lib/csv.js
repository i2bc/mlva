import store from '../store'
import { getGN } from './genonums'
import { getGeolocalisation } from './utils'

export function makeArray (metadata, panelId, advanced, tempGN) {
  // Setup ~
  const rows = []
  let mlva, meta, panel
  const strains = store.getters.strains
  let mlvadata
  let panels = store.state.panels.list
  if (panelId === -1) {
    mlvadata = store.getters.allMlvadata
  } else if (panelId === -2) {
    mlvadata = []
    panels = []
  } else {
    let panel = panels.find(p => p.id === panelId)
    mlvadata = panel.data
    panels = [panel]
  }
  if (!advanced) {
    panels = []
  }
  // Header ~
  rows.push(['key'].concat(metadata, panels.map(p => p.name), mlvadata))
  if (advanced) {
    // Struct ~
    let row = ['[key]']
    for (meta of metadata) row.push('info')
    for (panel of panels) row.push('GN')
    for (mlva of mlvadata) row.push('mlva')
    rows.push(row)
    // Panels ~
    for (let aPanel of panels) {
      let row = ['[panel] ' + aPanel.name]
      for (meta of metadata) row.push('')
      for (panel of panels) row.push(panel.name === aPanel.name ? 'GN' : '')
      for (mlva of mlvadata) row.push(aPanel.data.includes(mlva) ? 'X' : '')
      rows.push(row)
    }
  }
  // Strains ~
  for (let strain of strains) {
    let row = [strain.name]
    for (meta of metadata) row.push(strain.metadata[meta] || '')
    for (panel of panels) { let gn = getGN(panel, strain, tempGN); row.push(gn ? gn.value : '') }
    for (mlva of mlvadata) row.push(strain.data[mlva] || '')
    rows.push(row)
  }
  return rows
}

export function convertStrain (row, headers, strain = null) {
  strain = strain || { name: null, data: {}, metadata: {} }
  for (let header of headers) {
    if (header.type === 'key') strain.name = row[header.import]
    if (header.type === 'mlva') strain.data[header.name] = row[header.import] || ''
    if (header.type === 'info') strain.metadata[header.name] = row[header.import] || ''
  }
  return strain
}

export function convertPanel (row) {
  return {
    name: row.key.replace(/^\[panel]\s*/, ''),
    data: Object.keys(row).filter(h => row[h] === 'X')
  }
}

export function convertHeaders (row) {
  let key = Object.keys(row).find(h => h.toLowerCase() === 'key')
  if (key && row[key] === '[key]') {
    return Object.keys(row).map(head => {
      return { name: head, import: head, type: head === key ? 'key' : row[head] }
    })
  } else {
    return Object.keys(row).map((head, i) => {
      return { name: head, import: head, type: i ? 'ignore' : 'key' }
    })
  }
}

export function setLocation (strain, locationHeader) {
  let location = getGeolocalisation(strain.metadata[locationHeader])
  strain.metadata.lon = location.lon
  strain.metadata.lat = location.lat
  return strain
}
