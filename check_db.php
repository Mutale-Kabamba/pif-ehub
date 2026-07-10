<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Panelist score totals (expected from score sheet) ===" . PHP_EOL;
$expected = [
    'Blessing'   => ['count' => 5,  'total' => 70],
    'Bracious'   => ['count' => 4,  'total' => 52],
    'Sarah'      => ['count' => 5,  'total' => 51],
    'Florence'   => ['count' => 9,  'total' => 116],
    'Jacqueline' => ['count' => 9,  'total' => 118],
    'Mutale'     => ['count' => 9,  'total' => 122],
];

foreach ($expected as $name => $exp) {
    $u = Illuminate\Support\Facades\DB::table('users')->where('panelist_name', $name)->first();
    if (!$u) { printf("%-12s  USER NOT FOUND\n", $name); continue; }
    $row = Illuminate\Support\Facades\DB::table('panel_scores')
        ->where('panelist_id', $u->id)
        ->where('is_valid', 1)
        ->selectRaw('COUNT(*) as cnt, SUM(crit1_motivation+crit2_availability+crit3_resilience+crit4_communication) as tot')
        ->first();
    $cnt = (int)$row->cnt;
    $tot = (int)$row->tot;
    $ok  = ($cnt === $exp['count'] && $tot === $exp['total']) ? 'OK' : 'MISMATCH';
    printf("%-12s  scores=%-2d (exp %d)  total=%-3d (exp %d)  %s\n",
        $name, $cnt, $exp['count'], $tot, $exp['total'], $ok);
}
$credentials = [
    ['email' => 'super@pif.zm',      'password' => 'PIF_Admin_2026', 'login_as' => 'Mutale (Super User)'],
    ['email' => 'blessing@pif.zm',   'password' => 'PIF_Ble_2026',  'login_as' => 'Blessing'],
    ['email' => 'sarah@pif.zm',      'password' => 'PIF_Sar_2026',  'login_as' => 'Sarah'],
    ['email' => 'bracious@pif.zm',   'password' => 'PIF_Bra_2026',  'login_as' => 'Bracious'],
    ['email' => 'jacqueline@pif.zm', 'password' => 'PIF_Jac_2026',  'login_as' => 'Jacqueline'],
    ['email' => 'florence@pif.zm',   'password' => 'PIF_Flo_2026',  'login_as' => 'Florence'],
    ['email' => 'mwiinga@pif.zm',    'password' => 'PIF_Mwi_2026',  'login_as' => 'Mwiinga'],
];

foreach ($credentials as $c) {
    $user = Illuminate\Support\Facades\DB::table('users')->where('email', $c['email'])->first();
    if (!$user) {
        printf("%-30s  %-12s  %s\n", $c['login_as'], 'MISSING', 'User not in DB');
        continue;
    }
    $ok = Illuminate\Support\Facades\Hash::check($c['password'], $user->password);
    printf("%-30s  %-12s  pw_len=%-5d  %s\n",
        $c['login_as'],
        $ok ? 'LOGIN OK' : 'FAIL',
        strlen($user->password),
        $ok ? '' : 'password mismatch'
    );
}
