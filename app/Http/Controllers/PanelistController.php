<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\PanelScore;
use App\Models\User;
use Illuminate\Http\Request;

class PanelistController extends Controller
{
    private array $interviewCriteria = [
        'crit1_motivation'    => "Passion/Motivation (Genuine interest vs. just wanting...)",
        'crit2_availability'  => "Availability (Clear schedule, no conflicting commitments)",
        'crit3_resilience'    => "Resilience/Problem Solving (Shows grit and willingness...)",
        'crit4_communication' => "Communication Skills (Articulates thoughts clearly)",
    ];

    /**
     * Show the panelist evaluation form (redirects to dashboard with tab).
     */
    public function index(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['tab' => 'panel']);
    }

    /**
     * Store panel interview scores for a candidate.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'candidate_id' => 'required|exists:candidates,id',
            'comments'     => 'nullable|string',
        ];

        foreach (array_keys($this->interviewCriteria) as $key) {
            $rules[$key] = 'required|integer|between:1,5';
        }

        $validated = $request->validate($rules);

        $panelistId = $request->session()->get('admin_user_id');

        PanelScore::create([
            'candidate_id'      => $validated['candidate_id'],
            'panelist_id'       => $panelistId,
            'crit1_motivation'  => $validated['crit1_motivation'],
            'crit2_availability' => $validated['crit2_availability'],
            'crit3_resilience'  => $validated['crit3_resilience'],
            'crit4_communication' => $validated['crit4_communication'],
            'comments'          => $validated['comments'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Panel evaluation submitted successfully.');
    }
}
