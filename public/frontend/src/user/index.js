import Vue from 'vue'
import store from "../store";

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

    init () {
        if (!this.isLogged && store.state.accessToken !== null) {
            Vue.http.get('user').then(response => this.responseSuccessFul(response), errorResponse => this.responseFailed(errorResponse))
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
        if (response.body.id) {
            this.id = response.body.id
            this.email = response.body.email
            this.firstName = response.body.firstName
            this.lastName = response.body.lastName
            this.age = response.body.age
            this.sex = response.body.sex
            this.aboutMe = response.body.aboutMe

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