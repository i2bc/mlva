/* global $, baseUrl */
const urlPrefix = baseUrl.replace(/databases\/.*/, '')

export function getRequest (url, data, success, dataType) {
  $.get(urlPrefix + url, data, success, dataType)
}

export function postRequest (url, data, success, dataType) {
  $.post(urlPrefix + url, data, success, dataType)
}

export function redirect (url) {
  window.location.replace(urlPrefix + url)
}

export default {
  get: getRequest,
  post: postRequest
}
