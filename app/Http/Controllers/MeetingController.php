<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __constructor()
    {
        $this->middleware('auth:api', ['only' => [
            'update', 'store', 'destroy'
        ]]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meetings = Meeting::all();

        $meetings->each(function ($meeting) {
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
        });

        $response = [
            'msg' => 'Meetings created',
            'meetings' => $meetings
        ];

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required'],
            'description' => ['required'],
            'time' => ['required', 'date_format:Y-m-d H:i:s']
        ]);

        if (!$user = auth()->user()) {
            return response()->json([
                'msg' => 'User not found',
                404
            ]);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = Meeting::create([
            'title' => $title,
            'description' => $description,
            'time' => $time,
        ]);

        if ($meeting->save()) {
            $meeting->users()->attach($user_id);

            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'POST',
                'params' => 'title, description, time'
            ];
        }        

        $response = [
            'msg' => 'Meeting created',
            'meeting' => $meeting
        ];

        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();

        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/' . $id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting created',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => ['required'],
            'description' => ['required'],
            'time' => ['required', 'date_format:Y-m-d H:i:s']
        ]);

        if (!$user = auth()->user()) {
            return response()->json([
                'msg' => 'User not found',
                404
            ]);
        }
        
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = Meeting::with('users')->findOrFail($id);
        
        if (!$meeting->users()->where('users.id', $user_id)->first()) {
            return response()->json([
                'msg' => 'User not registered for meeting, update not successfull', 401
            ]);
        }

        $meeting->title = $title;
        $meeting->description = $description;
        $meeting->time = $time;
        
        if (!$meeting->update()) {
            return response()->json([
                'msg' => 'Error during updating'
            ]);
        }

        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/1',
            'method' => 'PATCH'
        ];

        $response = [
            'msg' => 'Meeting created',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);

        if (!$user = auth()->user()) {
            return response()->json([
                'msg' => 'User not found',
                404
            ]);
        }

        if (!$meeting->users()->where('users.id', $user->id)->first()) {
            return response()->json([
                'msg' => 'User not registered for meeting, update not successfull', 401
            ]);
        }

        $users = $meeting->users;
        $meeting->users()->detach();

        if (!$meeting->delete()) {
            $users->each(function ($user) use ($meeting) {
                $meeting->users()->attach($user);
            });

            return response()->json([
                'msg' => 'Delation failed',
                404
            ]);
        }

        $response = [
            'msg' => 'Meeting deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'DELETE'
            ]
        ];

        return response()->json($response, 200);
    }
}
