<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;

class ApiPollController extends Controller
{
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

        return $polls;
    }

    public function show(Request $request, string $token)
    {
        $user = $request->user();
        $poll = Poll::where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $showResults = $poll->results_public || ($user && $user->id === $poll->user_id);

        if ($showResults) {
            $poll = Poll::with(['options' => function ($query) {
                $query->withCount('votes');
            }])->where('id', $poll->id)->first();
        } else {
            $poll = Poll::with('options')->where('id', $poll->id)->first();
        }

        $poll->is_ended = $poll->ends_at && now()->timestamp > now()->parse($poll->ends_at)->timestamp;

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

    public function update(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if (!$poll->is_draft) {
            return response()->json(['message' => 'Poll already started.'], 422);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'duration' => 'nullable',
        ]);

        $poll->title = $validated['title'] ?? null;
        $poll->question = $validated['question'];
        $poll->allow_multiple_choices = $request->boolean('allow_multiple_choices');
        $poll->allow_vote_change = $request->boolean('allow_vote_change');
        $poll->results_public = $request->boolean('results_public');
        $poll->duration = $validated['duration'] ?? null;
        $poll->is_draft = $request->boolean('is_draft');

        if (!$poll->is_draft) {
            $poll->started_at = now();
            $poll->ends_at = $poll->duration ? now()->parse('@' . (now()->timestamp + $poll->duration)) : null;
        }

        $poll->save();

        $poll->options()->delete();
        foreach ($validated['options'] as $label) {
            $option = new PollOption();
            $option->poll()->associate($poll);
            $option->label = $label;
            $option->save();
        }

        return Poll::with('options')->where('id', $poll->id)->first();
    }

    public function vote(Request $request, string $token)
    {
        $poll = Poll::with('options')->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if ($poll->is_draft) {
            return response()->json(['message' => 'Poll is not started.'], 422);
        }

        if ($poll->ends_at && now()->timestamp > now()->parse($poll->ends_at)->timestamp) {
            return response()->json(['message' => 'Poll is closed.'], 422);
        }

        $validated = $request->validate([
            'option_ids' => 'required|array|min:1',
        ]);

        $validOptionIds = [];
        foreach ($poll->options as $option) {
            $validOptionIds[] = $option->id;
        }
        foreach ($validated['option_ids'] as $oid) {
            $found = false;
            foreach ($validOptionIds as $vid) {
                if ($oid == $vid) {
                    $found = true;
                }
            }
            if (!$found) {
                return response()->json(['message' => 'Invalid option.'], 422);
            }
        }

        if (!$poll->allow_multiple_choices && count($validated['option_ids']) > 1) {
            return response()->json(['message' => 'Only one option allowed.'], 422);
        }

        $user = $request->user();
        $existing = $poll->votes()->where('user_id', $user->id)->get();

        if (count($existing) > 0 && !$poll->allow_vote_change) {
            return response()->json(['message' => 'You already voted.'], 422);
        }

        if (count($existing) > 0) {
            $poll->votes()->where('user_id', $user->id)->delete();
        }

        foreach ($validated['option_ids'] as $oid) {
            $vote = new PollVote();
            $vote->poll()->associate($poll);
            $vote->user()->associate($user);
            $vote->poll_option_id = $oid;
            $vote->save();
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function myVote(Request $request, string $token)
    {
        $poll = Poll::where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $votes = $poll->votes()->where('user_id', $request->user()->id)->get();

        $ids = [];
        foreach ($votes as $vote) {
            $ids[] = $vote->poll_option_id;
        }

        return ['option_ids' => $ids];
    }

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
