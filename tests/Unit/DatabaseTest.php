<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;

/**
 * This test ensures that testings are not going to be executed over regular database
 */
it('confirms the database connection is the correct one for testings', function () {
    $is_testing_db = count(explode('_testing', env('DB_DATABASE'))) == 2;

    expect($is_testing_db)->toBe(true);

    $database = DB::select('SELECT version()');

    expect($database)->toBeArray();
    expect($database[0])->toBeObject();
    expect($database[0])->toHaveKey('version');
});