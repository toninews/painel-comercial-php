<?php
$query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
    ? '?' . $_SERVER['QUERY_STRING']
    : '';

$target = 'index-login.php' . $query;

if (!headers_sent())
{
    header('Location: ' . $target, true, 302);
    exit;
}

echo "<script>window.location='" . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . "';</script>";
