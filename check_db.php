<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Preview scoresheet output for Panel A
$panelists = App\Models\User::whereIn('panel', ['A', 'B'])
    ->orderBy('panel')->orderBy('panelist_name')->get();

foreach ($panelists as $panelist) {
    echo PHP_EOL . "=== Panelist: {$panelist->panelist_name} (Panel {$panelist->panel}) ===" . PHP_EOL;
    printf("%-32s  %3s %3s %3s %3s  %5s  %s\n", 'Candidate', 'Mot', 'Ava', 'Res', 'Com', 'Total', 'Status');
    echo str_repeat('-', 72) . PHP_EOL;

    $candidates  = App\Models\Candidate::where('panel', $panelist->panel)->orderBy('name')->get();
    $validScores = App\Models\PanelScore::where('panelist_id', $panelist->id)
        ->where('is_valid', 1)->get()->keyBy('candidate_id');
    $grandTotal  = 0;

    foreach ($candidates as $c) {
        $s = $validScores->get($c->id);
        if ($s) {
            $t = $s->crit1_motivation + $s->crit2_availability + $s->crit3_resilience + $s->crit4_communication;
            $grandTotal += $t;
            printf("%-32s  %3d %3d %3d %3d  %5d  Scored\n",
                $c->name, $s->crit1_motivation, $s->crit2_availability,
                $s->crit3_resilience, $s->crit4_communication, $t);
        } else {
            printf("%-32s  %3s %3s %3s %3s  %5s  DID NOT ATTEND\n",
                $c->name, '—', '—', '—', '—', '—');
        }
    }
    echo str_repeat('-', 72) . PHP_EOL;
    printf("%-32s  %3s %3s %3s %3s  %5d\n", 'PANELIST TOTAL', '', '', '', '', $grandTotal);
}
