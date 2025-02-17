<script setup lang="ts">
import Games from "./Lobby/Games.vue";
import state from "../../state";
import { wsmanager, WsChannel } from "../../ws";
import { onMounted, ref } from "vue";
import { Page } from "../../types";
import { useToast } from "vue-toast-notification";


let online = ref(0);
let chatElement = null

onMounted(() => {
    chatElement = document.querySelector(".chat-area")
    setTimeout(() => {
        chatElement.scrollTop = chatElement.scrollHeight;
    }, 100)
})

wsmanager.subscribe(WsChannel.Lobby, (type: string, payload: any) => {
    if (type == "online") {
        online.value = payload.online;
    }
    if (type == "new_message") {
        state.lobby.chat_messages.push(payload)
        setTimeout(() => {
            chatElement.scrollTop = chatElement.scrollHeight;
        }, 100)
    }
})

wsmanager.subscribe(WsChannel.Main, (type: string, payload: any) => {
    if (type == "already_logged_in") {
        useToast({position:'top'}).error("Этот пользователь уже в игре");
        logout();
    }
})

let logout = function() {
    localStorage.removeItem('token')
    state.page = Page.Login
}


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

</script>

<template>
    <div class="panels">
        <div class="panel games-list">
            <div class="panel-title games-list-title">
                Игры
                <div class="button --main">Создать</div>
            </div>
            <Games />
        </div>
        <div class="panel game">
            <div class="panel-title">
                Игра
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
                        {{ state.user?.rating }}
                    </div>
                </div>
            </div>
            <div class="panel chat">
                <div class="panel-title">
                    Чат
                    <div class="game-online">Онлайн: {{ online }}</div>
                </div>
                <div class="chat-area">
                    <div class="chat-message" v-for="message in state.lobby.chat_messages">
                        <div class="chat-message-time">[{{ message.datetime }}]</div>
                        <div class="chat-message-user">{{ message.user.name }}: </div>
                        {{ message.text }}
                    </div>
                </div>
                <div class="chat-input">
                    <input type="text" class="input-element" v-model="newMessageText" @keyup.enter.prevent.stop="sendMessage" maxLength="46">
                </div>
            </div>
        </div>
    </div>
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

    &::before {
        content: '';
        display: block;
        position: absolute;
        height: calc(100% - 40px);
        border-left: 2px dashed #2f2f2f;
        left: -12px;
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
        }
    }
}
</style>
