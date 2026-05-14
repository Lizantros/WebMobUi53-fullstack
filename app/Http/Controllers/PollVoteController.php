<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollVoteController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        return view('polls.vote', [
            'token' => $token,
            'isAuthenticated' => Auth::check(),
            'loginUrl' => url('/auth/login'),
        ]);
    }
}
