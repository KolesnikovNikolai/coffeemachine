<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'pgsql:host=192.168.99.100;port=5432;dbname=coffee_machine_test';

return $db;
