import Vue from 'vue'
import store from '../store'
import Config from '@/config.js'
import logger from '@/logger'
import { SET_ACCESS_TOKEN_MUTATION, UNSET_ACCESS_TOKEN_MUTATION } from '@/store/mutation-types'
import { LOGIN_URL, LOGOUT_URL, REGISTRATION_URL, RENEW_TOKEN_URL } from './request-urls'

const Api = {
    driver: null,
    baseUrl: Config.api.baseUrl,
    tokenType: 'Bearer',
    authorizationHeaderKey: 'Authorization',
    accessToken: null,
    renewToken: null,
    headers: {
        'Content-Type': 'application/json'
    },
    notFulfillStatuses: [],
    lastRequest: {url: '', data: {}, method: 'GET', headers: {}},

    init () {
        this.driver = Vue.http
        this.accessToken = store.state.accessToken
        this.renewToken = store.state.renewToken

        // Configure HTTP component
        this.driver.options.root = this.baseUrl

        if (this.accessToken !== null) {
            this.driver.headers.common[this.authorizationHeaderKey] = this.tokenType + ' ' + this.accessToken
        }

        Object.keys(this.headers).forEach(key => {
            this.driver.headers.common[key] = this.headers[key]
        })
    },

    request (url, data = {}, headers = {}) {
        this.lastRequest = {url, data, headers}

        let urlParams = {}
        if (typeof url === 'object') {
            urlParams = url[1]
            url = url[0]
        }

        let re1 = new RegExp("(GET|POST|PUT|DELETE) /([[\\w\\-/]+)/:(.+)")
        let re2 = new RegExp("(GET|POST|PUT|DELETE) /([[\\w\\-/]+)")

        let urlData = url.match(re1)
        if (urlData === null) {
            urlData = url.match(re2)
        }

        if (urlData === null) {
            let error = 'Invalid url: "' + url + '"'
            logger.add(error, 'warning')
            return {isOk: false, message: error}
        }

        let method = urlData[1]
        url = urlData[2]
        if (urlData[3] !== undefined && urlParams[urlData[3]] !== undefined) {
            url += '/' + urlParams[urlData[3]]
        }

        return new Promise(resolve => {
            let driver = null
            if (method === 'GET') {
                Object.keys(data).forEach(key => {
                    if (url.indexOf('?') === -1) {
                        url += '?'
                    } else {
                        url += '&'
                    }
                    url += key + '=' + data[key]
                })
                driver = this.driver.get(url)
            } else if (method === 'POST') {
                driver = this.driver.post(url, data)
            } else if (method === 'PUT') {
                driver = this.driver.put(url, data)
            } else if (method === 'DELETE') {
                driver = this.driver.delete(url, data)
            }

            if (driver === null) {
                this.requestFailed(resolve, {message: 'Invalid request method: "' + method + '"'})
            } else {
                driver.then(response => this.requestOk(resolve, response), errorResponse => this.requestFailed(resolve, errorResponse));
            }
        })
    },

    requestOk (resolve, response) {
        if (typeof response.body === 'string') {
            response.body = {message: response.body}
        }
        response.body.isOk = true
        resolve(response.body)
    },

    requestFailed (resolve, response) {
        let re3 = new RegExp("^" + this.baseUrl + "/(\\w+/\\w+/\\w+).*$");
        let re2 = new RegExp("^" + this.baseUrl + "/(\\w+/\\w+).*$");
        let re1 = new RegExp("^" + this.baseUrl + "/(\\w+).*$");
        let matches = response.url.match(re3)
        if (matches === null) {
            matches = response.url.match(re2)
        }
        if (matches === null) {
            matches = response.url.match(re1)
        }
        let url = matches !== null ? matches[1] : ''

        if (typeof response.body === 'string') {
            response.body = {message: response.body}
        }
        response.body.isOk = false
        if (response.body.message === undefined) {
            response.body.message = 'Unknown error'
        }
        if (response.body.code === undefined) {
            response.body.code = 0
        }



        let needToFulfill = true
        this.notFulfillStatuses.forEach(status => {
            if (response.status === status) {
                needToFulfill = false
            }
        })
        if (needToFulfill) {
            if (response.status === 401) {
                if (response.body.code === 1002 && this.renewToken !== null && url !== LOGIN_URL && url !== LOGOUT_URL && url !== REGISTRATION_URL && url !== RENEW_TOKEN_URL && url !== '' && this.lastRequest.url !== null) {
                    this.driver.post(RENEW_TOKEN_URL, {'renew_token': this.renewToken}).then(response => {
                        if (response.body['access_token']) {
                            this.fulfillSuccessfulTokenUpdating(response)
                        } else {
                            this.fulfillNoAuthorizedRequest(response)
                        }
                    }, errorResponse => {
                        this.fulfillNoAuthorizedRequest(errorResponse)
                    })
                } else {
                    this.fulfillNoAuthorizedRequest(response)
                }
            } else {
                logger.add(response.body.message, 'danger')
            }
        }

        this.notFulfillStatuses = []
        resolve(response.body)
    },

    async fulfillSuccessfulTokenUpdating (response) {
        // Remove old tokens
        this.accessToken = null
        this.renewToken = null
        // Set new tokens to local store
        store.commit(SET_ACCESS_TOKEN_MUTATION, {accessToken: response.body['access_token'], renewToken: response.body['renew_token']})
        // Renew self prams
        this.init()

        // Make last request again
        response = await this.request(this.lastRequest.url, this.lastRequest.data, this.lastRequest.headers)
        if (response.isOk) {
            Vue.user.isLogged = true
        }
    },

    fulfillNoAuthorizedRequest (response) {
        logger.add(response.body.message, 'danger')
        store.commit(UNSET_ACCESS_TOKEN_MUTATION)
        this.accessToken = null
        this.renewToken = null
    }
};

export default Api;