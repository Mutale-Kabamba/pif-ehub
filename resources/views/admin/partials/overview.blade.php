{{-- ===== COMPACT OVERVIEW TAB ===== --}}

{{-- Row 1: Quota & Pipeline Health in 2 Compact Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px; margin-bottom: 16px;">

    {{-- Left: Cohort Quotas --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                🎯 Cohort Selection Quotas
            </h4>
            <a href="?tab=leaderboard" style="font-size: 0.78rem; color: var(--blue-primary); text-decoration: none; font-weight: 600;">Full Ranks →</a>
        </div>

        {{-- Female Quota --}}
        <div style="margin-bottom: 10px; padding: 10px 12px; background: rgba(216, 27, 96, 0.04); border: 1px solid rgba(216, 27, 96, 0.12); border-radius: var(--radius-sm);">
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                <span style="font-weight: 600; color: #d81b60;">👩 Female Track (Top 7)</span>
                <span style="color: var(--text-secondary);">Scored: <strong>{{ $femaleEvaluatedCount ?? 0 }}/{{ $femaleCandidatesCount ?? 0 }}</strong> · Top: <strong>{{ number_format($topFemaleScore ?? 0, 1) }}/40</strong></span>
            </div>
            @php $femPercent = ($femaleCandidatesCount ?? 0) > 0 ? min(100, round((($femaleEvaluatedCount ?? 0) / $femaleCandidatesCount) * 100)) : 0; @endphp
            <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                <div style="width: {{ $femPercent }}%; height: 100%; background: #d81b60; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Male Quota --}}
        <div style="padding: 10px 12px; background: rgba(21, 101, 192, 0.04); border: 1px solid rgba(21, 101, 192, 0.12); border-radius: var(--radius-sm);">
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                <span style="font-weight: 600; color: #1565c0;">👨 Male Track (Top 3)</span>
                <span style="color: var(--text-secondary);">Scored: <strong>{{ $maleEvaluatedCount ?? 0 }}/{{ $maleCandidatesCount ?? 0 }}</strong> · Top: <strong>{{ number_format($topMaleScore ?? 0, 1) }}/40</strong></span>
            </div>
            @php $malePercent = ($maleCandidatesCount ?? 0) > 0 ? min(100, round((($maleEvaluatedCount ?? 0) / $maleCandidatesCount) * 100)) : 0; @endphp
            <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                <div style="width: {{ $malePercent }}%; height: 100%; background: #1565c0; border-radius: 3px;"></div>
            </div>
        </div>
    </div>

    {{-- Right: Evaluation Pipeline Progress --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 16px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
                📊 Pipeline Health
            </h4>
            <span style="font-size: 0.78rem; color: var(--text-muted);">{{ $totalCandidates ?? 0 }} Total Candidates</span>
        </div>

        {{-- Interview Progress --}}
        <div style="margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 3px;">
                <span style="color: var(--text-primary); font-weight: 600;">🎙️ Interview Completion</span>
                <span style="color: #2563eb; font-weight: 600;">{{ $interviewScoredCount ?? 0 }}/{{ $totalCandidates ?? 0 }} ({{ $totalCandidates > 0 ? round(($interviewScoredCount / $totalCandidates) * 100) : 0 }}%)</span>
            </div>
            <div style="height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                <div style="width: {{ $totalCandidates > 0 ? min(100, round(($interviewScoredCount / $totalCandidates) * 100)) : 0 }}%; height: 100%; background: #2563eb;"></div>
            </div>
        </div>

        {{-- Literacy Progress --}}
        <div style="margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 3px;">
                <span style="color: var(--text-primary); font-weight: 600;">💻 Literacy Completion</span>
                <span style="color: #7c3aed; font-weight: 600;">{{ $literacyScoredCount ?? 0 }}/{{ $totalCandidates ?? 0 }} ({{ $totalCandidates > 0 ? round(($literacyScoredCount / $totalCandidates) * 100) : 0 }}%)</span>
            </div>
            <div style="height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                <div style="width: {{ $totalCandidates > 0 ? min(100, round(($literacyScoredCount / $totalCandidates) * 100)) : 0 }}%; height: 100%; background: #7c3aed;"></div>
            </div>
        </div>

        {{-- Survey Submissions --}}
        <div>
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 3px;">
                <span style="color: var(--text-primary); font-weight: 600;">📋 Survey Submissions</span>
                <span style="color: var(--green-dark); font-weight: 600;">{{ ($baselineCount ?? 0) + ($endlineCount ?? 0) }} forms ({{ $baselineCount ?? 0 }} Base · {{ $endlineCount ?? 0 }} End)</span>
            </div>
            <div style="height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                <div style="width: {{ $totalCandidates > 0 ? min(100, round((($baselineCount ?? 0) / $totalCandidates) * 100)) : 0 }}%; height: 100%; background: var(--green-primary);"></div>
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Compact Top Performers Preview --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">
            ⭐ Leading Candidates Preview
        </h4>
        <div style="display: flex; gap: 6px;">
            <a href="{{ route('admin.leaderboard', ['format' => 'csv']) }}" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.78rem;">
                📥 CSV
            </a>
            <a href="?tab=leaderboard" class="btn btn-primary btn-sm" style="padding: 2px 8px; font-size: 0.78rem;">
                Full Leaderboard →
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 14px;">
        {{-- Female Leaders --}}
        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #d81b60; margin-bottom: 6px;">
                👩 Top 7 Females (Accepted)
            </div>
            <table class="data-table" style="font-size: 0.82rem;">
                <thead>
                    <tr>
                        <th style="padding: 4px 8px;">Rank</th>
                        <th style="padding: 4px 8px;">Candidate</th>
                        <th style="padding: 4px 8px; text-align: center;">Total (/40)</th>
                        <th style="padding: 4px 8px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($topFemales ?? []) as $fEntry)
                        <tr class="{{ $fEntry['status'] === 'ACCEPTED' ? 'accepted' : '' }}">
                            <td style="padding: 4px 8px;"><strong>#{{ $fEntry['rank'] }}</strong></td>
                            <td style="padding: 4px 8px;">{{ $fEntry['candidate_name'] }}</td>
                            <td style="padding: 4px 8px; text-align: center;"><strong>{{ number_format($fEntry['grand_total'], 1) }}</strong></td>
                            <td style="padding: 4px 8px; text-align: center;">
                                <span style="color: #2e7d32; font-weight: 700; font-size: 0.75rem;">🟢 ACCEPTED</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 8px;">No candidates scored yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Male Leaders --}}
        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #1565c0; margin-bottom: 6px;">
                👨 Top 3 Males (Accepted)
            </div>
            <table class="data-table" style="font-size: 0.82rem;">
                <thead>
                    <tr>
                        <th style="padding: 4px 8px;">Rank</th>
                        <th style="padding: 4px 8px;">Candidate</th>
                        <th style="padding: 4px 8px; text-align: center;">Total (/40)</th>
                        <th style="padding: 4px 8px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($topMales ?? []) as $mEntry)
                        <tr class="{{ $mEntry['status'] === 'ACCEPTED' ? 'accepted' : '' }}">
                            <td style="padding: 4px 8px;"><strong>#{{ $mEntry['rank'] }}</strong></td>
                            <td style="padding: 4px 8px;">{{ $mEntry['candidate_name'] }}</td>
                            <td style="padding: 4px 8px; text-align: center;"><strong>{{ number_format($mEntry['grand_total'], 1) }}</strong></td>
                            <td style="padding: 4px 8px; text-align: center;">
                                <span style="color: #2e7d32; font-weight: 700; font-size: 0.75rem;">🟢 ACCEPTED</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 8px;">No candidates scored yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
