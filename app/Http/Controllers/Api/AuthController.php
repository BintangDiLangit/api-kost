<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'nomor_hp' => 'required|string|unique:users,nomor_hp,except,id',
            'password' => 'required|string|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        try {
            DB::beginTransaction();
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'nomor_hp' => $request->nomor_hp,
                'password' => bcrypt($request->password),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return response()->json([
                'message' => 'Terima kasih telah mendaftar di blonjoo',
                'data' => ['token' => $token, 'user' => $user]
            ], Response::HTTP_OK);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal melakukan pendaftaran.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identitas' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {

            $auth = User::where('email', $request->identitas)->orWhere('nomor_hp', $request->identitas)->first();

            if (!empty($auth)) {
                if (password_verify($request->password, $auth->password)) {
                    // $auth->authenticate();
                    $token = $auth->createToken('auth_token')->plainTextToken;
                    return response()->json(
                        [
                            'message' => 'Logged Success',

                            'data' => [
                                'token' => $token,
                                'user' => $auth,
                            ],
                        ],
                        200
                    );
                }
            }
            return response()->json(['error' => 'Credentials not valid'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Credentials not valid'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json(
                [
                    'message' => 'Logged out'
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login expired'], 401);
        }
    }

    public function profile()
    {
        $user = User::find(Auth::user()->id);
        return response()->json(
            [
                'message' => 'My Profile',

                'data' => $user
            ],
            200
        );
    }
}
