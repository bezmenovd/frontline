<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

let games: {
    id: number,
    host: string,
    description: string,
    members: {
        now: number,
        max: number,
    }
}[] = [];

let selectedGameId = ref(0);
let selectedGame = computed(() => {
    return games.find((game) => game.id === selectedGameId.value)
})


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
            <div class="table-head-column">Участники</div>
        </div>
        <div class="table-body">
            <div v-for="game in games" :class="{'table-body-row': true, 'selected': selectedGameId == game.id}" @click="selectedGameId = game.id">
                <div :title="game.host">{{ game.host.substring(0, 18) }}</div>
                <div :title="game.description">{{ game.description.substring(0, 37) }}</div>
                <div>{{ game.members.now }}/{{ game.members.max }}</div>
            </div>
        </div>
    </div>
</template>

<style lang="scss">

</style>

