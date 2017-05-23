// = Filters ===

export function autolink (value) {
  return (value || '')
    .replace(/([\w,\sÀ-ÿ-]+)(\(((\w*:\/\/|www\.)[^\s()<>;]+\w)\))/i, '<a href="$3">$1</a>')
    .replace(/([\w\sÀ-ÿ-]+)(\(([\w.\-+]+@[a-z0-9-]+\.[a-z0-9\-.]+[^.,"'?!;:\s])\))/i, '<a href="mailto:$3">$1</a>')
}

// Lighten / Darken Color
// http://stackoverflow.com/questions/5560248/programmatically-lighten-or-darken-a-hex-color
export function LightenDarkenColor (color, percent) {
  let num = parseInt(color, 16)
  let amt = Math.round(2.55 * percent)
  let R = (num >> 16) + amt
  let B = (num >> 8 & 0x00FF) + amt
  let G = (num & 0x0000FF) + amt
  return (0x1000000 + (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 + (B < 255 ? (B < 1 ? 0 : B) : 255) * 0x100 + (G < 255 ? (G < 1 ? 0 : G) : 255)).toString(16).slice(1)
}

/* global $ */
// Get the latitude and longitude from simple location (ex: Paris)
const geoCache = {}
export function getGeolocalisation (location) {
  if (!geoCache[location]) {
    let res
    $.ajax({
      url: 'https://maps.googleapis.com/maps/api/geocode/json',
      data: { key: 'AIzaSyDcBNZnFRG3GNDeC1NkZX4nn4y9pmlu3RM', address: location },
      success (result) { res = result },
      async: false
    })
    if (!res) {
      geoCache[location] = { lat: null, lon: null }
    } else {
      let loc = res.results[0].geometry.location
      geoCache[location] = { lat: loc.lat, lon: loc.lng }
    }
  }
  return geoCache[location]
}
