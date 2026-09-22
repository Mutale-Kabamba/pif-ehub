@php
    $bCount = $baselineCount ?? 0;
    $mCount = $midlineCount ?? 0;
    $eCount = $endlineCount ?? 0;
    $tCount = $totalResponses ?? ($bCount + $mCount + $eCount);

    $keys = array_keys($quantQuestions ?? []);
    $totalBaseSum = 0; $totalMidSum = 0; $totalEndSum = 0;
    $qCount = count($keys);

    foreach ($keys as $k) {
        $totalBaseSum += (float) ($avgScores[$k]['baseline'] ?? 0);
        $totalMidSum  += (float) ($avgScores[$k]['midline'] ?? 0);
        $totalEndSum  += (float) ($avgScores[$k]['endline'] ?? 0);
    }

    $overallBaseAvg = $qCount > 0 ? round($totalBaseSum / $qCount, 2) : 0;
    $overallMidAvg  = $qCount > 0 ? round($totalMidSum / $qCount, 2) : 0;
    $overallEndAvg  = $qCount > 0 ? round($totalEndSum / $qCount, 2) : 0;
    $overallGrowth  = round($overallEndAvg - $overallBaseAvg, 2);
@endphp

{{-- ========== SURVEY M&E HEADER ========== --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            📈 Trainee Competency Insights &amp; M&amp;E Survey Results
        </h3>
        <p style="margin: 2px 0 0; font-size: 0.82rem; color: var(--text-secondary);">
            Tracking participant growth across 11 digital and technical competency dimensions.
        </p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('admin.survey.export') }}" class="btn btn-outline btn-sm" style="padding: 5px 12px; font-size: 0.82rem;">
            📥 Export CSV
        </a>
    </div>
</div>

{{-- ========== STAGE SUMMARY KPI CARDS ========== --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-bottom: 16px;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; border-left: 4px solid var(--green-primary);">
        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--green-dark);">
            🟢 Baseline (Pre-Training)
        </div>
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 4px;">
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary);">
                {{ $overallBaseAvg > 0 ? number_format($overallBaseAvg, 2) : '—' }} <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">/5.0</span>
            </div>
            <span class="badge badge-gray" style="font-size: 0.72rem;">{{ $bCount }} responses</span>
        </div>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #7c3aed;">
            🟣 Midline (Mid-Point)
        </div>
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 4px;">
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary);">
                {{ $overallMidAvg > 0 ? number_format($overallMidAvg, 2) : '—' }} <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">/5.0</span>
            </div>
            <span class="badge badge-gray" style="font-size: 0.72rem;">{{ $mCount }} responses</span>
        </div>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; border-left: 4px solid #0ea5e9;">
        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #0284c7;">
            🔵 Endline (Post-Training)
        </div>
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 4px;">
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary);">
                {{ $overallEndAvg > 0 ? number_format($overallEndAvg, 2) : '—' }} <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">/5.0</span>
            </div>
            <span class="badge badge-gray" style="font-size: 0.72rem;">{{ $eCount }} responses</span>
        </div>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; border-left: 4px solid {{ $overallGrowth >= 0 ? '#10b981' : '#ef4444' }};">
        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: {{ $overallGrowth >= 0 ? '#059669' : '#dc2626' }};">
            📈 Overall Impact Growth
        </div>
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 4px;">
            <div style="font-size: 1.35rem; font-weight: 800; color: {{ $overallGrowth >= 0 ? '#059669' : '#dc2626' }};">
                {{ $overallGrowth > 0 ? '+' : '' }}{{ number_format($overallGrowth, 2) }}
            </div>
            <span style="font-size: 0.72rem; color: var(--text-muted);">Base → End</span>
        </div>
    </div>
</div>

