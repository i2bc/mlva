export function getUrlParams () {
  let url = window.location.href
  // let hash = window.location.hash
  // url = url.replace(hash, '')
  if (url.indexOf('?') >= 0) {
    let params = url.split('?')[1].split('&')
    let paramObj = {}
    for (let param of params) {
      if (param.indexOf('=') >= 0) {
        paramObj[param.split('=')[0]] = param.split('=')[1]
      } else {
        paramObj[param] = true
      }
    }
    return paramObj
  }
  return {}
}

export function setUrlParams (params = {}) {
  let url = window.location.href
  // let hash = window.location.hash
  url = url
    // .replace(hash, '')
    .split('?')[0]
  let list = []
  for (let paramName in params) {
    list.push(params[paramName] !== true
      ? paramName + '=' + params[paramName]
      : paramName
    )
  }
  let paramString = list.length > 0
    ? '?' + list.join('&')
    : ''
  window.history.pushState({}, '', url + paramString)
  // window.history.pushState({}, '', url + paramString + hash)
}

export function getUrlParam (paramName, defaultValue = null) {
  return getUrlParams()[paramName] || defaultValue
}

export function setUrlParam (paramName, paramValue) {
  let params = getUrlParams()
  params[paramName] = paramValue
  setUrlParams(params)
}

export function unsetUrlParam (paramName) {
  let params = getUrlParams()
  delete params[paramName]
  setUrlParams(params)
}

export default {
  getAll: getUrlParams,
  setAll: setUrlParams,
  unset: unsetUrlParam,
  get: getUrlParam,
  set: setUrlParam
}
