<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index()
    {
        $data = User::all();
        return response()->json([
            'message' => 'List data kost',
            'data' => $data
        ], Response::HTTP_OK);
    }
}