{{-- ========== INTERACTIVE STAGE FILTER BAR ========== --}}
<div style="background: var(--surface-alt); border: 1px solid var(--border); border-radius: var(--radius-md) var(--radius-md) 0 0; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: none;">
    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-right: 4px;">
            Filter Survey Stage:
        </span>
        <button type="button" class="stage-filter-btn active" data-stage="all" onclick="filterSurveyChartStage('all', this)"
                style="padding: 5px 12px; font-size: 0.78rem; border-radius: 100px; border: 1px solid var(--green-primary); background: var(--green-primary); color: white; font-weight: 700; cursor: pointer; transition: all 0.15s;">
            📊 All Stages Comparison
        </button>
        <button type="button" class="stage-filter-btn" data-stage="baseline" onclick="filterSurveyChartStage('baseline', this)"
                style="padding: 5px 12px; font-size: 0.78rem; border-radius: 100px; border: 1px solid var(--border); background: #fff; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.15s;">
            🟢 Baseline Only ({{ $bCount }})
        </button>
        <button type="button" class="stage-filter-btn" data-stage="midline" onclick="filterSurveyChartStage('midline', this)"
                style="padding: 5px 12px; font-size: 0.78rem; border-radius: 100px; border: 1px solid var(--border); background: #fff; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.15s;">
            🟣 Midline Only ({{ $mCount }})
        </button>
        <button type="button" class="stage-filter-btn" data-stage="endline" onclick="filterSurveyChartStage('endline', this)"
                style="padding: 5px 12px; font-size: 0.78rem; border-radius: 100px; border: 1px solid var(--border); background: #fff; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.15s;">
            🔵 Endline Only ({{ $eCount }})
        </button>
    </div>

    <div style="font-size: 0.75rem; color: var(--text-muted);">
        Scale: 1 (Novice) to 5 (Mastery)
    </div>
</div>

