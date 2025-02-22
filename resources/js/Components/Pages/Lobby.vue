<script setup lang="ts">
import Hosts from "./Lobby/Hosts.vue";
import HostComponent from "./Lobby/Host.vue";
import state from "../../state";
import { wsmanager, WsChannel } from "../../ws";
import { onMounted, reactive, ref, watch } from "vue";
import { ChatMessage, Host, Page, User } from "../../types";
import { useToast } from "vue-toast-notification";
import Modal from "../Shared/Modal.vue";
import Selector from "../Shared/Selector.vue";
import { loading } from "../loading";
import alert from "../../shared/alert";



let logout = function() {
    localStorage.removeItem('token')
    window.location.href = window.location.href
}

let online = ref(0);
let chatElement : HTMLElement | null = null

onMounted(() => {
    chatElement = document.querySelector(".chat-area")
    setTimeout(() => {
        if (chatElement !== null) {
            chatElement.scrollTop = chatElement.scrollHeight;
        }
    }, 100)
})


let subscribeToHost = () => {
    wsmanager.subscribe(WsChannel.Host, (type: string, payload: any) => {
        if (type === "user_joined") {
            let info = <{
                message: ChatMessage,
                user: {
                    id: number,
                    name: string,
                }
            }>payload
            state.lobby.hosts.connected?.chatMessages.push(info.message)
            state.lobby.hosts.connected?.users.push(info.user)
        }
        if (type === "user_left") {
            let data = <{
                user_id: number,
                message: ChatMessage,
            }>payload
            if (state.lobby.hosts.connected !== undefined) {
                state.lobby.hosts.connected.chatMessages.push(data.message)
                state.lobby.hosts.connected.users = state.lobby.hosts.connected.users.filter((u: {id: number, name: string}) => u.id !== data.user_id)
            }
            if (data.user_id === state.user.id) {
                state.lobby.hosts.connected = undefined
                state.lobby.hosts.selected = undefined
                loading(false)
            }
        }
        if (type === "new_message") {
            let newMessage = <ChatMessage>payload
            state.lobby.hosts.connected?.chatMessages.push(newMessage);
        }
    })
}

wsmanager.subscribe(WsChannel.Lobby, (type: string, payload: any) => {
    if (type === "online") {
        let data = <{
            online: number
        }>payload
        online.value = data.online;
    }
    if (type === "new_message") {
        let newMessage = <ChatMessage>payload
        state.lobby.chatMessages.push(newMessage)
        setTimeout(() => {
            if (chatElement !== null) {
                chatElement.scrollTop = chatElement.scrollHeight;
            }
        }, 100)
    }
    if (type === "new_host") {
        let newHost = <Host>payload;
        state.lobby.hosts.list.push(newHost);

        if (newHost.user.id === state.user.id) {
            loading(false)
            state.lobby.hosts.selected = newHost
            state.lobby.hosts.connected = newHost
            subscribeToHost()
        }
    }
    if (type === "connected_to_host") {
        loading(false)
        let connectedHost = <Host>payload;
        state.lobby.hosts.selected = connectedHost
        state.lobby.hosts.connected = connectedHost
        let connectedHostInList = state.lobby.hosts.list.find((h: Host) => h.id === connectedHost.id);
        if (connectedHostInList !== undefined) {
            Object.assign(connectedHostInList, connectedHost)
        }
        subscribeToHost()
    }
    if (type === "host_deleted") {
        let deletedHostId = <number>payload
        state.lobby.hosts.list = state.lobby.hosts.list.filter((h: Host) => h.id !== deletedHostId)
        if (state.lobby.hosts.selected && state.lobby.hosts.selected.id == deletedHostId) {
            state.lobby.hosts.selected = undefined
        }
        if (state.lobby.hosts.connected && state.lobby.hosts.connected.id == deletedHostId) {
            if (state.lobby.hosts.connected.user.id === state.user.id) {
                loading(false)
            } else {
                alert("Ошибка", "Хост покинул игру");
            }
            state.lobby.hosts.connected = undefined
        }
    }
    if (type === "host_updated") {
        let newHostState = <Host>payload
        let currentHost = state.lobby.hosts.list.find((h: Host) => h.id === newHostState.id);
        if (currentHost !== undefined) {
            Object.assign(currentHost, newHostState)
            if (state.lobby.hosts.selected?.id === newHostState.id) {
                Object.assign(state.lobby.hosts.selected, newHostState)
            }
            if (state.lobby.hosts.connected?.id === newHostState.id) {
                Object.assign(state.lobby.hosts.connected, newHostState)
            }
        }
    }
})

