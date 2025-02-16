<script setup lang="ts">
import Games from "./Lobby/Games.vue";
import state from "../../state";
import { wsmanager, WsChannel } from "../../ws";
import { ref } from "vue";


let online = ref(0);

wsmanager.subscribe(WsChannel.Lobby, (type: string, payload: object) => {
    online.value = payload.online;
})

wsmanager.subscribe(WsChannel.Main, (type: string, payload: object) => {})

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
                        <img src="/public/icons/user.png" alt="">
                        {{ state.user?.name }}
                    </div>
                    <div class="player-panel-info-item --rating">
                        <img src="/public/icons/star.png" alt="">
                        {{ state.user?.rating }}
                    </div>
                </div>
            </div>
            <div class="panel chat">
                <div class="panel-title">
                    Чат
                    <div class="game-online">Онлайн: {{ online }}</div>
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
                & img {
                    filter: invert(1);
                    opacity: .5;
                    width: 18px;
                    height: 18px;
                }
            }

            &.--rating {
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
    max-height: calc(100% - 40px);

    &-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
}
.game {
    position: relative;

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
    & .panel-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    & .game-online {
        font-size: 14px;
    }
}
</style>
