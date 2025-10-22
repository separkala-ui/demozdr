<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "تعداد کاربران فعلی: " . User::count() . PHP_EOL;

$deleted = User::whereNotIn('id', [1, 2, 3])->delete();

echo "تعداد کاربران حذف شده: " . $deleted . PHP_EOL;
echo "تعداد کاربران باقی‌مانده: " . User::count() . PHP_EOL;

echo "کاربران باقی‌مانده:" . PHP_EOL;
foreach(User::all() as $user) {
    echo $user->id . ': ' . $user->email . ' (' . $user->first_name . ' ' . $user->last_name . ')' . PHP_EOL;
}
