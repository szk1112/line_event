<?php

namespace App\Console\Commands\RichMenu;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use LINE\LINEBot;

class GetListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'richmenu:get-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'LINEのオフィシャルアカウントのリッチメニューの一覧を取得します';

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
        $response = $bot->getRichMenuList();

        if (!$response->isSucceeded()) {
            print_r($response->getRawBody() . "\n");
            return 0;
        }

        $lists = [];
        foreach ($response->getJSONDecodedBody()['richmenus'] as $richmenu) {
            $item['richMenuId'] = $richmenu['richMenuId'];
            $item['name']       = $richmenu['name'];
            $lists[]            = $item;
        }
        if (empty($lists)) {
            print_r('The menu is not registered.' . "\n");
            return 0;
        }

        $response = Http::withHeaders([
                                          'Authorization' => 'Bearer ' . config('app.const.Line.LINE_BOT_CHANNEL_ACCESS_TOKEN'),
                                      ])->get("https://api.line.me/v2/bot/richmenu/alias/list");

        if (!$response->ok()) {
            print_r($response->body() . "\n");
            return 0;
        }

        $aliases = collect($response->json()['aliases']);
        foreach ($lists as $index => $item) {
            $lists[$index]['richMenuAliasId'] = $aliases->where('richMenuId', '=', $item['richMenuId'])
                                                        ->pluck('richMenuAliasId')
                                                        ->toArray();
        }

        foreach ($lists as $item) {
            printf("richMenuId: %s\n", $item['richMenuId']);
            printf("name: %s\n", $item['name']);
            if (isset($item['richMenuAliasId']) && is_array($item['richMenuAliasId'])) {
                printf("aliasId: %s\n", implode(' ', $item['richMenuAliasId']));
            }
            print_r("------------------------------------------------\n");
        }
        return 0;
    }
}
