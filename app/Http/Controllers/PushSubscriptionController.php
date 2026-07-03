<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function publicKey()
    {
        $publicKey = config('push.vapid.public_key');
        if (is_callable($publicKey)) {
            $publicKey = call_user_func($publicKey);
        }

        return response()->json([
            'publicKey' => $publicKey,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => ['required', 'url'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['sometimes', 'string'],
            'data' => ['sometimes', 'array'],
        ]);

        $user = Auth::user();

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'user_id' => $user->id,
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aes128gcm'),
                'data' => $request->input('data'),
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        $user = Auth::user();

        PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $request->input('endpoint'))
            ->delete();

        return response()->json(['status' => 'deleted']);
    }
}
