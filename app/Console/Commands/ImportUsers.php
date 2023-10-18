<?php

namespace App\Console\Commands;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-users';

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
        $count = 5000;
        $users = $this->getUsers($count);
        $chunks = array_chunk($users, 200);
        $countAllBefore = User::count();
        foreach ($chunks as $chunk) {
            $users = [];
            foreach ($chunk as $user) {
                $users[] = [
                    'first_name' => Arr::get($user, 'name.first'),
                    'last_name' => Arr::get($user, 'name.last'),
                    'age' => Arr::get($user, 'dob.age'),
                    'email' => Arr::get($user, 'email')
                ];
            }
            User::upsert($users, ['first_name', 'last_name'], ['age', 'email']);
        }
        $countAll = User::count();
        $countAdded = $countAll - $countAllBefore;
        Cache::set('count_users', [
            'count_all' => $countAll,
            'count_added' => $countAdded,
            'count_updated' => $count - $countAdded,
        ]);

        return 1;
    }

    private function client(): Client
    {
        $client = new Client([
            'base_uri' => 'https://randomuser.me/api',
            'timeout' => 300,
            'headers' => [
                'Content-Type' => 'application/json',
                "Accept" => "application/json",
            ],
            'http_errors' => false,
            'verify' => false
        ]);
        return $client;
    }


    public function getUsers(int $count = 5000)
    {
        $client = $this->client();
        try {
            $response = $client->get('', ['query' => ['results' => $count]])->getBody()->getContents();
            $result = json_decode($response, true);
            return $result['results'];
        } catch (\Exception $ex) {
            return false;
        }
    }
}
