<script setup lang="ts">
import Loading from "./Loading.vue";
import { loading, loadingState } from "./loading";
import Login from "./Pages/Login.vue";
import Register from "./Pages/Register.vue";
import Lobby from "./Pages/Lobby.vue";
import Game from "./Pages/Game.vue";
import state from "../state";
import { Host, Page, User } from "../types";
import { getUser } from "../api/user";
import { computed, onMounted, reactive } from "vue";
import axios from "axios";
import { fetchLobby } from '../api/lobby';
import { useToast } from 'vue-toast-notification';
import Modal from "./Shared/Modal.vue";
import { wsmanager } from "../ws";

axios.defaults.headers.common['X-TOKEN'] = localStorage.getItem('token');

window.addEventListener("beforeunload", function () {
    wsmanager.close()
    alert(123)
});

onMounted(() => {
    loading()
    getUser().then((user: User) => {
        state.user = user
        fetchLobby().then((response) => {
            state.lobby.chatMessages = response.chatMessages
            state.lobby.hosts.list = response.hosts
            state.page = Page.Lobby
        }).catch(error => {
            useToast({position:'top'}).error(error)
        }).finally(() => {
            loading(false)
        })
    }).catch((e) => {
        state.page = Page.Login
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
        case Page.Game:
            return Game;
        case Page.Start:
            return null;
    }
})

</script>

<template>
    <div class="container">
        <transition name="fade" mode="out-in">
            <component :is="currentComponent" :key="state.page"/>
        </transition>
        <Loading v-if="loadingState.show" :text="loadingState.text" :opacity="loadingState.opacity"/>


        <Modal :title="state.alert.title" v-if="state.alert.showModal">
            <template #body>
                {{ state.alert.text }}
            </template>
            <template #buttons>
                <div class="button --secondary" @click="state.alert.onClose(); state.alert.showModal = false" style="margin: auto">Закрыть</div>
            </template>
        </Modal>
    </div>
    <div class="window-size-warning">
        Минимальное разрешение для игры - 1280x800px<br>
        При необходимости можно уменьшить масштаб
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
    z-index: 999999;
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
    text-align: center;
    line-height: 30px;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

@media (max-height: 799.99px) or (max-width: 1279.99px) {
    .window-size-warning {
        // display: flex;
    }
}
</style>