{{-- ========== CHART.JS BAR CHART CONTAINER ========== --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 0 0 var(--radius-md) var(--radius-md); padding: 16px 14px 12px; margin-bottom: 18px;">
    <div style="height: 300px; position: relative;">
        <canvas id="surveyChart"></canvas>
    </div>
</div>

{{-- ========== DETAILED COMPETENCY PROGRESSION TABLE ========== --}}
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
        <h4 style="margin: 0; font-size: 0.92rem; font-weight: 700;">
            📊 Competency Dimension Score Breakdown &amp; Growth
        </h4>
        <span style="font-size:0.75rem; color:var(--text-muted);">
            Avg score (/5.00) across cohort participants
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table" id="surveyCompetencyTable" style="font-size: 0.82rem;">
            <thead>
                <tr>
                    <th style="padding: 6px 10px;">Competency Dimension</th>
                    <th class="col-stage col-baseline" style="padding: 6px 10px; text-align: center; width: 140px;">🟢 Baseline</th>
                    <th class="col-stage col-midline" style="padding: 6px 10px; text-align: center; width: 140px;">🟣 Midline</th>
                    <th class="col-stage col-endline" style="padding: 6px 10px; text-align: center; width: 140px;">🔵 Endline</th>
                    <th class="col-stage col-growth" style="padding: 6px 10px; text-align: center; width: 120px;">📈 Total Growth</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($quantQuestions ?? []) as $key => $question)
                    @php
                        $qIndex = $loop->iteration;
                        $baseVal = (float) ($avgScores[$key]['baseline'] ?? 0);
                        $midVal  = (float) ($avgScores[$key]['midline'] ?? 0);
                        $endVal  = (float) ($avgScores[$key]['endline'] ?? 0);
                        $growth  = $endVal - $baseVal;

                        $basePct = round(($baseVal / 5) * 100);
                        $midPct  = round(($midVal / 5) * 100);
                        $endPct  = round(($endVal / 5) * 100);
                    @endphp
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600; color: var(--text-primary); line-height: 1.35;">
                            <span class="badge badge-gray" style="font-size:0.72rem; margin-right:6px; font-weight:700;">Q{{ $qIndex }}</span>
                            {{ $question }}
                        </td>
                        <td class="col-stage col-baseline" style="padding: 8px 10px; text-align: center;">
                            <div style="font-weight: 700; color: #059669; font-size: 0.9rem;">
                                {{ number_format($baseVal, 2) }}
                            </div>
                            <div style="height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $basePct }}%; background: #10b981;"></div>
                            </div>
                        </td>
                        <td class="col-stage col-midline" style="padding: 8px 10px; text-align: center;">
                            <div style="font-weight: 700; color: #7c3aed; font-size: 0.9rem;">
                                {{ number_format($midVal, 2) }}
                            </div>
                            <div style="height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $midPct }}%; background: #8b5cf6;"></div>
                            </div>
                        </td>
                        <td class="col-stage col-endline" style="padding: 8px 10px; text-align: center;">
                            <div style="font-weight: 700; color: #0284c7; font-size: 0.9rem;">
                                {{ number_format($endVal, 2) }}
                            </div>
                            <div style="height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $endPct }}%; background: #0ea5e9;"></div>
                            </div>
                        </td>
                        <td class="col-stage col-growth" style="padding: 8px 10px; text-align: center;">
                            <span class="badge {{ $growth >= 0 ? 'badge-green' : 'badge-red' }}" style="font-weight: 800; font-size: 0.8rem; padding: 3px 8px;">
                                {{ $growth > 0 ? '+' : '' }}{{ number_format($growth, 2) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    let surveyChartInstance = null;

    @php
        $qKeys = array_keys($quantQuestions ?? []);
        $qShortLabels = [];
        $qFullLabels = [];
        foreach ($qKeys as $idx => $k) {
            $num = $idx + 1;
            $qShortLabels[] = "Q" . $num;
            $qFullLabels[] = "Q{$num}: " . ($quantQuestions[$k] ?? $k);
        }
    @endphp

    const labels = {!! json_encode($qShortLabels) !!};
    const fullLabels = {!! json_encode($qFullLabels) !!};

    const baselineData = {!! json_encode(array_values(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['baseline'] ?? 0;
    }, array_keys($quantQuestions ?? [])))) !!};

    const midlineData = {!! json_encode(array_values(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['midline'] ?? 0;
    }, array_keys($quantQuestions ?? [])))) !!};

    const endlineData = {!! json_encode(array_values(array_map(function($key) use ($avgScores) {
        return $avgScores[$key]['endline'] ?? 0;
    }, array_keys($quantQuestions ?? [])))) !!};

    const datasetsConfig = {
        baseline: {
            label: 'Baseline Stage (Pre-Training)',
            data: baselineData,
            backgroundColor: 'rgba(16, 185, 129, 0.75)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 1.5,
            borderRadius: 4
        },
        midline: {
            label: 'Midline Stage (Mid-Point)',
            data: midlineData,
            backgroundColor: 'rgba(139, 92, 246, 0.75)',
            borderColor: 'rgb(139, 92, 246)',
            borderWidth: 1.5,
            borderRadius: 4
        },
        endline: {
            label: 'Endline Stage (Post-Training)',
            data: endlineData,
            backgroundColor: 'rgba(14, 165, 233, 0.75)',
            borderColor: 'rgb(14, 165, 233)',
            borderWidth: 1.5,
            borderRadius: 4
        }
    };

    function renderChart(stage) {
        const canvas = document.getElementById('surveyChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const ctx = canvas.getContext('2d');

        let activeDatasets = [];
        if (stage === 'baseline') {
            activeDatasets = [datasetsConfig.baseline];
        } else if (stage === 'midline') {
            activeDatasets = [datasetsConfig.midline];
        } else if (stage === 'endline') {
            activeDatasets = [datasetsConfig.endline];
        } else {
            activeDatasets = [datasetsConfig.baseline, datasetsConfig.midline, datasetsConfig.endline];
        }

        if (surveyChartInstance) {
            surveyChartInstance.destroy();
        }

        surveyChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: activeDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 400
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11, weight: '600' }
                        },
                        title: {
                            display: true,
                            text: 'Average Rating (1–5)',
                            font: { size: 11, weight: 'bold' }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.06)'
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 30,
                            minRotation: 20,
                            font: { size: 10 }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 14,
                            padding: 12,
                            font: { size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                if (!items.length) return '';
                                const idx = items[0].dataIndex;
                                return fullLabels[idx] || labels[idx];
                            },
                            label: function(item) {
                                return ` ${item.dataset.label}: ${Number(item.raw).toFixed(2)} / 5.00`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Expose filter function globally
    window.filterSurveyChartStage = function(stage, btn) {
        document.querySelectorAll('.stage-filter-btn').forEach(b => {
            b.classList.remove('active');
            b.style.background = '#fff';
            b.style.color = 'var(--text-primary)';
            b.style.borderColor = 'var(--border)';
        });

        btn.classList.add('active');
        if (stage === 'baseline') {
            btn.style.background = '#10b981';
            btn.style.borderColor = '#10b981';
            btn.style.color = '#fff';
        } else if (stage === 'midline') {
            btn.style.background = '#8b5cf6';
            btn.style.borderColor = '#8b5cf6';
            btn.style.color = '#fff';
        } else if (stage === 'endline') {
            btn.style.background = '#0ea5e9';
            btn.style.borderColor = '#0ea5e9';
            btn.style.color = '#fff';
        } else {
            btn.style.background = 'var(--green-primary)';
            btn.style.borderColor = 'var(--green-primary)';
            btn.style.color = '#fff';
        }

        renderChart(stage);

        // Update table column highlights
        const allCols = document.querySelectorAll('#surveyCompetencyTable .col-stage');
        if (stage === 'all') {
            allCols.forEach(el => el.style.opacity = '1');
        } else {
            allCols.forEach(el => {
                if (el.classList.contains('col-' + stage) || el.classList.contains('col-growth')) {
                    el.style.opacity = '1';
                } else {
                    el.style.opacity = '0.35';
                }
            });
        }
    };

    // Initial render
    renderChart('all');
})();
</script>
