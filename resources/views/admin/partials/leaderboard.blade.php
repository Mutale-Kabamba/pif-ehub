<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
    <div>
        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;">🏆 Live Gender-Segmented Leaderboard</h3>
        <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--text-secondary);">
            Top 7 Females and Top 3 Males advance to the 2026 Cohort based on Grand Total (/40).
        </p>
    </div>
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <a href="{{ route('admin.roster.index', ['tab' => 'import']) }}" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
            📊 Import Excel Results
        </a>
        <a href="{{ route('admin.roster.index') }}" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
            👥 Manage Roster
        </a>
        @if(isset($leaderboard) && ($leaderboard['females']->count() > 0 || $leaderboard['males']->count() > 0))
            <a href="{{ route('admin.leaderboard', ['format' => 'csv']) }}" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
                📥 Export CSV
            </a>
            <a href="{{ route('admin.scoresheet') }}" class="btn btn-primary btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
                📋 Score Sheets CSV
            </a>
        @endif
    </div>
</div>

@if(isset($leaderboard) && ($leaderboard['females']->count() > 0 || $leaderboard['males']->count() > 0))
    {{-- Female Track --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <h4 style="color: #d81b60; margin: 0; font-size: 0.9rem; font-weight: 700;">
                👩‍🦰 Female Selection Track (Top 7 Advance)
            </h4>
            <span class="badge badge-pink" style="font-size: 0.72rem; padding: 2px 6px;">7 Seats</span>
        </div>
        @if($leaderboard['females']->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table" style="font-size: 0.82rem;">
                    <thead>
                        <tr>
                            <th style="padding: 4px 8px; width: 45px;">Rank</th>
                            <th style="padding: 4px 8px;">Candidate</th>
                            <th style="padding: 4px 8px; text-align: center;">Total (/40)</th>
                            <th style="padding: 4px 8px; text-align: center;">Literacy (/20)</th>
                            <th style="padding: 4px 8px; text-align: center;">Interview (/20)</th>
                            <th style="padding: 4px 8px; text-align: center;">Panelists</th>
                            <th style="padding: 4px 8px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard['females'] as $entry)
                            <tr class="{{ $entry['status'] === 'ACCEPTED' ? 'accepted' : '' }}">
                                <td style="padding: 4px 8px;"><strong>#{{ $entry['rank'] }}</strong></td>
                                <td style="padding: 4px 8px; font-weight: 500;">{{ $entry['candidate_name'] }}</td>
                                <td style="padding: 4px 8px; text-align: center;"><strong>{{ number_format($entry['grand_total'], 1) }}</strong></td>
                                <td style="padding: 4px 8px; text-align: center;">{{ number_format($entry['literacy_score'], 1) }}</td>
                                <td style="padding: 4px 8px; text-align: center;">{{ number_format($entry['interview_score'], 1) }}</td>
                                <td style="padding: 4px 8px; text-align: center;">{{ $entry['panelists_scored'] }}</td>
                                <td style="padding: 4px 8px; text-align: center;">
                                    @if($entry['status'] === 'ACCEPTED')
                                        <span style="color: #2e7d32; font-weight: 700; font-size: 0.75rem;">🟢 ACCEPTED</span>
                                    @else
                                        <span style="color: #c62828; font-size: 0.75rem;">🔴 WAITLIST</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="font-size: 0.8rem; color: var(--text-muted); padding: 8px;">No female candidate scores found yet.</div>
        @endif
    </div>

    {{-- Male Track --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <h4 style="color: #1565c0; margin: 0; font-size: 0.9rem; font-weight: 700;">
                👨 Male Selection Track (Top 3 Advance)
            </h4>
            <span class="badge badge-blue" style="font-size: 0.72rem; padding: 2px 6px;">3 Seats</span>
        </div>
        @if($leaderboard['males']->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table" style="font-size: 0.82rem;">
                    <thead>
                        <tr>
                            <th style="padding: 4px 8px; width: 45px;">Rank</th>
                            <th style="padding: 4px 8px;">Candidate</th>
                            <th style="padding: 4px 8px; text-align: center;">Total (/40)</th>
                            <th style="padding: 4px 8px; text-align: center;">Literacy (/20)</th>
                            <th style="padding: 4px 8px; text-align: center;">Interview (/20)</th>
                            <th style="padding: 4px 8px; text-align: center;">Panelists</th>
                            <th style="padding: 4px 8px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard['males'] as $entry)
                            <tr class="{{ $entry['status'] === 'ACCEPTED' ? 'accepted' : '' }}">
                                <td style="padding: 4px 8px;"><strong>#{{ $entry['rank'] }}</strong></td>
                                <td style="padding: 4px 8px; font-weight: 500;">{{ $entry['candidate_name'] }}</td>
                                <td style="padding: 4px 8px; text-align: center;"><strong>{{ number_format($entry['grand_total'], 1) }}</strong></td>
                                <td style="padding: 4px 8px; text-align: center;">{{ number_format($entry['literacy_score'], 1) }}</td>
                                <td style="padding: 4px 8px; text-align: center;">{{ number_format($entry['interview_score'], 1) }}</td>
                                <td style="padding: 4px 8px; text-align: center;">{{ $entry['panelists_scored'] }}</td>
                                <td style="padding: 4px 8px; text-align: center;">
                                    @if($entry['status'] === 'ACCEPTED')
                                        <span style="color: #2e7d32; font-weight: 700; font-size: 0.75rem;">🟢 ACCEPTED</span>
                                    @else
                                        <span style="color: #c62828; font-size: 0.75rem;">🔴 WAITLIST</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="font-size: 0.8rem; color: var(--text-muted); padding: 8px;">No male candidate scores found yet.</div>
        @endif
    </div>
@else
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
        No candidate evaluation scores recorded yet.
    </div>
@endif