<?php
require_once 'config.php';
requireLogin();

$lang = $_GET['lang'] ?? 'it';

if ($_SESSION['user_type'] === 'istituto') {
    header('Location: dashboard_istituto.php');
    exit;
} elseif ($_SESSION['user_type'] === 'partner') {
    header('Location: dashboard_partner.php');
    exit;
} elseif ($_SESSION['user_type'] === 'admin') {
    header('Location: dashboard_admin.php');
    exit;
} else {
    header('Location: dashboard_utente.php');
    exit;
}
?>
