<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "Roles count: " . Role::count() . PHP_EOL;
echo "Permissions count: " . Permission::count() . PHP_EOL;
echo "User count: " . \App\Models\User::count() . PHP_EOL;
