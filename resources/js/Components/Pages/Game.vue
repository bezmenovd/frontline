<script setup lang="ts">
import { computed, onMounted } from "vue";
import { wsmanager, WsChannel } from "../../ws";
import { loading } from "../loading";

let map : {
    ground: number[][],
} = {
    ground: [],
};
let players = [];


let groundCanvas = null
let ctx = null

let groundCanvasStyle = computed(() => {
    return `width: ${map.ground.length*32}px; height: ${map.ground.length*32}px;`
})

onMounted(() => {
    // @ts-ignore
    groundCanvas = document.getElementById("ground");
    // @ts-ignore
    ctx = groundCanvas.getContext("2d");
})


let drawMap = function() {
    let cellSize = 16;

    groundCanvas.style.width = `${map.ground.length * cellSize}px`;
    groundCanvas.width = map.ground.length * cellSize;
    groundCanvas.style.height = `${map.ground[0].length * cellSize}px`;
    groundCanvas.height = map.ground[0].length * cellSize;

    let colors = {
        1: "#0094ff",
        2: "#018c00",
        3: "#53b852",
        4: "#ffffff",
    };

    setTimeout(() => {
        for (let x = 0; x < map.ground.length; x++) {
            for (let y = 0; y < map.ground[0].length; y++) {
                // @ts-ignore
                ctx.fillStyle = colors[map.ground[x][y]]
                // @ts-ignore
                ctx.fillRect(x*cellSize, y*cellSize, cellSize, cellSize)
            }
        }
        loading(false)
    }, 300)
}

wsmanager.subscribe(WsChannel.Game, (type: string, payload: any) => {
    if (type === "game_loading_status") {
        let data = <{text: string}>payload
        loading(true, data.text, 1)
    }
    if (type === "game_data.players") {
        let data = <number[]>payload;
        players = data
    }
    if (type === "game_data.map.ground.part") {
        let data = <{part: number[][]}>payload
        map.ground = map.ground.concat(data.part)
    }
    if (type === "game_data.finish") {
        loading(true, "Создание карты", 1)
        drawMap()
    }
})

</script>

<template>
    <div class="game">
        <div class="map">
            <canvas id="ground"></canvas>
        </div>
    </div>
</template>

<style lang="scss">
.game {
    width: 100%;
    height: 100%;
}
#ground {
    
}
</style>
