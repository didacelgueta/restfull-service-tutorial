<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        
        $user = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'signin' => [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email, password'
            ]
        ];

        $response = [
            'msg' => 'User Created',
            'user' => $user
        ];

        return response()->json($response, 201);
    }

    public function signin(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:10'],
            'email' => ['required', 'email']

        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        return "It works!";
    }
}
