<?php

namespace App\Console\Commands;

use App\Ws\Controller;
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
        $wsServer = new Server("localhost", 8080);
        $wsServer->set([
            'worker_num' => 64,
            'buffer_output_size' => 4 * 1024 * 1024,
            'socket_buffer_size' => 2 * 1024 * 1024,
            'package_max_length' => 10 * 1024 * 1024,
        ]);
        
        $redisClient = new Client([
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379,
        ]);
        $redisClient->connect();

        $controller = new Controller($wsServer, $redisClient);
        $controller->start();
    }
}
