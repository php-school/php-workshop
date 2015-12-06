<?php

$db = new \PDO($argv[1]);

$stmt = $db->prepare('INSERT into users (name, age, gender) VALUES (:name, :age, :gender)');
$stmt->execute([':name' => 'Kurt Cobain', ':age' => 27, ':gender' => 'Male']);
