<template lang="html">
  <div class="container">
    <h4>Geolocalisation</h4>
    <div class="row" id="map"></div>
  </div>
</template>

<script>
import L from 'leaflet'

function createGeoJson (strains) {
  let geoJson = []
  for (let strain of strains) {
    if (strain.metadata['lon'] && strain.metadata['lat']) {
      let lon = +strain.metadata['lon'].replace(',', '.')
      let lat = +strain.metadata['lon'].replace(',', '.')
      geoJson.push({ lon, lat, name: strain.name })
    }
  }
  return geoJson
}

export default {
  data () { return { map: null } },
  computed: {
    strains () { return this.$store.getters.strains }
  },
  mounted () {
    this.map = L.map('map').setView([48.710734, 2.218233], 2)
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
      attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
      maxZoom: 18,
      id: 'mapbox.streets',
      accessToken: 'pk.eyJ1IjoibWx2YSIsImEiOiJjaWsxOXB5azUwMnkzd3JtNWYwcWZpdGg5In0.Yr7v7hBXRndIvOGuzte9aQ'
    }).addTo(this.map)

    for (let marker of createGeoJson(this.strains)) {
      L.marker([marker.lat, marker.lon]).bindPopup('key: ' + marker.name).addTo(this.map)
    }
  }
}
</script>

<style lang="css">
#map { height: 700px; }
</style>
