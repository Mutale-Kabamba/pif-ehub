<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::select('name','panel','role')->get();
echo "=== USERS (" . $users->count() . ") ===" . PHP_EOL;
foreach ($users as $u) {
    echo $u->name . ' | panel=' . $u->panel . ' | role=' . $u->role . PHP_EOL;
}

echo PHP_EOL;

$cands = App\Models\Candidate::select('name','panel','gender')->orderBy('panel')->orderBy('name')->get();
echo "=== CANDIDATES (" . $cands->count() . ") ===" . PHP_EOL;
foreach ($cands as $c) {
    echo $c->name . ' | Panel ' . $c->panel . ' | ' . $c->gender . PHP_EOL;
}
