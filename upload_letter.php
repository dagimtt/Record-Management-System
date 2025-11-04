<?php
include("db.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $subject = $_POST['subject'];
    $date = $_POST['date_received_sent'];
    $ref_no = $_POST['ref_no'];
    $desc = $_POST['description'];
    $file = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filePath = $uploadDir . time() . "_" . basename($file);
    move_uploaded_file($tmp, $filePath);

    $stmt = $conn->prepare("INSERT INTO letters 
        (type, ref_no, subject, sender, receiver, date_received_sent, description, file_path, created_by, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$type, $ref_no, $subject, $sender, $receiver, $date, $desc, $filePath, 1]); // '1' is current user (Record dept)

    echo "<div style='background:#d4edda;padding:10px;margin:10px;border-radius:5px;'>✅ Letter saved successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Record Department - Archive Upload</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4>📁 Record Department - Archive Upload</h4>
            <!-- Back Button -->
            <a href="javascript:history.back()" class="btn btn-light btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Letter Type</label>
                    <select name="type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="incoming">Incoming (External → Internal)</option>
                        <option value="outgoing">Outgoing (Internal → External)</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From</label>
                        <input type="text" name="sender" class="form-control" placeholder="Sender Name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">To</label>
                        <input type="text" name="receiver" class="form-control" placeholder="Receiver Name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date (Received/Sent)</label>
                        <input type="date" name="date_received_sent" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reference No.</label>
                        <input type="text" name="ref_no" class="form-control" placeholder="Reference Number">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description / Remark</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Attach Scanned PDF</label>
                    <input type="file" name="file" class="form-control" accept="application/pdf" required>
                </div>

                <button type="submit" class="btn btn-success">💾 Save to Archive</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
