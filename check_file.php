<?php
$path = __DIR__ . '/storage/app/public/uploads/sJi9C8gup68dSbMWUjUNQURZCwFEIokNmlSI8Ybq.jpg';
var_dump($path);
var_dump(file_exists($path));
var_dump(is_readable($path));
var_dump(filesize($path));
