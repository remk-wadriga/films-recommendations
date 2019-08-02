import Vue from 'vue'
import store from "../store";
import { ACCOUNT_INFO_URL } from '@/api/request-urls'

const User = {
    id: null,
    email: null,
    firstName: null,
    lastName: null,
    age: null,
    sex: null,
    aboutMe: null,
    password: null,
    fullName: null,
    isLogged: false,

    async init () {
        if (!this.isLogged && store.state.accessToken !== null) {
            let response = await Vue.api.get(ACCOUNT_INFO_URL)
            if (response.isOk) {
                this.responseSuccessFul(response)
            } else {
                this.responseFailed(response)
            }
        }
    },

    flush () {
        this.id = null
        this.email = null
        this.firstName = null
        this.lastName = null
        this.age = null
        this.sex = null
        this.aboutMe = null
        this.password = null
        this.fullName = null
        this.isLogged = false
    },

    responseSuccessFul(response) {
        if (response.id) {
            this.id = response.id
            this.email = response.email
            this.firstName = response.firstName
            this.lastName = response.lastName
            this.age = response.age
            this.sex = response.sex
            this.aboutMe = response.aboutMe

            this.fullName = this.firstName
            if (this.lastName && this.fullName) {
                this.fullName += ' ' + this.lastName;
            }
            if (!this.fullName) {
                this.fullName = this.email
            }
            this.isLogged = true
        }
    },

    responseFailed(response) {

    }
};

export default User;