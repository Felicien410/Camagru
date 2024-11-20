<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/controllers/AuthController.php';

$controller = new AuthController();
$controller->verify();