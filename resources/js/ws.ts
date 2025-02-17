export enum WsChannel {
    Main = "main",
    Lobby = "lobby",
    Host = "host",
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
        [WsChannel.Main]: (type: string, payload: any) => {},
        [WsChannel.Lobby]: (type: string, payload: any) => {},
        [WsChannel.Host]: (type: string, payload: any) => {},
    };

    constructor() {
        wsclient.onMessage = (event?: MessageEvent) => {
            let data : {
                channel: WsChannel,
                type: string,
                payload: any
            } = JSON.parse(event?.data || '{}') || {};

            if (data.type.length > 0) {
                this.channels[data.channel](data.type, data.payload);
            }
        }
    }

    subscribe(channel: WsChannel, callback: (type: string, payload: any) => void) {
        wsclient.send({
            token: localStorage.getItem('token') || '',
            channel: WsChannel.Main,
            type: "subscribe",
            payload: {
                channel,
            }
        })
        this.channels[channel] = (type: string, payload: any) => callback(type, payload);
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
        this.channels[channel] = (type: string, payload: any) => {}
    }

    send(channel: WsChannel, type: string, payload: any) {
        wsclient.send({
            token: localStorage.getItem('token') || '',
            channel,
            type,
            payload,
        })
    }
}


export const wsmanager = new WsManager();
