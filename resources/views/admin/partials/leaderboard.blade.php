<h3>🏆 Live Gender-Segmented Selection Leaderboard</h3>

<div class="info-box" style="margin-bottom: 20px;">
    Separated Selection Tracks: <strong>Top 7 Females</strong> and <strong>Top 3 Males</strong> are dynamically highlighted as ACCEPTED based on cumulative assessment scores (Literacy metrics out of 20 and average Panel values out of 20).
</div>

@if(isset($leaderboard) && ($leaderboard['females']->count() > 0 || $leaderboard['males']->count() > 0))
    <a href="{{ route('admin.leaderboard', ['format' => 'csv']) }}" class="btn btn-primary export-btn" style="margin-bottom: 20px; display: inline-block;">
        📥 Export Separated Leaderboards CSV
    </a>
    &nbsp;
    <a href="{{ route('admin.scoresheet') }}" class="btn btn-primary export-btn" style="margin-bottom: 20px; display: inline-block; background:#1565c0;">
        📋 Download Panelist Score Sheets CSV
    </a>

    <div style="margin-bottom: 40px;">
        <h4 style="color: #d81b60; margin-bottom: 12px; font-weight: 600;">👩‍🦰 Female Selection Track (Top 7 Advance)</h4>
        @if($leaderboard['females']->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate Name</th>
                            <th>Grand Total Score (/40)</th>
                            <th>Literacy Score (/20)</th>
                            <th>Interview Score (/20)</th>
                            <th>Panelists Scored</th>
                            <th>Admission Status</th>
                            <th>Assessment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard['females'] as $entry)
                            <tr class="{{ $entry['status'] === 'ACCEPTED' ? 'accepted' : '' }}">
                                <td><strong>#{{ $entry['rank'] }}</strong></td>
                                <td>{{ $entry['candidate_name'] }}</td>
                                <td><strong>{{ number_format($entry['grand_total'], 1) }}</strong></td>
                                <td>{{ number_format($entry['literacy_score'], 1) }}</td>
                                <td>{{ number_format($entry['interview_score'], 1) }}</td>
                                <td>{{ $entry['panelists_scored'] }}</td>
                                <td>
                                    @if($entry['status'] === 'ACCEPTED')
                                        <span style="color: #2e7d32; font-weight: bold;">🟢 ACCEPTED (Top 7)</span>
                                    @else
                                        <span style="color: #C62828;">🔴 WAITLIST</span>
                                    @endif
                                </td>
                                <td>{{ $entry['assessment_date'] ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="info-box info">No female candidate scores found yet.</div>
        @endif
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="color: #1565c0; margin-bottom: 12px; font-weight: 600;">👨 Male Selection Track (Top 3 Advance)</h4>
        @if($leaderboard['males']->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate Name</th>
                            <th>Grand Total Score (/40)</th>
                            <th>Literacy Score (/20)</th>
                            <th>Interview Score (/20)</th>
                            <th>Panelists Scored</th>
                            <th>Admission Status</th>
                            <th>Assessment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard['males'] as $entry)
                            <tr class="{{ $entry['status'] === 'ACCEPTED' ? 'accepted' : '' }}">
                                <td><strong>#{{ $entry['rank'] }}</strong></td>
                                <td>{{ $entry['candidate_name'] }}</td>
                                <td><strong>{{ number_format($entry['grand_total'], 1) }}</strong></td>
                                <td>{{ number_format($entry['literacy_score'], 1) }}</td>
                                <td>{{ number_format($entry['interview_score'], 1) }}</td>
                                <td>{{ $entry['panelists_scored'] }}</td>
                                <td>
                                    @if($entry['status'] === 'ACCEPTED')
                                        <span style="color: #2e7d32; font-weight: bold;">🟢 ACCEPTED (Top 3)</span>
                                    @else
                                        <span style="color: #C62828;">🔴 WAITLIST</span>
                                    @endif
                                </td>
                                <td>{{ $entry['assessment_date'] ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="info-box info">No male candidate scores found yet.</div>
        @endif
    </div>
@else
    <div class="info-box info" style="margin-top: 20px;">
        Leaderboard is functional, but no candidate evaluation matrices have been recorded yet.
    </div>
@endif