<h3>&#x1F4CA; Collective Trainee Survey Insights</h3>

<div style="display: flex; justify-content: flex-end; margin: 0 0 12px;">
    <a href="{{ route('admin.survey.export') }}" class="btn" style="text-decoration: none; padding: 8px 14px; font-size: 0.9rem;">
        Export Survey Results (CSV)
    </a>
</div>

<!-- Metric Cards -->
<div class="metric-cards">
    <div class="metric-card">
        <div class="value">{{ $totalResponses ?? 0 }}</div>
        <div class="label">Total Submitted Survey Forms</div>
    </div>
    <div class="metric-card">
        <div class="value">{{ $baselineCount ?? 0 }}</div>
        <div class="label">Baseline Entries (Day 1)</div>
    </div>
    <div class="metric-card">
        <div class="value">{{ $endlineCount ?? 0 }}</div>
        <div class="label">Endline Entries (Day 156)</div>
    </div>
</div>

<!-- Chart.js Canvas -->
<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-top: 24px;">
    <canvas id="surveyChart" height="400"></canvas>
</div>

<div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-top: 16px; overflow-x: auto;">
    <h4 style="margin-top: 0;">Average Scores by Question</h4>
    <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #e6e6e6; padding: 10px 8px;">Question</th>
                <th style="text-align: center; border-bottom: 1px solid #e6e6e6; padding: 10px 8px;">Baseline</th>
                <th style="text-align: center; border-bottom: 1px solid #e6e6e6; padding: 10px 8px;">Endline</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($quantQuestions ?? []) as $key => $question)
                <tr>
                    <td style="vertical-align: top; border-bottom: 1px solid #f0f0f0; padding: 10px 8px;">
                        {{ $question }}
                    </td>
                    <td style="text-align: center; border-bottom: 1px solid #f0f0f0; padding: 10px 8px;">
                        {{ number_format((float) (($avgScores[$key]['baseline'] ?? 0)), 2) }}
                    </td>
                    <td style="text-align: center; border-bottom: 1px solid #f0f0f0; padding: 10px 8px;">
                        {{ number_format((float) (($avgScores[$key]['endline'] ?? 0)), 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
(function() {
    const canvas = document.getElementById('surveyChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const ctx = canvas.getContext('2d');

    // Force plain indexed arrays so Chart.js receives valid label/value lists.
    const labels = {!! json_encode(array_values(array_map(function($q) {
        return substr($q, 0, 40) . (strlen($q) > 40 ? '...' : '');
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
                    label: 'Baseline',
                    data: baselineData,
                    backgroundColor: 'rgba(89, 179, 63, 0.6)',
                    borderColor: 'rgba(89, 179, 63, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Endline',
                    data: endlineData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
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
                    ticks: { stepSize: 0.5 },
                    title: {
                        display: true,
                        text: 'Average Score (1-5)'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 30,
                        font: { size: 10 }
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Baseline vs Endline: Average Self-Assessment Scores',
                    font: { size: 16 }
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });
})();
</script>
