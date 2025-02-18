<?php

namespace App\Console\Commands;

use App\Ws\GameServer;
use Illuminate\Console\Command;
use Swoole\WebSocket\Server;

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
    protected $description = 'Websocket-server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gameServer = new GameServer(new Server("0.0.0.0", 8080));
        $gameServer->start();
    }
}
