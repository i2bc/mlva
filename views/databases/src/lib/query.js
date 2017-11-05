const ignoreValues = ['', -1, undefined]

export function maskGeno (data, mask = null) {
  let nData = {}
  for (let mlva in data) {
    if (ignoreValues.includes(data[mlva])) continue
    if (mask && !mask.includes(mlva)) continue
    nData[mlva] = data[mlva]
  }
  return nData
}

export function getMask (data) {
  return Object.keys(maskGeno(data))
}

export function distanceGeno (ref, data, ignore = false) {
  let d = 0
  for (let mlva in ref) {
    if (data[mlva] == null) {
      d += ignore ? 0 : 1
    } else {
      d += !(data[mlva] == ref[mlva])
    }
  }
  return d
}

export function isGNinList (list, data) {
  let p = 1
  for (let other of list) p *= distanceGeno(other, data)
  return p === 0 // there is a null distance
}

export function query (strains, ref, maxDist, maxAmount, uniq) {
  let mask = getMask(ref)
  let list = []
  for (let strain of strains) {
    let data = maskGeno(strain.data, mask)
    // if (list.length >= maxAmount) break
    let deltaDist = distanceGeno(ref, data)
    if (deltaDist <= maxDist) {
      if (uniq) {
        if (isGNinList(list.map(o => maskGeno(o.data, mask)), data)) continue
      }
      strain.deltaDist = deltaDist
      list.push(strain)
    }
  }
  if (list.length >= maxAmount) {
    list.sort((strainA, strainB) => strainA.deltaDist - strainB.deltaDist)
    list = list.slice(0, maxAmount)
  }
  return list
}