wsmanager.subscribe(WsChannel.Main, (type: string, payload: any) => {
    if (type === "already_logged_in") {
        alert("Ошибка", "Пользователь уже авторизован", () => {
            logout()
        })
    }
})


let newMessageText = ref("");

let sendMessage = function() {
    if (newMessageText.value.length == 0) {
        return;
    }
    wsmanager.send(WsChannel.Lobby, "new_message", {
        text: newMessageText.value
    })
    newMessageText.value = "";
}


let newHost = reactive({
    showModal: false,
    data: {
        description: "",
        players: 2,
    },
    cancel() {
        this.showModal = false;
        this.data.description = "";
        this.data.players = 2;
    },
    send() {
        this.showModal = false
        loading()
        wsmanager.send(WsChannel.Lobby, "new_host", {
            description: this.data.description,
            players: this.data.players,
        });
    }
})

let connectToHost = function(host: Host) {
    loading()
    wsmanager.send(WsChannel.Lobby, "connect_to_host", {
        id: host.id
    });
}

let deleteHost = function() {
    loading()
    wsmanager.send(WsChannel.Host, "delete_host", {})
}

let leaveHost = function() {
    loading()
    wsmanager.send(WsChannel.Host, "leave_host", {})
}

watch([() => state.lobby.hosts.connected?.size, () => state.lobby.hosts.connected?.water], () => {
    wsmanager.send(WsChannel.Host, "update_host", state.lobby.hosts.connected)
})

</script>

<template>
    <div class="panels">
        <div class="panel games-list">
            <div class="panel-title games-list-title">
                Игры
                <div v-if="state.lobby.hosts.connected === undefined" class="button --main" @click="newHost.showModal = true">Создать</div>
            </div>
            <Hosts v-model="state.lobby.hosts.selected"/>
        </div>
        <div class="panel game">
            <div class="panel-title">
                Игра
                <div v-if="state.lobby.hosts.selected !== undefined && state.lobby.hosts.connected === undefined && state.lobby.hosts.selected.users.length < state.lobby.hosts.selected.players" class="button --main" @click="connectToHost(state.lobby.hosts.selected)" style="margin-left: auto">Присоединиться</div>
            </div>
            <div class="game-info">
                <template v-if="state.lobby.hosts.selected == null && state.lobby.hosts.connected == null">
                    <div class="game-no-selected-host">Выберите игру или создайте новую</div>
                </template>
                <template v-else>
                    <HostComponent />
                </template>
            </div>
            <div class="buttons game-buttons">
                <div v-if="state.lobby.hosts.connected?.user.id === state.user.id" class="button --secondary" @click="deleteHost()">Удалить и выйти</div>
                <div v-if="state.lobby.hosts.connected && state.lobby.hosts.connected?.user.id !== state.user.id" class="button --secondary" @click="leaveHost()">Выйти</div>
                <div v-if="state.lobby.hosts.connected?.user.id === state.user.id" class="button --main" @click="">Начать</div>
            </div>
        </div>
        <div class="right-panel">
            <div class="panel player-panel">
                <div class="player-panel-info">
                    <div class="player-panel-info-item --name">
                        <img src="/public/icons/user.png">
                        {{ state.user?.name }}
                        <img src="/public/icons/logout.png" class="logout" @click="logout">
                    </div>
                    <div class="player-panel-info-item --rating">
                        <img src="/public/icons/star.png">
                        {{ state.user?.rating ?? 0 }}
                    </div>
                </div>
            </div>
            <div class="panel chat">
                <div class="panel-title">
                    Общий чат
                    <div class="game-online">Онлайн: <span style="font-family: monospace">{{ online }}</span></div>
                </div>
                <div class="chat-area">
                    <div class="chat-message" v-for="message in state.lobby.chatMessages">
                        <div class="chat-message-time">[{{ message.datetime }}]</div>
                        <template v-if="message.user.id > 0">
                            <div class="chat-message-user">{{ message.user.name }}</div>: 
                        </template>
                        {{ message.text }}
                    </div>
                </div>
                <div class="chat-input">
                    <input type="text" class="input-element" v-model="newMessageText" @keyup.enter.prevent.stop="sendMessage" maxLength="46" placeholder="Для отправки сообщения нажмите Enter">
                </div>
            </div>
        </div>
    </div>

    <Modal title="Создать новую игру" v-if="newHost.showModal">
        <template #body>
            <div class="form" @keyup.enter.stop.prevent style="margin-bottom: 20px">
                <div class="input-group">
                    <div class="input-title">Описание</div>
                    <input class="input-element" type="text" spellcheck="false" size="36" maxlength="36" v-model="newHost.data.description" autofocus>
                </div>
                <div class="input-group">
                    <div class="input-title">
                        Игроки
                    </div>
                    <Selector :values="[2,4,8,16]" :default="2" v-model="newHost.data.players"/>
                </div>
            </div>
        </template>
        <template #buttons>
            <div class="button --secondary" @click="newHost.cancel()">Закрыть</div>
            <div class="button --main" @click="newHost.send()">Создать</div>
        </template>
    </Modal>
