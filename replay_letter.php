<?php
include("db.php");

$letter = [
    'type' => '',
    'sender' => '',
    'receiver' => '',
    'subject' => '',
    'date_received_sent' => '',
    'ref_no' => '',
    'description' => '',
    'file_path' => ''
];

// ✅ If Reply button clicked, load data from that row
if (isset($_GET['ref_no'])) {
    $ref_no = $_GET['ref_no'];
    $stmt = $conn->prepare("SELECT * FROM letters WHERE ref_no = ?");
    $stmt->execute([$ref_no]);
    $letter = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ✅ Handle form submission (update existing record)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $subject = $_POST['subject'];
    $date = $_POST['date_received_sent'];
    $ref_no = $_POST['ref_no'];
    $desc = $_POST['description'];

    $file_path = $letter['file_path']; // Keep old file if new not uploaded
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['file']['name']);
        $target = $uploadDir . $fileName;
        move_uploaded_file($_FILES['file']['tmp_name'], $target);
        $file_path = $target;
    }

    $stmt = $conn->prepare("UPDATE letters 
                            SET type=?, sender=?, receiver=?, subject=?, date_received_sent=?, 
                                description=?, file_path=? 
                            WHERE ref_no=?");
    $stmt->execute([$type, $sender, $receiver, $subject, $date, $desc, $file_path, $ref_no]);

    echo "<div style='background:#d4edda;padding:10px;margin:10px;border-radius:5px;'>✅ Letter updated successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reply / Update Letter</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4>📁 Reply / Update Letter</h4>
            <a href="dashboard.php" class="btn btn-light btn-sm">⬅ Back</a>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Letter Type</label>
                    <select name="type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="outgoing" <?= ($letter['type'] == 'incoming') ? 'selected' : '' ?>>Outgoing</option>
                        <option value="incoming" <?= ($letter['type'] == 'outgoing') ? 'selected' : '' ?>>Incoming</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From</label>
                        <input type="text" name="sender" class="form-control" value="<?= htmlspecialchars($letter['sender']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">To</label>
                        <input type="text" name="receiver" class="form-control" value="<?= htmlspecialchars($letter['receiver']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($letter['subject']) ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date (Received/Sent)</label>
                        <input type="date" name="date_received_sent" class="form-control" 
                               value="<?= htmlspecialchars($letter['date_received_sent']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reference No.</label>
                        <input type="text" name="ref_no" class="form-control" 
                               value="<?= htmlspecialchars($letter['ref_no']) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description / Remark</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($letter['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Attach Scanned PDF</label>
                    <input type="file" name="file" class="form-control" accept="application/pdf">
                    <?php if (!empty($letter['file_path'])): ?>
                        <small class="text-muted">Current file: 
                            <a href="<?= htmlspecialchars($letter['file_path']) ?>" target="_blank">View</a>
                        </small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-success">💾 Update Letter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
