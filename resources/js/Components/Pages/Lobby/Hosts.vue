<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import state from '../../../state';
import { Host } from '../../../types';

let selectedHost = defineModel<Host>();

let setGamesListTableHeight = function() {
    let gamesListTableEl = document.querySelector(".games-list-table")
    if (! gamesListTableEl) {
        return
    }
    gamesListTableEl.style.height = (window.innerHeight - 110) + "px";
}

window.onresize = setGamesListTableHeight;

onMounted(() => {
    setGamesListTableHeight();
})


</script>

<template>
    <div class="games-list-table table">
        <div class="table-head">
            <div class="table-head-column">Хост</div>
            <div class="table-head-column">Описание</div>
            <div class="table-head-column">Игроки</div>
        </div>
        <div class="table-body">
            <div 
                v-for="host in state.lobby.hosts.list" 
                :class="{
                    'table-body-row': true, 
                    'selected': selectedHost !== undefined && selectedHost.id == host.id, 
                    'disabled': state.lobby.hosts.connected !== undefined
                }" 
                @click="selectedHost = host"
            >
                <div :title="host.user.name">{{ host.user.name.substring(0, 18) }}</div>
                <div :title="host.description">{{ host.description.substring(0, 36) }}</div>
                <div style="text-align: right;">{{ host.users.length }}/{{ host.players }}</div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
.table {
    &-body {
        &-row {
            &.disabled {
                pointer-events: none;
                cursor: default;
            }
        }
    }
}
</style>

