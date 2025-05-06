import axios from 'axios'
import {ComponentRequestData} from '../DTOs/ComponentRequestData.js'
import {dispatchEvents} from '../Support/DispatchEvents.js'
import DOMUpdater from '../Support/DOMUpdater.js'

export default async function request(
  t,
  url,
  method = 'get',
  body = {},
  headers = {},
  componentRequestData = {},
) {
  if (!url) {
    t.loading = false
    MoonShine.ui.toast('Request URL not set', 'error')
    return
  }

  if (!navigator.onLine) {
    t.loading = false
    MoonShine.ui.toast('No internet connection', 'error')
    return
  }

  if (!(componentRequestData instanceof ComponentRequestData)) {
    componentRequestData = new ComponentRequestData()
  }

  if (componentRequestData.hasBeforeRequest()) {
    beforeRequest(componentRequestData.beforeRequest, t.$el, t)
  }

  try {
    const response = await axios({
      url: url,
      method: method,
      data: body,
      headers: headers,
      responseType: componentRequestData.responseType,
    }).then(function (response) {
      t.loading = false

      const data = response.data ?? {}
      const contentDisposition = response.headers['content-disposition']

      if (componentRequestData.hasBeforeHandleResponse()) {
        componentRequestData.beforeHandleResponse(data, t)
      }

      if (componentRequestData.hasResponseHandler()) {
        responseHandler(
          componentRequestData.responseHandler,
          response,
          t.$el,
          componentRequestData.events,
          t,
        )

        return
      }

      let htmlData = data.htmlData ?? []

      /**
       * TODO(4.0) remove legacy
       */
      if (data.html !== undefined) {
        htmlData = [{html: data.html}]
      }

      if (componentRequestData.selector && typeof data === 'string') {
        htmlData = [{html: data}]
      }

      DOMUpdater({
        htmlData: htmlData,
        selectors: componentRequestData.selector
          ? componentRequestData.selector.split(',')
          : undefined,
        fields_values: data.fields_values,
      })

      if (data.redirect) {
        window.location.assign(data.redirect)
      }

      if (contentDisposition?.startsWith('attachment')) {
        let fileName = contentDisposition.split('filename=')[1]

        downloadFile(fileName, data)
      }

      const type = data.messageType ? data.messageType : 'success'

      if (data.message) {
        MoonShine.ui.toast(data.message, type, data.messageDuration ?? null)
      }

      const events = data.events ?? componentRequestData.events

      if (events) {
        dispatchEvents(events, type, t, componentRequestData.extraProperties)
      }

      if (componentRequestData.hasAfterResponse()) {
        const afterResponseCallback = componentRequestData.afterResponse(data, type, t)
        afterResponse(afterResponseCallback, data, type, t)
      }
    })
  } catch (errorResponse) {
    t.loading = false

    if (componentRequestData.hasResponseHandler()) {
      responseHandler(
        componentRequestData.responseHandler,
        errorResponse,
        t.$el,
        componentRequestData.events,
        t,
      )
      return
    }

    if (!errorResponse?.response?.data) {
      console.error(errorResponse.message)

      MoonShine.ui.toast('Unknown Error', 'error')
      return
    }

    const data = errorResponse.response.data

    if (componentRequestData.hasErrorCallback()) {
      componentRequestData.errorCallback(data, t)
    }

    MoonShine.ui.toast(data.message ?? data, 'error')
  }
}

export function urlWithQuery(url, append, callback = null) {
  let urlObject = url.startsWith('/') ? new URL(url, window.location.origin) : new URL(url)

  if (callback !== null) {
    callback(urlObject)
  }

  let separator = urlObject.searchParams.size ? '&' : '?'

  return (urlObject.toString() + separator + append).replace(/[?&]+$/, '')
}

function responseHandler(callback, response, element, events, component) {
  if (typeof callback !== 'string') {
    return
  }

  if (callback.trim() === '') {
    return
  }

  const fn = MoonShine.callbacks[callback]

  if (typeof fn !== 'function') {
    MoonShine.ui.toast('Error', 'error')

    throw new Error(callback + ' is not a function!')
  }

  fn(response, element, events, component)
}

export function beforeRequest(callback, element, component) {
  if (typeof callback !== 'string') {
    return
  }

  if (callback.trim() === '') {
    return
  }

  const fn = MoonShine.callbacks[callback]

  if (typeof fn !== 'function') {
    throw new Error(callback + ' is not a function!')
  }

  fn(element, component)
}

export function afterResponse(callback, data, messageType, component) {
  if (typeof callback !== 'string') {
    return
  }

  if (callback.trim() === '') {
    return
  }

  const fn = MoonShine.callbacks[callback]

  if (typeof fn !== 'function') {
    throw new Error(callback + ' is not a function!')
  }

  fn(data, messageType, component)
}

export function initCallback(callback) {
  if (callback === null) {
    return {
      beforeRequest: '',
      responseHandler: '',
      afterResponse: '',
    }
  }
  return callback
}

function downloadFile(fileName, data) {
  const url = window.URL.createObjectURL(new Blob([data]))
  const a = document.createElement('a')
  a.style.display = 'none'
  a.href = url
  a.download = fileName
  document.body.appendChild(a)
  a.click()
  window.URL.revokeObjectURL(url)
}
