<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
    <div>
        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;">📈 Trainee Baseline vs. Endline Competency Insights</h3>
        <p style="margin: 2px 0 0; font-size: 0.78rem; color: var(--text-secondary);">
            Self-assessment scores across 11 digital and technical competency dimensions.
        </p>
    </div>
    <a href="{{ route('admin.survey.export') }}" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 0.78rem;">
        📥 Export Survey CSV
    </a>
</div>

{{-- Chart.js Canvas Container (Compact) --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px; margin-bottom: 14px;">
    <div style="height: 260px; position: relative;">
        <canvas id="surveyChart"></canvas>
    </div>
</div>

{{-- Compact Question Averages Table --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px;">
    <h4 style="margin: 0 0 8px; font-size: 0.88rem; font-weight: 700;">Average Scores by Competency (/5.00)</h4>
    <div style="overflow-x: auto;">
        <table class="data-table" style="font-size: 0.82rem;">
            <thead>
                <tr>
                    <th style="padding: 4px 8px;">Competency Dimension</th>
                    <th style="padding: 4px 8px; text-align: center; width: 100px;">Baseline</th>
                    <th style="padding: 4px 8px; text-align: center; width: 100px;">Endline</th>
                    <th style="padding: 4px 8px; text-align: center; width: 100px;">Growth</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($quantQuestions ?? []) as $key => $question)
                    @php
                        $baseVal = (float) ($avgScores[$key]['baseline'] ?? 0);
                        $endVal  = (float) ($avgScores[$key]['endline'] ?? 0);
                        $growth  = $endVal - $baseVal;
                    @endphp
                    <tr>
                        <td style="padding: 4px 8px;">
                            {{ $question }}
                        </td>
                        <td style="padding: 4px 8px; text-align: center; font-weight: 600; color: #2e7d32;">
                            {{ number_format($baseVal, 2) }}
                        </td>
                        <td style="padding: 4px 8px; text-align: center; font-weight: 600; color: #1565c0;">
                            {{ number_format($endVal, 2) }}
                        </td>
                        <td style="padding: 4px 8px; text-align: center; font-weight: 700; color: {{ $growth >= 0 ? '#2e7d32' : '#c62828' }};">
                            {{ $growth > 0 ? '+' : '' }}{{ number_format($growth, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    const canvas = document.getElementById('surveyChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const ctx = canvas.getContext('2d');

    const labels = {!! json_encode(array_values(array_map(function($q) {
        return substr($q, 0, 32) . (strlen($q) > 32 ? '...' : '');
    }, $quantQuestions ?? []))) !!};

    const baselineData = {!! json_encode(array_values(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['baseline'] ?? 0;
    }, array_keys($quantQuestions ?? [])))) !!};

    const endlineData = {!! json_encode(array_values(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['endline'] ?? 0;
    }, array_keys($quantQuestions ?? [])))) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Baseline (Day 1)',
                    data: baselineData,
                    backgroundColor: 'rgba(89, 179, 63, 0.7)',
                    borderColor: 'rgba(89, 179, 63, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Endline (Day 156)',
                    data: endlineData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    title: { display: false }
                },
                x: {
                    ticks: {
                        maxRotation: 30,
                        minRotation: 20,
                        font: { size: 9 }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            }
        }
    });
})();
</script>
