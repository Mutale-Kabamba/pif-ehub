<h3>&#x1F3C6; Live Candidate Selection Leaderboard</h3>

<div class="info-box" style="margin-bottom: 20px;">
    Aggregates practical literacy metrics (Assessor out of 20) and Panel interview scores
    (Averaged across active logins out of 20) for a total out of 40.
</div>

@if(isset($leaderboard) && count($leaderboard) > 0)
    <a href="?tab=leaderboard&amp;format=csv" class="btn btn-primary export-btn">
        &#x1F4E5; Export CSV
    </a>

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
                @foreach($leaderboard as $entry)
                    <tr class="{{ $entry['rank'] <= 10 ? 'accepted' : '' }}">
                        <td><strong>#{{ $entry['rank'] }}</strong></td>
                        <td>{{ $entry['candidate_name'] }}</td>
                        <td><strong>{{ number_format($entry['grand_total'], 1) }}</strong></td>
                        <td>{{ number_format($entry['literacy_score'], 1) }}</td>
                        <td>{{ number_format($entry['interview_score'], 1) }}</td>
                        <td>{{ $entry['panelists_scored'] }}</td>
                        <td>
                            @if($entry['rank'] <= 10)
                                <span style="color: #2e7d32;">&#x1F7E2; ACCEPTED</span>
                            @else
                                <span style="color: #C62828;">&#x1F534; WAITLIST / CUT-OFF</span>
                            @endif
                        </td>
                        <td>{{ $entry['assessment_date'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="info-box info" style="margin-top: 20px;">
        Leaderboard is functional, but no candidate evaluation matrices have been recorded yet.
    </div>
@endif
