<script setup lang="ts">
import { computed, onMounted } from "vue";
import { wsmanager, WsChannel } from "../../ws";
import { loading } from "../loading";

let map : {
    size: {
        width: number,
        height: number,
    },
    ground: number[][],
} = {
    size: {
        width: 0,
        height: 0,
    },
    ground: [],
};
let players = [];


let groundCanvas : HTMLElement|null = null
let groundCtx : HTMLElement|null = null

let minimapCanvas : HTMLElement|null = null
let minimapCtx : HTMLElement|null = null

let mapElement : HTMLElement|null = null;
let viewboxElement : HTMLElement|null = null;

let scale = 1;
let minScale = 0.125;

onMounted(() => {
    // @ts-ignore
    groundCanvas = document.getElementById("ground");
    // @ts-ignore
    groundCtx = groundCanvas.getContext("2d");

    // @ts-ignore
    minimapCanvas = document.getElementById("minimap");
    // @ts-ignore
    minimapCtx = minimapCanvas.getContext("2d");
})


let drawMap = function() {
    let cellSize = 48;

    groundCanvas.style.width = `${map.size.width * cellSize}px`;
    groundCanvas.width = map.size.width * cellSize;
    groundCanvas.style.height = `${map.size.height * cellSize}px`;
    groundCanvas.height = map.size.height * cellSize;

    let colors = {
        1: "#4583b3",
        2: "#bedc8a",
        3: "#e4d3a6",
        4: "#ffffff",
    };

    for (let x = 0; x < map.size.width; x++) {
        for (let y = 0; y < map.size.height; y++) {
            // @ts-ignore
            groundCtx.fillStyle = colors[map.ground[x][y]]
            // @ts-ignore
            groundCtx.fillRect(x*cellSize, y*cellSize, cellSize, cellSize)
        }
    }
}

let drawMinimap = function() {
    let cellWidth = 256 / map.size.width;
    let cellHeight = 256 / map.size.height;

    let cellSize = Math.min(cellWidth, cellHeight)

    let colors = {
        1: "#4583b3",
        2: "#bedc8a",
        3: "#e4d3a6",
        4: "#ffffff",
    };

    let startX = map.size.width < map.size.height ? (map.size.height - map.size.width) / 2 : 0;
    let startY = map.size.height < map.size.width ? (map.size.width - map.size.height) / 2 : 0;

    for (let x = startX; x < map.size.width; x++) {
        for (let y = startY; y < map.size.height; y++) {
            // @ts-ignore
            minimapCtx.fillStyle = colors[map.ground[x][y]]
            // @ts-ignore
            minimapCtx.fillRect(x*cellSize, y*cellSize, cellSize, cellSize)
        }
    }

    viewboxElement.style.width = Math.min((window.innerWidth / (mapElement.clientWidth * scale) * 178), 178) + "px";
    viewboxElement.style.height = Math.min((window.innerHeight / (mapElement.clientHeight * scale) * 178), 178) + "px";
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
        map.size.width = map.ground.length;
        map.size.height = map.ground[0].length;
        loading(true, "Создание карты", 1)
        minScale = 0.125 * (256 / Math.max(map.size.width, map.size.height))
        drawMap()
        drawMinimap()
        setTimeout(() => {[
            loading(false)
        ]}, 500)
    }
})

