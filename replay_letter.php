<?php
session_start(); // Add session start
include("db.php");


// Check if user is logged in and is a director
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['director_position'] !== 'director') {
    header("Location: login.php");
    exit();
}

// Get director's department from session (assuming it's stored during login)
$director_department = $_SESSION['director_department'] ?? '';

$letter = [
    'type' => '',
    'sender' => '',
    'receiver' => '',
    'subject' => '',
    'date_received_sent' => '',
    'ref_no' => '',
    'request_number' => '',
    'description' => '',
    'file_path' => ''
];

// ✅ If Reply button clicked, load data from that row
if (isset($_GET['request_number'])) {
    $request_number = $_GET['request_number'];
    $stmt = $conn->prepare("SELECT * FROM letters WHERE request_number = ?");
    $stmt->execute([$request_number]);
    $letter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$letter) {
        die("<div style='padding:20px;color:red;'>❌ Letter not found for request number: " . htmlspecialchars($request_number) . "</div>");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $subject = $_POST['subject'];
    $date = $_POST['date_received_sent'];
    $ref_no = $_POST['ref_no'];
    $request_number = $_POST['request_number'];
    $desc = $_POST['description'];
    
    // Use the actual logged-in user's ID
    $created_by = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    

        
        // Update all incoming letters with this request_number to 'seen'
        $stmt = $conn->prepare("UPDATE letters SET status = 'seen' WHERE request_number = ? AND type = 'incoming'");
        $stmt->execute([$request_number]);
    

    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['file']['name']);
        $target = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $file_path = $target;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO letters 
                (type, ref_no, request_number, subject, sender, receiver, date_received_sent, description, department, file_path, created_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, 'reply')");
        $stmt->execute([$type, $ref_no, $request_number, $subject, $sender, $receiver, $date, $desc,$director_department, $file_path, $created_by]);

        // Set success message and redirect to dashboard
        $_SESSION['success_message'] = "✅ Reply letter created successfully!";
        header("Location: director_panel.php");
        exit();
        
    } catch (PDOException $e) {
        echo "<div style='background:#f8d7da;padding:10px;margin:10px;border-radius:5px;'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reply / Update Letter</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  background-color: #f4f6fa;
  font-family: 'Segoe UI';
}
.card {
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  border: none;
}
.card-header {
  background: linear-gradient(45deg, #123AAE, #007bff);
  color: white;
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  padding: 20px;
}
.form-control:read-only {
  background-color: #f8f9fa;
  border-color: #e9ecef;
  color: #6c757d;
}
.btn-submit {
  background: linear-gradient(45deg, #28a745, #20c997);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  padding: 12px 30px;
  font-size: 1.1rem;
  transition: all 0.3s ease;
}
.btn-submit:hover {
  background: linear-gradient(45deg, #218838, #1e9e8a);
  transform: translateY(-2px);
}
.current-file-badge {
  background: #e7f3ff;
  padding: 8px 12px;
  border-radius: 8px;
  border-left: 4px solid #007bff;
  margin-top: 5px;
}
</style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4><i class="fa fa-reply me-2"></i>Reply to Letter</h4>
            <a href="director_panel.php" class="btn btn-light btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['request_number'])): ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i>
                    Replying to letter with Request Number: <strong><?= htmlspecialchars($letter['request_number']) ?></strong>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Hidden field to preserve request_number -->
                <input type="hidden" name="request_number" value="<?= htmlspecialchars($letter['request_number']) ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Letter Type</label>
                        <input type="text" name="type" class="form-control" value="outgoing" readonly>
                        <small class="text-muted">Reply letters are always outgoing</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Request Number</label>
                        <input type="text" name="request_number_display" class="form-control" 
                               value="<?= htmlspecialchars($letter['request_number']) ?>" readonly>
                        <small class="text-muted">Same as original letter</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From</label>
                        <input type="text" name="sender" class="form-control" 
                               value="<?= htmlspecialchars($letter['receiver'] ?? '') ?>" required 
                               placeholder="Typically the original receiver becomes the sender">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">To</label>
                        <input type="text" name="receiver" class="form-control" 
                               value="<?= htmlspecialchars($letter['sender'] ?? '') ?>" required
                               placeholder="Typically the original sender becomes the receiver">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" 
                           value="Re: <?= htmlspecialchars($letter['subject'] ?? '') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date (Received/Sent)</label>
                        <input type="date" name="date_received_sent" class="form-control" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reference No.</label>
                        <input type="text" name="ref_no" class="form-control" 
                               value="<?= htmlspecialchars($letter['ref_no'] ?? '') ?>" 
                               placeholder="Enter new reference number for the reply">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description / Remark</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter your reply message..."><?= htmlspecialchars($letter['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Attach Scanned PDF</label>
                    <input type="file" name="file" class="form-control" accept="application/pdf">
                    
                    <?php if (!empty($letter['file_path'])): ?>
                        <div class="current-file-badge mt-2">
                            <strong>Original Letter File:</strong> 
                            <a href="<?= htmlspecialchars($letter['file_path']) ?>" target="_blank" class="ms-2">
                                <i class="fa fa-eye text-primary"></i> View Original PDF
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-submit">
                        <i class="fa fa-paper-plane me-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// File validation
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[name="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file type
                if (file.type !== 'application/pdf') {
                    alert('Please select a PDF file only.');
                    this.value = '';
                    return;
                }
                
        
            }
        });
    }
    
    // Auto-fill current date if empty
    const dateField = document.querySelector('input[name="date_received_sent"]');
    if (dateField && !dateField.value) {
        dateField.value = '<?= date('Y-m-d') ?>';
    }
});
</script>
</body>
</html>