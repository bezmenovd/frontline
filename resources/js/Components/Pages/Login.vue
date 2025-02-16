<script setup lang="ts">
import { login } from '../../api/user';
import state from '../../state';
import { Page, User } from '../../types';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import { loading } from '../loading';
import axios from 'axios';


let data = {
    name: "",
    password: "",
}

let submit = function() {
    if (data.name.length == 0 || data.password.length == 0) {
        return
    }

    loading()

    login(data.name, data.password).then((r) => {
        localStorage.setItem('token', r.token)
        axios.defaults.headers.common['X-TOKEN'] = r.token;
        state.user = r.user
        state.page = Page.Lobby
    }).catch((r:{error:string}) => {
        useToast({position:'top'}).error(r.error)
    }).finally(() => {
        loading(false)
    })
}

let goToRegister = function() {
    state.page = Page.Register
}

</script>

<template>
    <div class="panel login-panel">
        <div class="panel-title">Авторизация</div>
        <div class="form" @keyup.enter.stop.prevent="submit">
            <div class="input-group">
                <div class="input-title">Имя пользователя</div>
                <input class="input-element" type="text" spellcheck="false" maxlength="26" v-model="data.name">
            </div>
            <div class="input-group">
                <div class="input-title">Пароль</div>
                <input class="input-element" type="password" spellcheck="false" maxlength="26" v-model="data.password">
            </div>
        </div>
        <div class="buttons">
            <div class="button --text" @click="goToRegister">Регистрация</div>
            <div class="button --main" @click="submit">Войти</div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.login-panel {
    width: 400px;
    height: 400px;
    top: calc(50% - 200px);
    left: calc(50% - 200px);
    position: relative;
    display: grid;
    grid-template-rows: 40px 1fr 40px;
    gap: 40px;
    font-size: 18px;
}
.panel-title {
    justify-content: center;
}
.input-element {
    font-family: monospace;
}
.input-group:not(:first-of-type) {
    margin-top: 20px;
}
.buttons {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