onMounted(() => {
    mapElement = document.getElementById("map");
    viewboxElement = document.getElementById("viewbox");

    let mapDragging = false;
    let startX : number, startY : number;
    let offsetX: number, offsetY: number;

    scale = 1;
    const maxScale = 1;

    if (mapElement !== null && viewboxElement !== null) {
        mapElement.addEventListener("mousedown", (event) => {
            if (event.button == 2) {
                mapDragging = true;

                startX = event.clientX
                startY = event.clientY
                offsetX = mapElement.offsetLeft
                offsetY = mapElement.offsetTop
            }
        })
        document.addEventListener("mousemove", (event) => {
            if (mapDragging) {
                let deltaX = event.clientX - startX;
                let deltaY = event.clientY - startY;

                let newOffsetX = offsetX + deltaX;
                let newOffsetY = offsetY + deltaY;

                const minX = -((mapElement.scrollWidth * scale) - (window.innerWidth / 2));
                const maxX = window.innerWidth / 2;
                const minY = -((mapElement.scrollHeight * scale) - (window.innerHeight / 2));
                const maxY = window.innerHeight / 2;
                const left = Math.max(minX, Math.min(newOffsetX, maxX));
                const top = Math.max(minY, Math.min(newOffsetY, maxY));

                mapElement.style.left = left + "px";
                mapElement.style.top = top + "px";

                viewboxElement.style.left = (-left / ((mapElement.clientWidth * scale) / 178)) + "px";
                viewboxElement.style.top = (-top / ((mapElement.clientHeight * scale) / 178)) + "px";
            }
        });
        document.addEventListener("mouseup", (event) => {
            if (event.button === 2) {
                mapDragging = false;
            }
        })

        document.addEventListener("wheel", (event) => {
            event.preventDefault();

            let rect = mapElement.getBoundingClientRect();
            let mouseX = event.clientX - rect.left;
            let mouseY = event.clientY - rect.top;

            let oldScale = scale;
            let scaleStep = 0.05;
            scale += event.deltaY < 0 ? scaleStep : -scaleStep;
            scale = Math.max(minScale, Math.min(scale, maxScale));

            let scaleRatio = scale / oldScale;

            let newLeft = event.clientX - (mouseX * scaleRatio);
            let newTop = event.clientY - (mouseY * scaleRatio);

            const minX = -(mapElement.offsetWidth * scale - window.innerWidth / 2);
            const maxX = window.innerWidth / 2;
            const minY = -(mapElement.offsetHeight * scale - window.innerHeight / 2);
            const maxY = window.innerHeight / 2;

            let left = Math.max(minX, Math.min(newLeft, maxX))
            let top = Math.max(minY, Math.min(newTop, maxY))

            mapElement.style.left = left + 'px';
            mapElement.style.top = top + 'px';
            mapElement.style.transformOrigin = "0 0";
            mapElement.style.transform = `scale(${scale})`;

            viewboxElement.style.left = (-left / ((mapElement.clientWidth * scale) / 178)) + "px";
            viewboxElement.style.top = (-top / ((mapElement.clientHeight * scale) / 178)) + "px";
            viewboxElement.style.width = Math.min((window.innerWidth / (mapElement.clientWidth * scale) * 178), 178) + "px";
            viewboxElement.style.height = Math.min((window.innerHeight / (mapElement.clientHeight * scale) * 178), 178) + "px";
        }, { passive: false });

        window.addEventListener("resize", function() {
            viewboxElement.style.width = Math.min((window.innerWidth / (mapElement.clientWidth * scale) * 178), 178) + "px";
            viewboxElement.style.height = Math.min((window.innerHeight / (mapElement.clientHeight * scale) * 178), 178) + "px";
        })
    }
})


</script>

<template>
    <div class="game">
        <div class="ui">
            <div class="minimap panel">
                <div class="minimap-wrapper">
                    <div class="minimap-viewbox" id="viewbox"></div>
                    <canvas class="minimap-canvas" id="minimap" width="256" height="256"></canvas>
                </div>
            </div>
        </div>
        <div id="map">
            <canvas id="ground"></canvas>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.game {
    width: 100%;
    height: 100%;
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
    z-index: 1;
}
.ui {

}
.minimap {
    position: absolute;
    bottom: 0;
    right: 0;
    border-radius: 0;
    border-top-left-radius: 8px;
    border-bottom-width: 0;
    border-right-width: 0;
    width: 200px;
    height: 200px;
    z-index: 100;
    padding: 10px;

    &-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    &-viewbox {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 2px dashed rgb(84, 84, 84);
        box-sizing: border-box;
    }
    &-canvas {
        width: 178px;
        height: 178px;
        background: black;
    }
}
</style>
