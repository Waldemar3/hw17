<?php

namespace App\Console\Commands;

use App\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Bot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $token = "";

        Artisan::call('migrate:fresh --seed');

        $offset = 0;
        while(true){
            $data = json_decode(file_get_contents("https://api.telegram.org/bot{$token}/getUpdates?offset=". $offset), true);

            foreach ($data['result'] as $message){
                $offset = $message['update_id'] + 1;

                $chatId = $message['message']['chat']['id'];

                if(ctype_digit($message['message']['text'])){
                    $order = Order::where('id', (int)$message['message']['text'])->get()->first();

                    $status = $order->status?'Ваш заказ доставлен!':'Заказ доставляется';

                    $text = 'Заказ: '.$order->title.' - '.'Статус заказа: '.$status;
                }else{
                    $text = "Неверный формат";
                }

                file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chatId}&text={$text}");
            }
            sleep(1);
        }
    }
}
