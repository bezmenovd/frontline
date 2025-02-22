<script setup lang="ts">
import { register } from '../../api/user';
import state from '../../state';
import { Host, Page, User } from '../../types';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import { loading } from '../loading';
import axios from 'axios';
import { fetchLobby } from '../../api/lobby';


let data = {
    name: "",
    email: "",
    password: "",
    passwordRepeat: "",
}

let allowSubmit = true;
let submit = function() {
    if (!allowSubmit) {
        return
    }
    if (data.name.length == 0 || data.password.length == 0 || data.password.length == 0) {
        return
    }
    if (data.password !== data.passwordRepeat) {
        useToast({position:'top'}).error("Пароли не совпадают")
        return
    }

    allowSubmit = false
    setTimeout(() => {
        allowSubmit = true
    }, 3000)

    loading()

    register(data.name, data.email, data.password).then((r) => {
        localStorage.setItem('token', r.token)
        axios.defaults.headers.common['X-TOKEN'] = r.token;
        state.user = r.user
        
        fetchLobby().then((response) => {
            state.lobby.chatMessages = response.chatMessages
            state.lobby.hosts.list = response.hosts
            state.page = Page.Lobby
        }).catch(error => {
            useToast({position:'top'}).error(error)
        }).finally(() => {
            loading(false)
        })
    }).catch((error: string) => {
        useToast({position:'top'}).error(error)
        loading(false)
    })
}

</script>

<template>
    <div class="panel login-panel">
        <div class="panel-title">Регистрация</div>
        <div class="form">
            <div class="input-group">
                <div class="input-title">Имя пользователя</div>
                <input class="input-element" type="text" spellcheck="false" maxlength="18" v-model="data.name" autofocus="true">
            </div>
            <div class="input-group">
                <div class="input-title">Электронная почта</div>
                <input class="input-element" type="email" spellcheck="false" maxlength="26" v-model="data.email">
            </div>
            <div class="input-group">
                <div class="input-title">Пароль</div>
                <input class="input-element" type="password" spellcheck="false" maxlength="26" v-model="data.password">
            </div>
            <div class="input-group">
                <div class="input-title">Повторите пароль</div>
                <input class="input-element" type="password" spellcheck="false" maxlength="26" v-model="data.passwordRepeat">
            </div>
        </div>
        <div class="buttons">
            <div class="button --text" @click="state.page = Page.Login">Авторизация</div>
            <div class="button --main" @click="submit">Зарегистрироваться</div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.login-panel {
    width: 400px;
    height: 560px;
    top: calc(50% - 280px);
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
