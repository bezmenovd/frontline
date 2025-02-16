export enum WsChannel {
    Main = "main",
    Lobby = "lobby",
    Host = "host",
    Chat = "chat",
}

class WsClient
{
    socket: WebSocket;
    onOpen: () => void = () => {};
    onMessage: (e: MessageEvent) => void = () => {};
    onClose: () => void = () => {};
    
    constructor() {
        this.socket = new WebSocket("http://0.0.0.0:8080");

        this.socket.onopen = () => {
            this.onOpen()
        }

        this.socket.onmessage = (e: MessageEvent) => {
            this.onMessage(e)
        }
        
        this.socket.onclose = () => {
            this.onClose()
        }
    }

    send(args: { token: string, channel: WsChannel, type: string, payload: object }) {
        this.socket.send(JSON.stringify(args))
    }
}

const wsclient = new WsClient();


class WsManager
{
    channels = {
        [WsChannel.Main]: (type: string, payload: object) => {},
        [WsChannel.Lobby]: (type: string, payload: object) => {},
        [WsChannel.Host]: (type: string, payload: object) => {},
        [WsChannel.Chat]: (type: string, payload: object) => {},
    };

    constructor() {
        wsclient.onMessage = (event?: MessageEvent) => {
            let data : {
                channel: WsChannel,
                type: string,
                payload: object
            } = JSON.parse(event?.data || '{}') || {};

            if (data.type.length > 0) {
                this.channels[data.channel](data.type, data.payload);
            }
        }
    }

    subscribe(channel: WsChannel, callback: (type: string, payload: object) => void) {
        wsclient.send({
            token: localStorage.getItem('token') || '',
            channel: WsChannel.Main,
            type: "subscribe",
            payload: {
                channel,
            }
        })
        this.channels[channel] = (type: string, payload: object) => callback(type, payload);
    }

    unsubscribe(channel: WsChannel) {
        wsclient.send({
            token: localStorage.getItem('token') || '',
            channel: WsChannel.Main,
            type: "unsubscribe",
            payload: {
                channel,
            }
        })
        this.channels[channel] = (type: string, payload: object) => {}
    }

    send(channel: WsChannel, type: string, payload: object) {
        wsclient.send({
            token: localStorage.getItem('token') || '',
            channel,
            type,
            payload,
        })
    }
}


export const wsmanager = new WsManager();
