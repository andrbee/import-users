<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $data = [
            'count_all' => User::count(),
            'count_added' => 0,
            'count_updated' => 0,
        ];

        return response()->json($data);
    }

    public function import()
    {
        $result = Artisan::call('import-users');

        return response()->json(Cache::get('count_users'));
    }

}
