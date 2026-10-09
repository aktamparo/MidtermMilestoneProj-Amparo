<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
Auth::logout();
header('Location: login.php'); exit;
