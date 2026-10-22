<?php

$db = new PDO($argv[1]);
echo $db->query('SELECT name FROM users')->fetchColumn();
