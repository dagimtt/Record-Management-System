<?php
include("db.php");

// Check if ID is passed (for displaying the letter)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div style='padding:20px;color:red;'>❌ Invalid request: No letter ID provided.</div>");
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department'])) {
    $department = $_POST['department'];
    
    // Update the letter
    $stmt = $conn->prepare("UPDATE letters SET department = ?, status = 'new' WHERE id = ?");
    $stmt->execute([$department, $id]);
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id . "&message=Letter sent successfully");
    exit;
}

// Fetch the letter
$stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
$stmt->execute([$id]);
$letter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$letter) {
    die("<div style='padding:20px;color:red;'>❌ Letter not found.</div>");
}