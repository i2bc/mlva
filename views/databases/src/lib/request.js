/* global Blob */
/* global baseUrl */
import axios from 'axios'

const urlPrefix = baseUrl.replace(/databases\/.*/, '')

export function redirect (url) {
  window.location.replace(urlPrefix + url)
}

axios.create({ baseURL: urlPrefix })
export const Request = { baseURL: urlPrefix }

for (let method of ['delete', 'get', 'head', 'options']) {
  Request[method] = (url, config) => new Promise((resolve, reject) => {
    axios[method](Request.baseURL + url, config)
      .then(({ data }) => resolve(data))
      .catch(error => reject(error))
  })
}

for (let method of ['post', 'put', 'patch']) {
  Request[method] = (url, data, config) => new Promise((resolve, reject) => {
    axios[method](Request.baseURL + url, data, config)
      .then(({ data }) => resolve(data))
      .catch(error => reject(error))
  })
}

Request.postBlob = (url, data, config) =>
  Request.post(url, new Blob([JSON.stringify(data)], { type: 'application/json' }), config)

export default Request

export function postRequest (...args) { return Request.post(...args) }
// export function getRequest (...args) { return Request.get(...args) }
