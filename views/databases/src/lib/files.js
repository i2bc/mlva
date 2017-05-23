export function downloadFile (filename, filetype, content) {
  let a = document.createElement('a')
  a.href = 'data:attachment/' + filetype + ',' + encodeURIComponent(content)
  a.target = '_blank'
  a.download = filename

  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

export default { download: downloadFile }
