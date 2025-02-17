<script setup lang="ts">
import Loading from "./Loading.vue";
import { loading, loadingNow } from "./loading";
import Login from "./Pages/Login.vue";
import Register from "./Pages/Register.vue";
import Lobby from "./Pages/Lobby.vue";
import state from "../state";
import { Page, User } from "../types";
import { getUser } from "../api/user";
import { computed, onMounted } from "vue";
import axios from "axios";
import { fetchLobby } from '../api/lobby';
import { useToast } from 'vue-toast-notification';

axios.defaults.headers.common['X-TOKEN'] = localStorage.getItem('token');

onMounted(() => {
    loading()
    getUser().then((user: User) => {
        state.user = user
        fetchLobby().then((response) => {
            state.lobby.chat_messages = response.chat_messages
            state.page = Page.Lobby
        }).catch(error => {
            useToast({position:'top'}).error(error)
        })
        state.page = Page.Lobby
    }).catch((e) => {
        state.page = Page.Login
    }).finally(() => {
        loading(false)
    })
})

let currentComponent = computed(() => {
    switch (state.page) {
        case Page.Login:
            return Login;
        case Page.Register:
            return Register;
        case Page.Lobby:
            return Lobby;
        case Page.Start:
            return null;
      }
})

</script>

<template>
    <div class="container">
        <transition name="fade" mode="out-in">
            <component :is="currentComponent"/>
        </transition>
        <Loading v-if="loadingNow"/>
    </div>
    <div class="window-size-warning">
        Минимальное разрешение для игры - 1280x720 px
    </div>
</template>

<style lang="scss">
* {
    margin: 0;
    padding: 0;
    user-select: none;
}
body {
    background: black;
    font-family: Figtree, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
    color: white;
    overflow-x: hidden;
    overflow-y: hidden;
}
.container {
    width: 100vw;
    height: 100vh;
}

.window-size-warning {
    z-index: 9999;
    display: none;
    position: absolute;
    top: 0;
    left: 0;
    height: 100vh;
    width: 100vw;
    background: #18181b;
    color: white;
    align-items: center;
    justify-content: center;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

@media (max-height: 719.99px) or (max-width: 1279.99px) {
    .window-size-warning {
        // display: flex;
    }
}
</style>