</template>

<style lang="scss">
.panels {
    padding: 20px;    
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    height: 100vh;
    box-sizing: border-box;
}
.right-panel {
    display: grid;
    grid-template-rows: 100px 1fr;
    gap: 20px;
    max-height: calc(100vh - 40px);
}
.player-panel {
    max-height: unset;

    &-info {
        display: grid;
        grid-template-rows: 1fr 1fr;
        gap: 10px;

        &-item {
            display: flex;
            align-items: center;
            gap: 10px;

            &.--name {
                font-size: 14px;
                font-family: monospace;
                & img {
                    filter: invert(1);
                    opacity: .5;
                    width: 18px;
                    height: 18px;

                    &.logout {
                        position: relative;
                        left: 5px;
                        cursor: pointer;
                        opacity: .4;

                        &:hover {
                            opacity: 1;
                        }
                    }
                }
            }

            &.--rating {
                font-size: 14px;
                font-family: monospace;
                gap: 6px;
                font-weight: 600;

                & img {
                    filter: invert(1);
                    opacity: 0.5;
                    width: 22px;
                    height: 22px;
                    position: relative;
                    left: -2px;
                    top: -1px;
                }
            }
        }
    }
}
.games-list {
    display: grid;
    grid-template-rows: 38px 1fr;
    gap: 20px;
    max-height: calc(100vh - 40px);

    &-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
}
.game {
    position: relative;
    max-height: calc(100vh - 40px);
    overflow: visible;
    display: grid;
    grid-template-rows: 40px 1fr 40px;
    gap: 20px;

    &::before {
        content: '';
        display: block;
        position: absolute;
        height: calc(100% - 40px);
        border-left: 2px dashed #2f2f2f;
        left: -12px;
        top: 20px;
    }

    &-no-selected-host {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: .5;
    }

    &-info {
    }
}
.chat {
    max-height: calc(100vh - 160px);
    display: grid;
    grid-template-rows: 40px 1fr 40px;
    gap: 10px;

    & .panel-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    & .game-online {
        font-size: 14px;
    }

    &-area {
        background: #0f0f0f;
        border: 1px solid rgba(229, 231, 235, 0.1450980392);
        overflow-y: auto;
        // max-height: calc(100% - 100px);
    }
    &-message {
        font-family: monospace;
        user-select: text !important;
        font-size: 14px;
        margin-bottom: 3px;
        overflow-wrap: break-word;

        &-time {
            display: inline;
            opacity: .6;
        }

        &-user {
            margin-left: 3px;
            display: inline;
            font-size: 14px;
            opacity: .8;

            &:hover {
                text-decoration: underline;
                cursor: pointer;
            }
        }
    }
}
</style>
