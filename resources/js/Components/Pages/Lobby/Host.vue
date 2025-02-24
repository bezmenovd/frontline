<script setup lang="ts">
import state from '../../../state';
import Selector from "../../Shared/Selector.vue";
import { wsmanager, WsChannel } from "../../../ws";
import { onMounted, ref, watch } from 'vue';


let newMessageText = ref("");

let sendMessage = function() {
    if (newMessageText.value.length == 0) {
        return;
    }
    wsmanager.send(WsChannel.Host, "new_message", {
        text: newMessageText.value
    })
    newMessageText.value = "";
}


let chatElement : HTMLElement | null = null

watch(() => state.lobby.hosts.connected?.chatMessages.length, () => {
    chatElement = document.querySelector(".chat-area")
    setTimeout(() => {
        if (chatElement !== null) {
            chatElement.scrollTop = chatElement.scrollHeight;
        }
    }, 100)
})


</script>

<template>
    <div class="host">
        <div class="host-info">
            <!-- <div class="host-info-user">Хост: <span class="user" style="font-family: monospace;">{{ state.lobby.hosts.selected?.user.name }}</span></div> -->
            <div class="host-info-description"><span style="font-family: monospace;">{{ state.lobby.hosts.selected?.description || 'нет описания' }}</span></div>

            <div class="delimiter"></div>

            <div class="host-parameters">
                <template v-if="state.lobby.hosts.connected?.user.id === state.user.id">
                    <div class="input-group">
                        <div class="input-title">Размер</div>
                        <Selector :values="['64x64', '128x128', '256x256']" v-model="state.lobby.hosts.connected.size" @update:modelValue="$emit('change')"/>
                    </div>
                    <div class="input-group">
                        <div class="input-title">Уровень воды</div>
                        <Selector :values="['низкий', 'средний', 'высокий']" v-model="state.lobby.hosts.connected.water" @update:modelValue="$emit('change')"/>
                    </div>
                </template>
                <template v-else-if="state.lobby.hosts.selected">
                    <div class="input-group">
                        <div class="input-title">Размер</div>
                        <Selector disabled :values="['64x64', '128x128', '256x256']" v-model="state.lobby.hosts.selected.size"/>
                    </div>
                    <div class="input-group">
                        <div class="input-title">Уровень воды</div>
                        <Selector disabled :values="['низкий', 'средний', 'высокий']" v-model="state.lobby.hosts.selected.water"/>
                    </div>
                </template>
            </div>

            <div class="delimiter"></div>

            <div class="host-users">
                <!-- <div class="host-users-title">Игроки:</div> -->
                <div class="table host-users-table">
                    <div class="table-body">
                        <div class="table-body-row with-no-hover" v-for="row in Math.ceil((state.lobby.hosts.selected?.players ?? 1)/4)">
                            <div :class="{'user':true, 'empty':(state.lobby.hosts.selected?.players ?? 0) <= ((row-1)*4), 'bot': state.lobby.hosts.selected?.users[((row-1)*4)] === undefined }">{{ state.lobby.hosts.selected?.users[((row-1)*4)]?.name ?? 'бот' }}</div>
                            <div :class="{'user':true, 'empty':(state.lobby.hosts.selected?.players ?? 0) <= ((row-1)*4)+1, 'bot': state.lobby.hosts.selected?.users[((row-1)*4)+1] === undefined }">{{ state.lobby.hosts.selected?.users[((row-1)*4)+1]?.name ?? 'бот' }}</div>
                            <div :class="{'user':true, 'empty':(state.lobby.hosts.selected?.players ?? 0) <= ((row-1)*4)+2, 'bot': state.lobby.hosts.selected?.users[((row-1)*4)+2] === undefined }">{{ state.lobby.hosts.selected?.users[((row-1)*4)+2]?.name ?? 'бот' }}</div>
                            <div :class="{'user':true, 'empty':(state.lobby.hosts.selected?.players ?? 0) <= ((row-1)*4)+3, 'bot': state.lobby.hosts.selected?.users[((row-1)*4)+3] === undefined }">{{ state.lobby.hosts.selected?.users[((row-1)*4)+3]?.name ?? 'бот' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="host-chat" v-if="state.lobby.hosts.connected">
                <div class="host-chat-area chat-area">
                    <div class="chat-message" v-for="message in state.lobby.hosts.connected.chatMessages">
                        <div class="chat-message-time">[{{ message.datetime }}]</div>
                        <template v-if="message.user.id > 0">
                            <div class="chat-message-user">{{ message.user.name }}</div>: 
                        </template><span>{{ message.text }}</span>
                    </div>
                </div>
                <div class="chat-input">
                    <input type="text" class="input-element" v-model="newMessageText" @keyup.enter.prevent.stop="sendMessage" maxLength="40" placeholder="Для отправки сообщения нажмите Enter">
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.host {
    &-info {
        height: 100%;
        gap: 10px;
        display: flex;
        flex-direction: column;

        &-user {
            & span {
                font-size: 14px;
            }
        }
        &-description {
            & span {
                font-size: 14px;
            }
        }
    }
    &-users {
        &-table {
            display: block;
        }
        &-title {
            
        }
    }
    &-chat {
        height: 250px;
        gap: 10px;
        display: grid;
        grid-template-rows: 1fr 40px;
        margin-top: 10px;

        &-area {
            min-height: 0;
            flex-grow: 1;
            overflow-y: auto;
            min-height: 0;
        }
    }
}
.user {
    opacity: .8;

    &:not(.bot):hover {
        cursor: pointer;
        text-decoration: underline;
    }

    &.empty {
        font-size: 0;
        cursor: default;
    }
}
.table-body-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
}
</style>
