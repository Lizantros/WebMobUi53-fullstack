<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;

class ApiPollController extends Controller
{
    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

        return $polls;
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'duration' => 'nullable',
        ]);

        $user = $request->user();

        $poll = new Poll();
        $poll->user()->associate($user);
        $poll->title = $validated['title'] ?? null;
        $poll->question = $validated['question'];
        $poll->is_draft = $request->boolean('is_draft');
        $poll->allow_multiple_choices = $request->boolean('allow_multiple_choices');
        $poll->allow_vote_change = $request->boolean('allow_vote_change');
        $poll->results_public = $request->boolean('results_public');
        $poll->duration = $validated['duration'] ?? null;
        $poll->secret_token = bin2hex(random_bytes(16));

        if (!$poll->is_draft) {
            $poll->started_at = now();
            $poll->ends_at = $poll->duration ? now()->parse('@' . (now()->timestamp + $poll->duration)) : null;
        }

        $poll->save();

        foreach ($validated['options'] as $label) {
            $option = new PollOption();
            $option->poll()->associate($poll);
            $option->label = $label;
            $option->save();
        }

        return Poll::with('options')->where('id', $poll->id)->first();
    }

    /**
     * Remove the specified poll.
     */
    public function remove(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();

        return response()->json(['message' => 'success'], 200);
    }
}
