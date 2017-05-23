import store from '../store'
import { maskGeno, isGNinList, distanceGeno } from './query'

export function generateTempGN (panelId) {
  let strains = store.state.strains.list
  let panel = store.state.panels.list.find(p => p.id === panelId)
  if (!panel) return []
  let oData = panel.listGN.map(gn => gn.data)
  let nData = []
  for (let strain of strains) {
    let data = maskGeno(strain.data, panel.data)
    if (isGNinList(oData, data)) continue
    if (!isGNinList(nData, data)) nData.push(data)
  }
  let i = panel.listGN.map(gn => gn.value).reduce((a, b) => a > b ? a : b, 1) + ''
  i = i.replace(/\D*/g, '') | 0
  return nData.map(data => { return { value: (++i) + 'temp', data } })
}

export function getGN (panel, strain, temp = true) {
  let data = maskGeno(strain.data, panel.data)
  let gn = panel.listGN.find(gn => !distanceGeno(gn.data, data))
  if (!gn) return null
  if (gn.value.endsWith('temp') && !temp) return null
  return gn
}
