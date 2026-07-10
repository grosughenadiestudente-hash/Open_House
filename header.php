<?php
if (!isset($lang)) {
    $lang = $_GET['lang'] ?? 'it';
}
if (!isset($active_page)) {
    $active_page = '';
}
include __DIR__ . '/navbar.php';
