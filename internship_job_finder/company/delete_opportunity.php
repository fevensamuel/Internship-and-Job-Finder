<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$id = $_GET['id'];
$company_id = $_SESSION['user_id'];

$sql = "DELETE FROM opportunities WHERE id = $id AND company_id = $company_id";
$conn->query($sql);
header("Location: my_opportunities.php");
exit();
?>
