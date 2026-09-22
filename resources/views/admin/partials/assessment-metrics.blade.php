{{-- ===== COMPACT ASSESSMENT & INTERVIEW METRICS ===== --}}

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
    <div>
        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;">🎯 Assessment &amp; Interview Granular Breakdown</h3>
        <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--text-secondary);">
            Evaluation analytics across 4 interview criteria, panelist scoring activity, and 10 digital literacy practical tasks.
        </p>
    </div>
    <a href="{{ route('admin.scoresheet') }}" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
        📋 Score Sheets CSV
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px; margin-bottom: 14px;">

    {{-- Interview Criteria Breakdown --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px;">
        <h4 style="margin: 0 0 10px; font-size: 0.88rem; color: #2563eb; font-weight: 700;">
            🎙️ Interview Criteria Averages (/5.00)
        </h4>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            @php
                $criteriaColors = [
                    'crit1_motivation'    => '#2563eb',
                    'crit2_availability'  => '#059669',
                    'crit3_resilience'    => '#d97706',
                    'crit4_communication' => '#7c3aed',
                ];
                $criteriaNames = [
                    'crit1_motivation'    => 'Motivation',
                    'crit2_availability'  => 'Availability',
                    'crit3_resilience'    => 'Resilience',
                    'crit4_communication' => 'Communication',
                ];
            @endphp

            @foreach(($interviewCriteriaAverages ?? []) as $critKey => $avgVal)
                @php
                    $color = $criteriaColors[$critKey] ?? '#2563eb';
                    $label = $criteriaNames[$critKey] ?? ucfirst($critKey);
                    $percentage = round(($avgVal / 5) * 100);
                @endphp
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 2px;">
                        <span style="font-weight: 600; color: var(--text-primary);">{{ $label }}</span>
                        <span style="font-weight: 700; color: {{ $color }};">{{ number_format($avgVal, 2) }}/5 ({{ $percentage }}%)</span>
                    </div>
                    <div style="height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $percentage }}%; height: 100%; background: {{ $color }};"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 10px; font-size: 0.78rem; color: var(--text-muted);">
            Avg Total: <strong>{{ number_format($avgInterviewScore ?? 0, 1) }} / 20</strong> across {{ $validPanelScoresCount ?? 0 }} score sheets.
        </div>
    </div>

    {{-- Panelist Activity Breakdown --}}
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px;">
        <h4 style="margin: 0 0 10px; font-size: 0.88rem; color: #059669; font-weight: 700;">
            👥 Panelist Scoring Summary
        </h4>

        <div style="overflow-x: auto;">
            <table class="data-table" style="font-size: 0.8rem;">
                <thead>
                    <tr>
                        <th style="padding: 3px 6px;">Panelist</th>
                        <th style="padding: 3px 6px;">Panel</th>
                        <th style="padding: 3px 6px; text-align: center;">Scored</th>
                        <th style="padding: 3px 6px; text-align: center;">Avg Given</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($panelistStats ?? []) as $pStat)
                        <tr>
                            <td style="padding: 3px 6px;"><strong>{{ $pStat['name'] }}</strong></td>
                            <td style="padding: 3px 6px;">
                                <span class="badge {{ $pStat['panel'] === 'A' ? 'badge-blue' : ($pStat['panel'] === 'B' ? 'badge-green' : 'badge-gray') }}" style="font-size: 0.7rem; padding: 1px 5px;">
                                    {{ $pStat['panel'] ?: 'Cover' }}
                                </span>
                            </td>
                            <td style="padding: 3px 6px; text-align: center;"><strong>{{ $pStat['scored_count'] }}</strong></td>
                            <td style="padding: 3px 6px; text-align: center;">{{ number_format($pStat['avg_score_given'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 8px;">No data logged.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Practical Literacy Task Breakdown --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px;">
    <h4 style="margin: 0 0 8px; font-size: 0.88rem; color: #7c3aed; font-weight: 700;">
        💻 Digital Literacy Task Performance (/2 per module)
    </h4>

    <div style="overflow-x: auto;">
        <table class="data-table" style="font-size: 0.8rem;">
            <thead>
                <tr>
                    <th style="width: 30px; padding: 4px 6px;">#</th>
                    <th style="padding: 4px 6px;">Task Description</th>
                    <th style="width: 100px; text-align: center; padding: 4px 6px;">Avg (/2)</th>
                    <th style="width: 90px; text-align: center; padding: 4px 6px;">Mastery</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($literacyTaskAverages ?? []) as $tIndex => $tData)
                    @php
                        $scoreVal = (float) $tData['average'];
                        $rate = round(($scoreVal / 2) * 100);
                        $badgeBg = $rate >= 80 ? '#2e7d32' : ($rate >= 50 ? '#d97706' : '#c62828');
                    @endphp
                    <tr>
                        <td style="padding: 3px 6px;"><strong>{{ $loop->iteration }}</strong></td>
                        <td style="padding: 3px 6px;">{{ $tData['task'] }}</td>
                        <td style="text-align: center; font-weight: 700; padding: 3px 6px;">{{ number_format($scoreVal, 2) }}</td>
                        <td style="text-align: center; padding: 3px 6px;">
                            <span style="background: {{ $badgeBg }}; color: white; padding: 1px 6px; border-radius: 10px; font-size: 0.72rem; font-weight: 700;">
                                {{ $rate }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 10px;">No practical data recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
