<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'question'               => 'required|string',
            'title'                  => 'nullable|string',
            'options'                => 'required|array|min:2',
            'options.*'              => 'required|string',
            'allow_multiple_choices' => 'boolean',
            'results_public'         => 'boolean',
            'ends_at'                => 'nullable|date|after:now',
            'start_now'              => 'boolean',
            'scheduled_at'           => 'nullable|date|after:now',
        ]);

        $startNow = $validated['start_now'] ?? false;
        $scheduledAt = $validated['scheduled_at'] ?? null;

        $poll = $request->user()->polls()->create([
            'question'               => $validated['question'],
            'title'                  => $validated['title'] ?? null,
            'secret_token'           => Str::uuid(),
            'is_draft'               => !($validated['start_now'] ?? false),
            'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? false,
            'allow_vote_change'      => false,
            'results_public'         => $validated['results_public'] ?? false,
            'ends_at'                => $validated['ends_at'] ?? null,  
            'started_at'             => $startNow ? now() : $scheduledAt,
        ]);

        foreach ($validated['options'] as $label) {
        $poll->options()->create(['label' => $label]);
        }

        return response()->json($poll->load('options'), 201);
    }

    public function update(Request $request, string $id)
    {
        $poll = Poll::find($id);

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'question'               => 'sometimes|required|string',
            'title'                  => 'nullable|string',
            'options'                => 'sometimes|required|array|min:2',
            'options.*'              => 'required|string',
            'allow_multiple_choices' => 'boolean',
            'results_public'         => 'boolean',
            'ends_at'                => 'nullable|date',
            'start_now'              => 'boolean',
            'scheduled_at'           => 'nullable|date|after:now',
        ]);

        $startNow    = $validated['start_now'] ?? false;
        $scheduledAt = $validated['scheduled_at'] ?? null;

        $poll->update([
            'title'                  => $validated['title'] ?? $poll->title,
            'question'               => $validated['question'] ?? $poll->question,
            'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? $poll->allow_multiple_choices,
            'results_public'         => $validated['results_public'] ?? $poll->results_public,
            'ends_at'                => $validated['ends_at'] ?? $poll->ends_at,
            'is_draft'               => $startNow ? false : $poll->is_draft,
            'started_at'             => $startNow ? now() : ($scheduledAt ?? $poll->started_at),
        ]);

        // Mise à jour des options si fournies
        if (isset($validated['options'])) {
            $poll->options()->delete();
            foreach ($validated['options'] as $label) {
                $poll->options()->create(['label' => $label]);
            }
        }

        return response()->json($poll->load('options'), 200);
    }

    public function destroy(Request $request, string $id)
    {
        $poll = Poll::find($id);

        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();

        return response()->json(['message' => 'Poll deleted successfully.']);
    }
}
