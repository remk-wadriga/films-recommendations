import Vue from 'vue'
import store from "../store";

const User = {
    id: null,
    username: null,
    firstName: null,
    lastName: null,
    age: null,
    sec: null,
    fullName: null,
    isLogged: false,

    init () {
        if (!this.isLogged && store.state.accessToken !== null) {
            Vue.http.get('user').then(response => this.responseSuccessFul(response), errorResponse => this.responseFailed(errorResponse))
        }
    },

    flush () {
        this.id = null
        this.username = null
        this.firstName = null
        this.lastName = null
        this.age = null
        this.sec = null
        this.fullName = null
        this.isLogged = false
    },

    responseSuccessFul(response) {
        if (response.body.id) {
            this.id = response.body.id
            this.username = response.body.username
            this.firstName = response.body.firstName
            this.lastName = response.body.lastName
            this.age = response.body.age
            this.sec = response.body.sec

            this.fullName = this.firstName
            if (this.lastName && this.fullName) {
                this.fullName += ' ' + this.lastName;
            }
            if (!this.fullName) {
                this.fullName = this.username
            }
            this.isLogged = true
        }
    },

    responseFailed(response) {

    }
};

export default User;