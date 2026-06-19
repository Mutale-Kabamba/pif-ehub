<h3>&#x1F4CA; Collective Trainee Survey Insights</h3>

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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('surveyChart').getContext('2d');

    // Labels shortened from question text
    const labels = {!! json_encode(array_map(function($q) {
        // Extract first ~40 chars as short label
        return substr($q, 0, 40) . (strlen($q) > 40 ? '...' : '');
    }, $quantQuestions ?? [])) !!};

    // Chart data passed from controller
    const baselineData = {!! json_encode(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['baseline'] ?? 0;
    }, array_keys($quantQuestions ?? []))) !!};

    const endlineData = {!! json_encode(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['endline'] ?? 0;
    }, array_keys($quantQuestions ?? []))) !!};

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
});
</script>
@endsection
