<?php
include("db.php");

// Check if ID is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div style='padding:20px;color:red;'>❌ Invalid request: No letter ID provided.</div>");
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department'])) {
    $department = $_POST['department'];
    $letter_id = intval($_POST['letter_id']);
    
    // Update the letter in database
    try {
        $update_stmt = $conn->prepare("UPDATE letters SET department = ?, status = 'new' WHERE id = ?");
        $update_stmt->execute([$department, $letter_id]);
        
        // Success message
        $success_message = "✅ Letter successfully sent to $department department!";
        
        // Refresh the letter data to show updated status
        $stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
        $stmt->execute([$id]);
        $letter = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error_message = "❌ Error updating letter: " . $e->getMessage();
    }
}

// Fetch letter data (either initial load or after update)
$stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
$stmt->execute([$id]);
$letter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$letter) {
    die("<div style='padding:20px;color:red;'>❌ Letter not found.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Letter Detail - <?= htmlspecialchars($letter['ref_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
  background-color: #f4f6fa;
  font-family: 'Segoe UI';
  min-height: 100vh;
}
.container {
  margin-top: 40px;
  max-width: 950px;
  padding-bottom: 80px;
}
.card {
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  border: none;
  overflow: hidden;
}
.card-header {
  background: linear-gradient(45deg, #123AAE, #007bff);
  color: white;
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  padding: 20px;
}
.label {
  font-weight: 600;
  color: #555;
}
.value {
  color: #222;
}
.badge-status {
  font-size: 0.9rem;
  padding: 6px 10px;
  border-radius: 8px;
}
.preview-box {
  background: #fff;
  padding: 15px;
  border-radius: 10px;
  border: 1px solid #ddd;
}

/* FIXED: Send Letter Button Styles */
.btn-send-letter {
  background: linear-gradient(45deg, #123AAE, #007bff);
  color: white !important;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(18, 58, 174, 0.3);
  padding: 12px 30px;
  font-size: 1.1rem;
  margin-top: 10px;
}

.btn-send-letter:hover {
  background: linear-gradient(45deg, #0f2f8a, #0056b3);
  color: white !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(18, 58, 174, 0.5);
}

.btn-send-letter:active {
  transform: translateY(0);
}

.form-section {
  background: #ffffff;
  border: 1px solid #e3e6f0;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.05);
  margin-top: 40px;
}

/* Make the department selection more prominent */
.department-select {
  border: 2px solid #123AAE !important;
  border-radius: 10px !important;
  padding: 12px 15px;
  font-size: 1rem;
  box-shadow: 0 2px 10px rgba(18, 58, 174, 0.1);
}

.department-select:focus {
  border-color: #007bff !important;
  box-shadow: 0 0 0 0.2rem rgba(18, 58, 174, 0.25) !important;
}

.form-label-custom {
  font-weight: 600;
  color: #495057;
  font-size: 1.1rem;
  margin-bottom: 15px;
}

/* Success alert styling */
.alert-success-custom {
  border-radius: 10px;
  border: none;
  background: linear-gradient(45deg, #d4edda, #c3e6cb);
  color: #155724;
  border-left: 4px solid #28a745;
}

/* Show current department if already sent */
.current-department {
  background: #e7f3ff;
  padding: 10px 15px;
  border-radius: 8px;
  border-left: 4px solid #007bff;
}
</style>
</head>
<body>

<div class="container">
  <!-- Success/Error Messages -->
  <?php if (isset($success_message)): ?>
    <div class="alert alert-success-custom alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle me-2"></i><?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa fa-exclamation-circle me-2"></i><?= $error_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="card mb-5">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="m-0"><i class="fa fa-envelope-open-text me-2"></i>Letter Details</h4>
      <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body p-4">
      
      <div class="row mb-3">
        <div class="col-md-6">
          <p class="label mb-1"><i class="fa fa-hashtag me-1"></i>Reference No</p>
          <p class="value"><?= htmlspecialchars($letter['ref_no']) ?></p>
        </div>
        <div class="col-md-6">
          <p class="label mb-1"><i class="fa fa-calendar me-1"></i>Date Received/Sent</p>
          <p class="value"><?= htmlspecialchars($letter['date_received_sent']) ?></p>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <p class="label mb-1"><i class="fa fa-user me-1"></i>From</p>
          <p class="value"><?= htmlspecialchars($letter['sender']) ?></p>
        </div>
        <div class="col-md-6">
          <p class="label mb-1"><i class="fa fa-paper-plane me-1"></i>To</p>
          <p class="value"><?= htmlspecialchars($letter['receiver']) ?></p>
        </div>
      </div>

      <div class="mb-3">
        <p class="label mb-1"><i class="fa fa-heading me-1"></i>Subject</p>
        <p class="value"><?= htmlspecialchars($letter['subject']) ?></p>
      </div>

      <div class="mb-3">
        <p class="label mb-1"><i class="fa fa-align-left me-1"></i>Description</p>
        <p class="value"><?= nl2br(htmlspecialchars($letter['description'])) ?></p>
      </div>

      <div class="row mb-4">
        <div class="col-md-4">
          <p class="label mb-1"><i class="fa fa-tag me-1"></i>Type</p>
          <span class="badge bg-primary"><?= ucfirst(htmlspecialchars($letter['type'])) ?></span>
        </div>
        <div class="col-md-4">
          <p class="label mb-1"><i class="fa fa-info-circle me-1"></i>Status</p>
          <?php
            $status = strtolower($letter['status']);
            $color = match($status) {
              'new' => 'success',
              'read' => 'secondary',
              'pending' => 'warning',
              'sent' => 'info',
              'archived' => 'dark',
              default => 'info'
            };
          ?>
          <span class="badge bg-<?= $color ?> badge-status"><?= ucfirst($status) ?></span>
        </div>
        <?php if (!empty($letter['department'])): ?>
        <div class="col-md-4">
          <p class="label mb-1"><i class="fa fa-building me-1"></i>Department</p>
          <div class="current-department">
            <strong><?= htmlspecialchars($letter['department']) ?></strong>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($letter['file_path']) && file_exists($letter['file_path'])): ?>
        <div class="preview-box mb-3">
          <h6><i class="fa fa-file-pdf text-danger me-2"></i>Attached Document:</h6>
          <embed src="<?= htmlspecialchars($letter['file_path']) ?>" type="application/pdf" width="100%" height="400px" />
          <div class="mt-3 text-center">
            <a href="<?= htmlspecialchars($letter['file_path']) ?>" download class="btn btn-outline-primary btn-custom">
              <i class="fa fa-download"></i> Download PDF
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">⚠️ No attached file found for this letter.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Department Selection - Only show if letter hasn't been sent yet -->
  <?php if ($letter['status'] == 'pending'): ?>
  <div class="form-section">
    <form method="post" action="">
      <div class="text-center mb-4">
        <h5 class="form-label-custom">
          <i class="fa fa-paper-plane me-2 text-primary"></i>Forward Letter to Department
        </h5>
        <p class="text-muted">Select a department to send this letter for further processing</p>
      </div>
      
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="mb-4">
            <label class="form-label fw-semibold text-secondary">
              <i class="fa fa-building me-2"></i>Select Department
            </label>
            <select name="department" class="form-select department-select" required>
              <option value="">-- Choose Department --</option>
              <?php
                $deptQuery = $conn->query("SELECT name FROM departments ORDER BY name ASC");
                while ($dept = $deptQuery->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($dept['name'] === $letter['department']) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($dept['name'])."' $selected>".htmlspecialchars($dept['name'])."</option>";
                }
              ?>
            </select>
          </div>
          
          <input type="hidden" name="letter_id" value="<?= htmlspecialchars($letter['id']) ?>">
          
          <div class="text-center">
            <button type="submit" class="btn btn-send-letter">
              <i class="fa fa-paper-plane me-2"></i>Send Letter to Department
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <?php else: ?>
    <!-- Show message if letter already sent -->
    <div class="alert alert-info text-center">
      <i class="fa fa-info-circle me-2"></i>
      This letter has already been sent to <strong><?= htmlspecialchars($letter['department']) ?></strong> department.
    </div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Optional: Add some interactive feedback
document.addEventListener('DOMContentLoaded', function() {
    const sendButton = document.querySelector('.btn-send-letter');
    const departmentSelect = document.querySelector('select[name="department"]');
    
    if (sendButton && departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            if (this.value) {
                sendButton.style.opacity = '1';
            } else {
                sendButton.style.opacity = '0.8';
            }
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
</body>
</html>