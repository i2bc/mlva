import Request from '@/lib/request'
import { maskGeno } from '@/lib/query'
import { convertStrain } from '@/lib/csv'
import { getGeolocalisation } from '@/lib/utils'

const applyToAll = async function (list, cb) {
  let promises = list.map(async function (...args) {
    return await cb(...args)
  })
  return await Promise.all(promises)
}

const Importer = { applyToAll }

// =============================================================================
//  > Strains
// =============================================================================
const getLocalisation = async function (strain, geolocalisation) {
  if (geolocalisation == null) return strain
  if (strain.metadata[geolocalisation] == null) return strain
  if (strain.metadata['lon'] && strain.metadata['lat']) return strain
  let location = await getGeolocalisation(strain.metadata[geolocalisation])
  if (location) {
    strain.metadata.lon = location.lon
    strain.metadata.lat = location.lat
  }
  return strain
}

Importer.sendStrains = async function (strains, baseId, headers, geolocalisation, oldStrains = []) {
  if (strains.length === 0) return []
  strains = await applyToAll(strains, s => convertStrain(s, headers))
  strains = await applyToAll(strains, s => getLocalisation(s, geolocalisation))
  strains = await Request.postBlob('strains/post/' + baseId, strains)
  return strains
}

Importer.deleteStrains = async function (strains, baseId) {
  if (strains.length === 0) return []
  strains = await Request.postBlob('strains/delete/' + baseId, strains)
  return strains
}

// =============================================================================
//  > Panels & GN
// =============================================================================
Importer.sendPanels = async function (panels, baseId) {
  if (panels.length === 0) return []
  panels = await applyToAll(panels, p => Request.post('panels/make', { baseId, name: p.name, data: p.data }))
  return panels
}

Importer.sendGNs = async function (strains, panels, baseId) {
  if (strains.length === 0 || panels.length === 0) return []
  let genonums = {}
  strains = strains.map(strain => strain.data)
  for (let strain of strains) {
    for (let panel of panels) {
      console.log(strain)
      let gn = strain.other[panel.name]
      if (gn == null || gn.endsWith('temp') || !gn.trim()) continue
      let data = maskGeno(strain.data, panel.data)
      genonums[panel.id] = genonums[panel.id] || []
      genonums[panel.id].push({ value: gn, data })
    }
  }
  for (let panelId in genonums) await Request.postBlob('panels/addGN/' + panelId, genonums[panelId])
  return genonums
}

export default Importer
