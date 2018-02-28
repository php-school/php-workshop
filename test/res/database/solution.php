<?php

$count = 0;
for ($i = 2; $i < count($argv); $i++) {
    $count += $argv[$i];
}

echo $count;
