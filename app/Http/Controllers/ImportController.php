<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    protected ExcelImportExportService $excelService;

    public function __construct(ExcelImportExportService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Download an Excel or CSV template for data entry.
     */
    public function downloadTemplate(Request $request, string $type): StreamedResponse
    {
        $format = $request->query('format', 'xlsx');
        return $this->excelService->downloadTemplate($type, $format);
    }

    /**
     * Handle bulk candidates import.
     */
    public function importCandidates(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $result = $this->excelService->importCandidates($request->file('file'));

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', implode(' ', $result['errors'] ?? ['Failed to import candidates.']));
        }

        return redirect()->route('admin.roster.index', ['tab' => 'candidates'])
            ->with('success', $result['message']);
    }

    /**
     * Handle bulk panelists import.
     */
    public function importPanelists(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $result = $this->excelService->importPanelists($request->file('file'));

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', implode(' ', $result['errors'] ?? ['Failed to import panelists.']));
        }

        return redirect()->route('admin.roster.index', ['tab' => 'panelists'])
            ->with('success', $result['message']);
    }

    /**
     * Handle bulk interview panel scores import.
     */
    public function importInterviewScores(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $result = $this->excelService->importInterviewScores($request->file('file'));

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', implode(' ', $result['errors'] ?? ['Failed to import interview scores.']));
        }

        $redirectTo = $request->input('redirect_to', 'roster');
        $targetRoute = match ($redirectTo) {
            'panel'       => route('admin.panel'),
            'leaderboard' => route('admin.dashboard', ['tab' => 'leaderboard']),
            default       => route('admin.roster.index', ['tab' => 'import']),
        };

        return redirect($targetRoute)->with('success', $result['message']);
    }

    /**
     * Handle bulk literacy assessment scores import.
     */
    public function importLiteracyScores(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $result = $this->excelService->importLiteracyScores($request->file('file'));

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', implode(' ', $result['errors'] ?? ['Failed to import literacy scores.']));
        }

        $redirectTo = $request->input('redirect_to', 'roster');
        $targetRoute = match ($redirectTo) {
            'literacy'    => route('admin.literacy'),
            'leaderboard' => route('admin.dashboard', ['tab' => 'leaderboard']),
            default       => route('admin.roster.index', ['tab' => 'import']),
        };

        return redirect($targetRoute)->with('success', $result['message']);
    }

    /**
     * Handle bulk survey responses import (baseline / endline surveys).
     */
    public function importSurveyResponses(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $result = $this->excelService->importSurveyResponses($request->file('file'));

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', implode(' ', $result['errors'] ?? ['Failed to import survey responses.']));
        }

        $redirectTo = $request->input('redirect_to', 'roster');
        $targetRoute = match ($redirectTo) {
            'analytics'   => route('admin.analytics'),
            'dashboard'   => route('admin.dashboard'),
            default       => route('admin.roster.index', ['tab' => 'import']),
        };

        return redirect($targetRoute)->with('success', $result['message']);
    }
}
