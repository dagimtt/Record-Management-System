<?php
session_start();

// Check if user is logged in and is an chief officer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['position'] !== 'chief officer') {
    header("Location: login.php");
    exit();
}

// Check session timeout (optional but recommended)
$timeout_duration = 3600; // 1 hour
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

include("db.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $subject = $_POST['subject'];
    $date = $_POST['date_received_sent'];
    $ref_no = $_POST['ref_no'];
    $req_num = $_POST['request_number']; // New field
    $desc = $_POST['description'];
    $file = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];

    // Use the actual logged-in user's ID
    $created_by = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    
    if (!$created_by) {
        die("<div style='background:#f8d7da;padding:10px;margin:10px;border-radius:5px;'>❌ Error: User not properly logged in!</div>");
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        die("<div style='background:#f8d7da;padding:10px;margin:10px;border-radius:5px;'>❌ Error: Please upload a file!</div>");
    }
    
    $file = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filePath = $uploadDir . time() . "_" . basename($file);
    
    if (!move_uploaded_file($tmp, $filePath)) {
        die("<div style='background:#f8d7da;padding:10px;margin:10px;border-radius:5px;'>❌ Error: Failed to upload file!</div>");
    }

    try {
        $stmt = $conn->prepare("INSERT INTO letters 
            (type, ref_no, request_number, subject, sender, receiver, date_received_sent, description, file_path, created_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$type, $ref_no, $req_num, $subject, $sender, $receiver, $date, $desc, $filePath, $created_by]);
        
        // Set success message in session and redirect to dashboard
        $_SESSION['success_message'] = "✅ Letter saved successfully!";
        header("Location: dashboard.php");
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Record Department - Archive Upload</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  body {
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                url('img/background.png') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  
  .main-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
  }
  
  .upload-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    border: none;
    backdrop-filter: blur(10px);
  }
  
  .card-header {
    background: linear-gradient(135deg, #2c3e50, #4a6491);
    padding: 20px 30px;
    border-bottom: 3px solid #3498db;
  }
  
  .card-header h4 {
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.5px;
  }
  
  .card-body {
    padding: 30px;
  }
  
  .form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
  }
  
  .form-control, .form-select {
    border-radius: 8px;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s;
  }
  
  .form-control:focus, .form-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
  }
  
  .btn-back {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    transition: all 0.3s;
  }
  
  .btn-back:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateY(-2px);
  }
  
  .btn-submit {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border: none;
    border-radius: 8px;
    padding: 12px 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
  }
  
  .btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
  }
  
  .file-upload-container {
    border: 2px dashed #3498db;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: rgba(52, 152, 219, 0.05);
    transition: all 0.3s;
    cursor: pointer;
  }
  
  .file-upload-container:hover {
    background: rgba(52, 152, 219, 0.1);
  }
  
  .file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
  }
  
  .file-upload-icon {
    font-size: 40px;
    color: #3498db;
    margin-bottom: 10px;
  }
  
  .file-input {
    display: none;
  }
  
  .form-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eaeaea;
  }
  
  .form-section-title {
    font-size: 1.1rem;
    color: #3498db;
    margin-bottom: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
  }
  
  .form-section-title i {
    margin-right: 10px;
  }
  
  .required-field::after {
    content: " *";
    color: #e74c3c;
  }
  
  @media (max-width: 768px) {
    .card-body {
      padding: 20px;
    }
    
    .main-container {
      padding: 10px 0;
    }
  }
</style>
</head>
<body>

<div class="main-container">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card upload-card">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-archive me-2"></i>Record Department - Archive Upload</h4>
            <a href="dashboard.php" class="btn btn-back">
              <i class="fa fa-arrow-left me-1"></i> Back to Dashboard
            </a>
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <!-- Letter Type Section -->
              <div class="form-section">
                <div class="form-section-title">
                  <i class="fas fa-envelope"></i> Letter Information
                </div>
                <div class="mb-4">
                  <label class="form-label required-field">Letter Type</label>
                  <select name="type" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="incoming">Incoming (External → Internal)</option>
                    <option value="outgoing">Outgoing (Internal → External)</option>
                  </select>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label required-field">From</label>
                    <input type="text" name="sender" class="form-control" placeholder="Sender Name" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label required-field">To</label>
                    <input type="text" name="receiver" class="form-control" placeholder="Receiver Name" required>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label required-field">Subject</label>
                  <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                </div>
              </div>

              <!-- Document Details Section -->
              <div class="form-section">
                <div class="form-section-title">
                  <i class="fas fa-file-alt"></i> Document Details
                </div>
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label required-field">Date (Received/Sent)</label>
                    <input type="date" name="date_received_sent" class="form-control" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Reference No.</label>
                    <input type="text" name="ref_no" class="form-control" placeholder="Reference Number">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Req Num</label>
                    <input type="text" name="request_number" class="form-control" placeholder="Request Number">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Description / Remark</label>
                  <textarea name="description" class="form-control" rows="3" placeholder="Add any additional information about this document..."></textarea>
                </div>
              </div>

              <!-- File Upload Section -->
              <div class="form-section">
                <div class="form-section-title">
                  <i class="fas fa-file-upload"></i> File Attachment
                </div>
                <div class="mb-4">
                  <label class="form-label required-field">Attach Scanned PDF</label>
                  <div class="file-upload-container">
                    <label class="file-upload-label">
                      <div class="file-upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                      </div>
                      <div class="mb-2">Click to upload or drag and drop</div>
                      <div class="text-muted mb-2">PDF files only (Max. 10MB)</div>
                      <input type="file" name="file" class="file-input" accept="application/pdf" required>
                      <div class="btn btn-outline-primary mt-2">Select File</div>
                    </label>
                  </div>
                  <div class="mt-2" id="file-name">No file selected</div>
                </div>
              </div>

              <div class="d-flex justify-content-end mt-4">
                <button type="reset" class="btn btn-outline-secondary me-3">Reset Form</button>
                <button type="submit" class="btn btn-submit">
                  <i class="fas fa-save me-2"></i> Save to Archive
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Display selected file name
  document.querySelector('.file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
    document.getElementById('file-name').textContent = fileName;
  });
  
  // Make the entire file upload container clickable
  document.querySelector('.file-upload-container').addEventListener('click', function() {
    document.querySelector('.file-input').click();
  });
</script>

</body>
</html>