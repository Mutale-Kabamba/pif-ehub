<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RosterController extends Controller
{
    /**
     * Display the Candidates & Panelists Roster management terminal.
     */
    public function index(Request $request): View
    {
        $currentUser = auth()->user() ?: User::find(session('admin_user_id'));
        if ($currentUser && ! $currentUser->isSuper()) {
            abort(403, 'Only super administrators can manage candidate and panelist rosters.');
        }

        $activeTab = $request->query('tab', 'candidates');

        // Candidates Query
        $candidatesQuery = Candidate::with(['literacyScore', 'panelScores'])
            ->withCount(['panelScores']);

        if ($request->filled('q_candidate')) {
            $term = '%' . $request->query('q_candidate') . '%';
            $candidatesQuery->where('name', 'like', $term);
        }

        if ($request->filled('panel_candidate')) {
            $panel = $request->query('panel_candidate');
            if ($panel === 'unassigned') {
                $candidatesQuery->whereNull('panel');
            } else {
                $candidatesQuery->where('panel', $panel);
            }
        }

        if ($request->filled('gender_candidate')) {
            $candidatesQuery->where('gender', $request->query('gender_candidate'));
        }

        $candidates = $candidatesQuery->orderBy('panel')->orderBy('name')->get();

        // Panelists Query
        $panelistsQuery = User::withCount(['panelScores']);

        if ($request->filled('q_panelist')) {
            $term = '%' . $request->query('q_panelist') . '%';
            $panelistsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('panelist_name', 'like', $term);
            });
        }

        if ($request->filled('panel_panelist')) {
            $panel = $request->query('panel_panelist');
            if ($panel === 'unassigned') {
                $panelistsQuery->whereNull('panel');
            } else {
                $panelistsQuery->where('panel', $panel);
            }
        }

        $panelists = $panelistsQuery->orderBy('role')->orderBy('panel')->orderBy('name')->get();

        // Summary metrics
        $totalCandidates = Candidate::count();
        $panelACount = Candidate::where('panel', 'A')->count();
        $panelBCount = Candidate::where('panel', 'B')->count();
        $totalPanelists = User::count();

        // Assessments Query for dynamic assessments/interviews/surveys
        $assessments = \App\Models\Assessment::withCount(['candidates', 'questions'])->orderBy('title')->get();

        return view('admin.roster.index', [
            'user'             => $currentUser,
            'activeTab'        => $activeTab,
            'candidates'       => $candidates,
            'panelists'        => $panelists,
            'assessments'      => $assessments,
            'totalCandidates'  => $totalCandidates,
            'panelACount'      => $panelACount,
            'panelBCount'      => $panelBCount,
            'totalPanelists'   => $totalPanelists,
        ]);
    }

    /**
     * Store a new candidate.
     */
    public function storeCandidate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'panel'  => 'nullable|in:A,B,cover',
        ]);

        $candidate = Candidate::create([
            'name'   => trim($validated['name']),
            'gender' => $validated['gender'],
            'panel'  => $validated['panel'] ?? null,
        ]);

        return redirect()->route('admin.roster.index', ['tab' => 'candidates'])
            ->with('success', "Candidate '{$candidate->name}' added successfully.");
    }

    /**
     * Update an existing candidate.
     */
    public function updateCandidate(Request $request, Candidate $candidate): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'panel'  => 'nullable|in:A,B,cover',
        ]);

        $candidate->update([
            'name'   => trim($validated['name']),
            'gender' => $validated['gender'],
            'panel'  => $validated['panel'] ?? null,
        ]);

        return redirect()->route('admin.roster.index', ['tab' => 'candidates'])
            ->with('success', "Candidate '{$candidate->name}' updated successfully.");
    }

    /**
     * Delete a candidate.
     */
    public function destroyCandidate(Candidate $candidate): RedirectResponse
    {
        $name = $candidate->name;

        DB::transaction(function () use ($candidate) {
            $candidate->panelScores()->delete();
            $candidate->literacyScore()->delete();
            $candidate->evaluationScores()->delete();
            $candidate->assessmentAssignments()->delete();
            $candidate->delete();
        });

        return redirect()->route('admin.roster.index', ['tab' => 'candidates'])
            ->with('success', "Candidate '{$name}' deleted successfully.");
    }

    /**
     * Store a new panelist / admin user.
     */
    public function storePanelist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'panel'    => 'nullable|in:A,B,cover',
            'role'     => 'required|in:panelist,super',
        ]);

        $user = User::create([
            'name'          => trim($validated['name']),
            'panelist_name' => trim($validated['name']),
            'email'         => strtolower(trim($validated['email'])),
            'password'      => Hash::make($validated['password']),
            'panel'         => $validated['panel'] ?? null,
            'role'          => $validated['role'],
        ]);

        return redirect()->route('admin.roster.index', ['tab' => 'panelists'])
            ->with('success', "Panelist '{$user->name}' created successfully.");
    }

    /**
     * Update an existing panelist / user.
     */
    public function updatePanelist(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'panel'    => 'nullable|in:A,B,cover',
            'role'     => 'required|in:panelist,super',
        ]);

        $data = [
            'name'          => trim($validated['name']),
            'panelist_name' => trim($validated['name']),
            'email'         => strtolower(trim($validated['email'])),
            'panel'         => $validated['panel'] ?? null,
            'role'          => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.roster.index', ['tab' => 'panelists'])
            ->with('success', "Panelist '{$user->name}' updated successfully.");
    }

    /**
     * Delete a panelist.
     */
    public function destroyPanelist(User $user): RedirectResponse
    {
        $currentAdminId = session('admin_user_id') ?: auth()->id();
        if ($user->id == $currentAdminId) {
            return redirect()->route('admin.roster.index', ['tab' => 'panelists'])
                ->with('error', 'You cannot delete your own currently logged-in account.');
        }

        // Prevent deleting the last super user
        if ($user->isSuper() && User::where('role', 'super')->count() <= 1) {
            return redirect()->route('admin.roster.index', ['tab' => 'panelists'])
                ->with('error', 'Cannot delete the only remaining Super Administrator account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.roster.index', ['tab' => 'panelists'])
            ->with('success', "Panelist '{$name}' removed successfully.");
    }
}
