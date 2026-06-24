<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TestItem;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'users' => User::count(),
            'test_items' => TestItem::count(),
        ]);
    }
}
