import store from '../store'
import { ADD_LOGGER_MESSAGE_MUTATION } from '@/store/mutation-types'

const Logger = {
    add (text, type = 'info') {
        store.commit(ADD_LOGGER_MESSAGE_MUTATION, {type, text})
    }
};

export default Logger;
