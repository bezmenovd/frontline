<?php

namespace App\Console\Commands;

use App\Ws\ChannelManager;
use Illuminate\Console\Command;
use WebSocket;

class WebsocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:websocket-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server = new Websocket\Server(8080);
        $manager = new ChannelManager($server);

        $server->start();
    }
}
