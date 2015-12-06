<?php

$db = new \PDO($argv[1]);

$stmt = $db->prepare('INSERT into users (name, age, gender) VALUES (:name, :age, :gender)');
$stmt->execute([':name' => 'Jim Morrison', ':age' => 27, ':gender' => 'Male']);
