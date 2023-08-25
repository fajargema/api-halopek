<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\JsonApiResponse;
use App\Models\Chat;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role == "user") {
            $userIds = Chat::distinct('konselor_id')->pluck('konselor_id')->toArray();
        } else if ($user->role == "konselor") {
            $userIds = Chat::distinct('user_id')->pluck('user_id')->toArray();
        }

        $users = User::whereIn('id', $userIds)->select(['id', 'name', 'email'])->get();

        return JsonApiResponse::success($users);
    }
    public function show($id)
    {
        $user = auth()->user();
        if ($user->role == "user") {
            $chat = Chat::with('user', 'konselor')->where('user_id', $id)->where('user_id', $user->id)->get();
        } else if ($user->role == "konselor") {
            $chat = Chat::with('user', 'konselor')->where('konselor_id', $id)->where('user_id', $user->id)->get();
        }

        return JsonApiResponse::success($chat);
    }

    public function send(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required',
            ]);

            if ($validator->fails()) {
                return JsonApiResponse::error($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (auth()->user()->role == "user") {
                $message = [
                    'user_id' => Auth::id(),
                    'konselor_id' => $id,
                    'message' => $request->message,
                ];
            } else if (auth()->user()->role == "konselor") {
                $message = [
                    'user_id' => $id,
                    'konselor_id' => Auth::id(),
                    'message' => $request->message,
                ];
            } else {
                return JsonApiResponse::error("bad request", Response::HTTP_BAD_REQUEST);
            }

            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]);

            $pusher->trigger('chat-channel', 'new-message', $message);

            $chat = new Chat($message);
            $chat->save();

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return JsonApiResponse::error($e, Response::HTTP_CONFLICT);
        }
    }
}
