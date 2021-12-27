<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:10'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:4']

        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);
        if ($user->save()) {
            $user->signin = [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email, password'
            ];
            
            $response = [
                'msg' => 'User Created',
                'user' => $user
            ];

            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'An error occured'
        ];
        
        return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'email' => ['required', 'email']

        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = auth()->attempt($credentials)) {
                return response()->json([
                    'msg' => 'Invalid credentials',
                    401
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'msg' => 'Could not create token',
                500
            ]);
        }

        return response()->json(['token', $token]);
    }
}
