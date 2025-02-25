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

onMounted(() => {
    // @ts-ignore
    groundCanvas = document.getElementById("ground");
    // @ts-ignore
    ctx = groundCanvas.getContext("2d");
})


let drawMap = function() {
    let cellSize = 48;

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


onMounted(() => {
    let map = document.getElementById("map")

    let mapDragging = false;
    let startX : number, startY : number;
    let offsetX: number, offsetY: number;

    let scale = 1;
    const minScale = 0.1;
    const maxScale = 2;

    if (map !== null) {
        map.addEventListener("mousedown", (event) => {
            if (event.button == 2) {
                mapDragging = true;

                startX = event.clientX
                startY = event.clientY
                offsetX = map.offsetLeft
                offsetY = map.offsetTop
            }
        })
        document.addEventListener("mousemove", (event) => {
            if (mapDragging) {
                let deltaX = event.clientX - startX;
                let deltaY = event.clientY - startY;

                let newOffsetX = offsetX + deltaX;
                let newOffsetY = offsetY + deltaY;

                const minX = -((map.scrollWidth * scale) - (window.innerWidth / 2));
                const maxX = window.innerWidth / 2;
                const minY = -((map.scrollHeight * scale) - (window.innerHeight / 2));
                const maxY = window.innerHeight / 2;

                map.style.left = Math.max(minX, Math.min(newOffsetX, maxX)) + "px";
                map.style.top = Math.max(minY, Math.min(newOffsetY, maxY)) + "px";
            }
        });
        document.addEventListener("mouseup", (event) => {
            if (event.button === 2) {
                mapDragging = false;
            }
        })

        document.addEventListener("wheel", (event) => {
            event.preventDefault();

            let rect = map.getBoundingClientRect();
            let mouseX = event.clientX - rect.left;
            let mouseY = event.clientY - rect.top;

            let oldScale = scale;
            let scaleStep = 0.1;
            scale += event.deltaY < 0 ? scaleStep : -scaleStep;
            scale = Math.max(minScale, Math.min(scale, maxScale));

            let scaleRatio = scale / oldScale;

            let newLeft = event.clientX - (mouseX * scaleRatio);
            let newTop = event.clientY - (mouseY * scaleRatio);

            const minX = -(map.offsetWidth * scale - window.innerWidth / 2);
            const maxX = window.innerWidth / 2;
            const minY = -(map.offsetHeight * scale - window.innerHeight / 2);
            const maxY = window.innerHeight / 2;

            map.style.left = `${Math.max(minX, Math.min(newLeft, maxX))}px`;
            map.style.top = `${Math.max(minY, Math.min(newTop, maxY))}px`;
            map.style.transformOrigin = "0 0";
            map.style.transform = `scale(${scale})`;
        });
    }
})


</script>

<template>
    <div class="game">
        <div id="map">
            <canvas id="ground"></canvas>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.game {
    width: 100vw;
    height: 100vh;
    position: relative;
}
#ground {
    
}
#map {
    width: fit-content;
    height: fit-content;
    position: absolute;
    top: 0;
    left: 0;
}
</style>
