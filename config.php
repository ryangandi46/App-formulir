<?php
$pdo = new PDO("mysql:host=localhost;dbname=form_app", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
session_start();
