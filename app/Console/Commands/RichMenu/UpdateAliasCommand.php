<?php

namespace App\Console\Commands\RichMenu;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use LINE\LINEBot;

class UpdateAliasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'richmenu:update-alias {richMenuAliasId} {richMenuId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'LINEのオフィシャルアカウントのリッチメニューでエイリアスIDに紐付いているリッチメニューIDを更新します';

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
     * @return int
     */
    public function handle(LINEBot $bot)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('app.const.Line.LINE_BOT_CHANNEL_ACCESS_TOKEN'),
            'Content-Type'  => 'application/json'
        ])->post(
            "https://api.line.me/v2/bot/richmenu/alias/" . $this->argument('richMenuAliasId'),
            [
                'richMenuId' => $this->argument('richMenuId'),
            ],
        );

        if (!$response->ok()) {
            print_r($response->body() . "\n");
            return 0;
        }

        print_r("success\n");
        return 0;
    }
}
