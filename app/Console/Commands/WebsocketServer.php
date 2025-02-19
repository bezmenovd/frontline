<?php

namespace App\Console\Commands;

use App\Ws\GameServer;
use Illuminate\Console\Command;
use Predis\Client;
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
        $wsServer = new Server("0.0.0.0", 8080);
        $wsServer->set([
            'worker_num' => 16,
        ]);

        $redisClient = new Client([
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379,
        ]);
        $redisClient->connect();

        $gameServer = new GameServer($wsServer, $redisClient);
        $gameServer->start();
    }
}
