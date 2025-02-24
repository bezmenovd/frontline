import alert from "./shared/alert";

export enum WsChannel {
    Main = "main",
    Lobby = "lobby",
    Host = "host",
    Game = "game",
}

class WsClient
{
    socket: WebSocket;
    onOpen: () => void = () => {};
    onMessage: (e: MessageEvent) => void = () => {};
    onClose: () => void = () => {};
    onError: () => void = () => {};
    
    constructor() {
        this.socket = new WebSocket("ws://localhost:8080");

        this.socket.onopen = () => {
            this.onOpen()
        }
        this.socket.onmessage = (e: MessageEvent) => {
            this.onMessage(e)
        }
        this.socket.onclose = () => {
            this.onClose()
        }
        this.socket.onerror = () => {
            this.onError()
        }
    }

    send(args: { token: string, channel?: WsChannel, type: string, payload: object }) {
        try {
            this.socket.send(JSON.stringify(args))
        } catch (e) {
            this.onError()
        }
    }

    close() {
        this.socket.close()
    }

    reopen() {
        this.close()
        this.socket = new WebSocket("ws://localhost:8080");

        this.socket.onopen = () => {
            this.onOpen()
        }
        this.socket.onmessage = (e: MessageEvent) => {
            this.onMessage(e)
        }
        this.socket.onclose = () => {
            this.onClose()
        }
        this.socket.onerror = () => {
            this.onError()
        }
    }
}


class WsManager
{
    channels = {
        [WsChannel.Main]: (type: string, payload: any) => {},
        [WsChannel.Lobby]: (type: string, payload: any) => {},
        [WsChannel.Host]: (type: string, payload: any) => {},
        [WsChannel.Game]: (type: string, payload: any) => {},
    };

    wsclient: WsClient;

    constructor() {
        this.wsclient = new WsClient()
        this.wsclient.onMessage = (event?: MessageEvent) => {
            let data : {
                channel: WsChannel,
                type: string,
                payload: any
            } = JSON.parse(event?.data || '{}') || {};

            if (data.type.length > 0) {
                this.channels[data.channel](data.type, data.payload);
            }
        }
        this.wsclient.onError = () => {
            alert("Ошибка", "Нет подключения к серверу", () => {
                window.location.href = window.location.href
            })
        }
    }

    subscribe(channel: WsChannel, callback: (type: string, payload: any) => void) {
        this.wsclient.send({
            token: localStorage.getItem('token') || '',
            type: "subscribe",
            payload: {
                channel,
            }
        })
        this.channels[channel] = (type: string, payload: any) => callback(type, payload);
    }

    unsubscribe(channel: WsChannel) {
        this.wsclient.send({
            token: localStorage.getItem('token') || '',
            type: "unsubscribe",
            payload: {
                channel,
            }
        })
        this.channels[channel] = (type: string, payload: any) => {}
    }

    send(channel: WsChannel, type: string, payload: any) {
        this.wsclient.send({
            token: localStorage.getItem('token') || '',
            channel,
            type,
            payload,
        })
    }

    close() {
        this.wsclient.close()
    }

    reopen() {
        this.wsclient.reopen()
    }
}


export const wsmanager = new WsManager();
