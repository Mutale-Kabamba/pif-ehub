<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== User credential check ===" . PHP_EOL;
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
