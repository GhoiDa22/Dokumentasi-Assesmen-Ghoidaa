<?php
session_start();
require_once 'db_connect.php';
require_once '../includes/functions.php';

if (isset($_GET['logout'])) {
    session_destroy();
    redirect('../');
}
?>