<?php
session_start(); // Add session start at the top

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include("db.php");

// Check if ID is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div style='padding:20px;color:red;'>❌ Invalid request: No letter ID provided.</div>");
}

$id = intval($_GET['id']);

// Get current user info from session
$current_user_department = $_SESSION['director_department'] ?? '';
$current_user_position = $_SESSION['director_position'] ?? '';

// Handle form submission for sending to department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department'])) {
    $department = $_POST['department'];
    $letter_id = intval($_POST['letter_id']);
    
    // Update the letter in database
    try {
        $update_stmt = $conn->prepare("UPDATE letters SET department = ?, status = 'sent' WHERE id = ?");
        $update_stmt->execute([$department, $letter_id]);
        
        // Success message
        $success_message = "✅ Letter successfully sent to $department department!";
         header("Location: director_panel.php");

        
        // Refresh the letter data to show updated status
        $stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
        $stmt->execute([$id]);
        $letter = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error_message = "❌ Error updating letter: " . $e->getMessage();
    }
}

// Handle form submission for editing letter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_letter'])) {
    $ref_no = $_POST['ref_no'];
    $request_number = $_POST['request_number'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];
    $subject = $_POST['subject'];
    $date_received_sent = $_POST['date_received_sent'];
    $description = $_POST['description'];
    $letter_id = intval($_POST['letter_id']);
    
    try {
        $update_stmt = $conn->prepare("UPDATE letters SET ref_no = ?, request_number = ?, sender = ?, receiver = ?, subject = ?, date_received_sent = ?, description = ? WHERE id = ?");
        $update_stmt->execute([$ref_no, $request_number, $sender, $receiver, $subject, $date_received_sent, $description, $letter_id]);
        
        // Success message
        $edit_success_message = "✅ Letter details updated successfully!";
        
        // Refresh the letter data
        $stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
        $stmt->execute([$id]);
        $letter = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $edit_error_message = "❌ Error updating letter: " . $e->getMessage();
    }
}

// Fetch letter data (either initial load or after update)
$stmt = $conn->prepare("SELECT * FROM letters WHERE id = ?");
$stmt->execute([$id]);
$letter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$letter) {
    die("<div style='padding:20px;color:red;'>❌ Letter not found.</div>");
}

// Check if we're in edit mode
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == 'true';

// Check if user can send to department (Bureau director)
$can_send_to_department = (!$edit_mode && 
                          $letter['status'] == 'new' && 
                          $current_user_department == 'Bureau' && 
                          $current_user_position == 'director');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Letter Detail - <?= htmlspecialchars($letter['ref_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
  --primary: #4361ee;
  --primary-dark: #3a56d4;
  --secondary: #7209b7;
  --success: #06d6a0;
  --info: #118ab2;
  --warning: #ffd166;
  --danger: #ef476f;
  --light: #f8f9fa;
  --dark: #212529;
  --gray: #6c757d;
  --light-gray: #e9ecef;
  --border-radius: 16px;
  --box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
  font-family: 'Poppins', sans-serif;
}

body {
  background: linear-gradient(135deg, #f5f7ff 0%, #f0f4ff 100%);
  min-height: 100vh;
  padding-bottom: 40px;
}

.container {
  margin-top: 30px;
  max-width: 1100px;
}

/* Card Styles */
.letter-card {
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  border: none;
  overflow: hidden;
  transition: var(--transition);
  margin-bottom: 30px;
  background: white;
}

.letter-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
}

.card-header-custom {
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  color: white;
  border-top-left-radius: var(--border-radius);
  border-top-right-radius: var(--border-radius);
  padding: 30px;
  position: relative;
  overflow: hidden;
}

.card-header-custom::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,192C672,181,768,139,864,138.7C960,139,1056,181,1152,197.3C1248,213,1344,203,1392,197.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
  background-size: cover;
  background-position: center;
  opacity: 0.2;
}

.card-body-custom {
  padding: 35px;
  background-color: white;
}

/* Typography and Labels */
.page-title {
  font-weight: 700;
  font-size: 2rem;
  margin-bottom: 5px;
  letter-spacing: -0.5px;
}

.page-subtitle {
  font-weight: 400;
  opacity: 0.9;
  font-size: 1rem;
}

.label {
  font-weight: 600;
  color: var(--gray);
  font-size: 0.9rem;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
}

.label i {
  margin-right: 10px;
  width: 18px;
  text-align: center;
  font-size: 1rem;
}

.value {
  color: var(--dark);
  font-weight: 500;
  font-size: 1.1rem;
  margin-bottom: 20px;
  padding-left: 28px;
}

/* Badge Styles */
.badge-status {
  font-size: 0.85rem;
  padding: 8px 15px;
  border-radius: 30px;
  font-weight: 500;
  letter-spacing: 0.3px;
}

.badge-type {
  background: linear-gradient(135deg, var(--info) 0%, #1e96c8 100%);
  color: white;
  font-size: 0.85rem;
  padding: 8px 15px;
  border-radius: 30px;
  font-weight: 500;
}

/* Button Styles */
.btn-custom-primary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  padding: 14px 30px;
  transition: var(--transition);
  box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.05rem;
}

.btn-custom-primary:hover {
  background: linear-gradient(135deg, var(--primary-dark) 0%, #2f4fd6 100%);
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
}

.btn-custom-success {
  background: linear-gradient(135deg, var(--success) 0%, #05c290 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  padding: 12px 25px;
  transition: var(--transition);
  box-shadow: 0 6px 20px rgba(6, 214, 160, 0.3);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-custom-success:hover {
  background: linear-gradient(135deg, #05c290 0%, #04b486 100%);
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(6, 214, 160, 0.4);
}

.btn-custom-warning {
  background: linear-gradient(135deg, var(--warning) 0%, #ffc745 100%);
  color: var(--dark);
  border: none;
  border-radius: 12px;
  font-weight: 600;
  padding: 12px 25px;
  transition: var(--transition);
  box-shadow: 0 6px 20px rgba(255, 209, 102, 0.3);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-custom-warning:hover {
  background: linear-gradient(135deg, #ffc745 0%, #ffbe2d 100%);
  color: var(--dark);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(255, 209, 102, 0.4);
}

.btn-custom-light {
  background: white;
  color: var(--gray);
  border: 2px solid var(--light-gray);
  border-radius: 12px;
  font-weight: 500;
  padding: 12px 25px;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.btn-custom-light:hover {
  background: var(--light);
  color: var(--dark);
  border-color: #ced4da;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.btn-custom-secondary {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  padding: 12px 25px;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
}

.btn-custom-secondary:hover {
  background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
}

.btn-sm-custom {
  padding: 8px 16px;
  font-size: 0.9rem;
  border-radius: 10px;
}

/* Form Styles */
.form-section {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 35px;
  margin-top: 30px;
  border-top: 5px solid var(--primary);
  position: relative;
  overflow: hidden;
}

.form-section::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

.department-select {
  border: 2px solid var(--light-gray);
  border-radius: 12px;
  padding: 14px 18px;
  font-size: 1rem;
  transition: var(--transition);
  background: white;
}

.department-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 0.3rem rgba(67, 97, 238, 0.2);
  transform: translateY(-2px);
}

.edit-form input, .edit-form textarea, .edit-form select, .edit-form .form-control {
  border: 2px solid var(--light-gray);
  border-radius: 12px;
  padding: 14px 18px;
  transition: var(--transition);
  font-size: 1rem;
  background: white;
}

.edit-form input:focus, .edit-form textarea:focus, .edit-form select:focus, .edit-form .form-control:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 0.3rem rgba(67, 97, 238, 0.2);
  transform: translateY(-2px);
}

.form-label-custom {
  font-weight: 700;
  color: var(--dark);
  font-size: 1.3rem;
  margin-bottom: 20px;
  text-align: center;
  display: block;
  position: relative;
}

.form-label-custom::after {
  content: "";
  display: block;
  width: 60px;
  height: 4px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  margin: 10px auto 0;
  border-radius: 2px;
}

/* Alert Styles */
.alert-custom-success {
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
  color: #155724;
  border-left: 5px solid var(--success);
  padding: 18px 25px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 25px;
}

.alert-custom-danger {
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
  color: #721c24;
  border-left: 5px solid var(--danger);
  padding: 18px 25px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 25px;
}

.alert-custom-info {
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
  color: #0c5460;
  border-left: 5px solid var(--info);
  padding: 18px 25px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 25px;
}

.alert-custom-warning {
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  color: #856404;
  border-left: 5px solid var(--warning);
  padding: 18px 25px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 25px;
}

/* Preview Box */
.preview-box {
  background: white;
  padding: 25px;
  border-radius: 12px;
  border: 2px solid var(--light-gray);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  margin-top: 25px;
  transition: var(--transition);
}

.preview-box:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

/* Status Indicators */
.status-indicator {
  display: inline-flex;
  align-items: center;
  padding: 10px 18px;
  border-radius: 30px;
  font-size: 0.9rem;
  font-weight: 500;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.status-new {
  background: rgba(6, 214, 160, 0.15);
  color: var(--success);
  border: 2px solid rgba(6, 214, 160, 0.3);
}

.status-read {
  background: rgba(108, 117, 125, 0.15);
  color: var(--gray);
  border: 2px solid rgba(108, 117, 125, 0.3);
}

.status-pending {
  background: rgba(255, 209, 102, 0.15);
  color: #e6a700;
  border: 2px solid rgba(255, 209, 102, 0.3);
}

.status-sent {
  background: rgba(17, 138, 178, 0.15);
  color: var(--info);
  border: 2px solid rgba(17, 138, 178, 0.3);
}

.status-archived {
  background: rgba(33, 37, 41, 0.15);
  color: var(--dark);
  border: 2px solid rgba(33, 37, 41, 0.3);
}

/* Action Buttons Container */
.action-buttons {
  background: white;
  padding: 25px;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  margin-top: 30px;
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  border-top: 5px solid var(--light-gray);
}

/* Back Button */
.btn-back {
  position: absolute;
  top: 25px;
  right: 25px;
  z-index: 10;
}

/* Letter Info Grid */
.letter-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 25px;
  margin-bottom: 25px;
}

.letter-info-item {
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  padding: 20px;
  border-radius: 12px;
  border-left: 5px solid var(--primary);
  transition: var(--transition);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.letter-info-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Department Info */
.department-info {
  background: linear-gradient(135deg, #e7f3ff 0%, #d4e7ff 100%);
  padding: 20px 25px;
  border-radius: 12px;
  border-left: 5px solid var(--info);
  margin-top: 25px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

/* User Badge */
.user-info-badge {
  background: linear-gradient(135deg, var(--success) 0%, #05c290 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 30px;
  font-size: 0.85rem;
  font-weight: 500;
  box-shadow: 0 4px 15px rgba(6, 214, 160, 0.3);
  display: inline-flex;
  align-items: center;
  margin-top: 10px;
}

.user-header-badge {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Permission Badge */
.permission-badge {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
  display: inline-flex;
  align-items: center;
  margin-left: 10px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .container {
    margin-top: 15px;
    padding: 0 15px;
  }
  
  .card-body-custom {
    padding: 25px;
  }
  
  .card-header-custom {
    padding: 25px;
  }
  
  .page-title {
    font-size: 1.6rem;
  }
  
  .action-buttons {
    flex-direction: column;
    align-items: center;
    padding: 20px;
  }
  
  .btn-back {
    position: relative;
    top: 0;
    right: 0;
    margin-top: 15px;
  }
  
  .letter-info-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .form-section {
    padding: 25px;
  }
}
</style>
</head>
<body>

<div class="container">
  <!-- Success/Error Messages -->
  <?php if (isset($success_message)): ?>
    <div class="alert alert-custom-success alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle me-2"></i><?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($error_message)): ?>
    <div class="alert alert-custom-danger alert-dismissible fade show" role="alert">
      <i class="fa fa-exclamation-circle me-2"></i><?= $error_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($edit_success_message)): ?>
    <div class="alert alert-custom-success alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle me-2"></i><?= $edit_success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($edit_error_message)): ?>
    <div class="alert alert-custom-danger alert-dismissible fade show" role="alert">
      <i class="fa fa-exclamation-circle me-2"></i><?= $edit_error_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Main Letter Card -->
  <div class="letter-card">
    <div class="card-header-custom">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <h1 class="page-title"><i class="fa fa-envelope-open-text me-2"></i>Letter Details</h1>
          <p class="page-subtitle mb-2">Reference: <?= htmlspecialchars($letter['ref_no']) ?></p>
          <span class="user-header-badge">
            <i class="fa fa-user-shield me-1"></i>
            <?= htmlspecialchars($current_user_department) ?> - <?= htmlspecialchars($current_user_position) ?>
          </span>
        </div>
        <div class="btn-back">
          <a href="javascript:history.back()" class="btn btn-custom-light">
            <i class="fa fa-arrow-left me-1"></i> Back
          </a>
        </div>
      </div>
    </div>
    <div class="card-body-custom">
      
      <?php if ($edit_mode): ?>
        <!-- EDIT FORM -->
        <form method="post" action="" class="edit-form">
          <input type="hidden" name="edit_letter" value="1">
          <input type="hidden" name="letter_id" value="<?= htmlspecialchars($letter['id']) ?>">
          
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label class="form-label label"><i class="fa fa-hashtag"></i> Reference No</label>
              <input type="text" name="ref_no" class="form-control" value="<?= htmlspecialchars($letter['ref_no']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label label"><i class="fa fa-tag"></i> Request Number</label>
              <input type="text" name="request_number" class="form-control" value="<?= htmlspecialchars($letter['request_number']) ?>">
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label class="form-label label"><i class="fa fa-user"></i> From</label>
              <input type="text" name="sender" class="form-control" value="<?= htmlspecialchars($letter['sender']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label label"><i class="fa fa-paper-plane"></i> To</label>
              <input type="text" name="receiver" class="form-control" value="<?= htmlspecialchars($letter['receiver']) ?>" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label label"><i class="fa fa-heading"></i> Subject</label>
            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($letter['subject']) ?>" required>
          </div>

          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label class="form-label label"><i class="fa fa-calendar"></i> Date Received/Sent</label>
              <input type="date" name="date_received_sent" class="form-control" value="<?= htmlspecialchars($letter['date_received_sent']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label label"><i class="fa fa-tag"></i> Type</label>
              <input type="text" class="form-control" value="<?= ucfirst(htmlspecialchars($letter['type'])) ?>" disabled>
              <small class="text-muted">Type cannot be changed</small>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label label"><i class="fa fa-align-left"></i> Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($letter['description']) ?></textarea>
          </div>

          <div class="action-buttons">
            <button type="submit" class="btn btn-custom-success">
              <i class="fa fa-save me-2"></i> Save Changes
            </button>
            <a href="?id=<?= $id ?>" class="btn btn-custom-secondary">
              <i class="fa fa-times me-2"></i> Cancel
            </a>
          </div>
        </form>
      <?php else: ?>
        <!-- VIEW MODE -->
        <div class="letter-info-grid">
          <div class="letter-info-item">
            <p class="label"><i class="fa fa-hashtag"></i> Reference No</p>
            <p class="value"><?= htmlspecialchars($letter['ref_no']) ?></p>
          </div>
          
          <div class="letter-info-item">
            <p class="label"><i class="fa fa-tag"></i> Request Number</p>
            <p class="value"><?= !empty($letter['request_number']) ? htmlspecialchars($letter['request_number']) : '-' ?></p>
          </div>
          
          <div class="letter-info-item">
            <p class="label"><i class="fa fa-user"></i> From</p>
            <p class="value"><?= htmlspecialchars($letter['sender']) ?></p>
          </div>
          
          <div class="letter-info-item">
            <p class="label"><i class="fa fa-paper-plane"></i> To</p>
            <p class="value"><?= htmlspecialchars($letter['receiver']) ?></p>
          </div>
          
          <div class="letter-info-item">
            <p class="label"><i class="fa fa-heading"></i> Subject</p>
            <p class="value"><?= htmlspecialchars($letter['subject']) ?></p>
          </div>
          
          <div class="letter-info-item">
            <p class="label"><i class="fa fa-calendar"></i> Date Received/Sent</p>
            <p class="value"><?= htmlspecialchars($letter['date_received_sent']) ?></p>
          </div>
        </div>

        <div class="mb-4">
          <p class="label"><i class="fa fa-align-left"></i> Description</p>
          <p class="value"><?= nl2br(htmlspecialchars($letter['description'])) ?></p>
        </div>

        <div class="d-flex flex-wrap gap-4 mb-4">
          <div>
            <p class="label mb-2"><i class="fa fa-tag"></i> Type</p>
            <span class="badge-type"><?= ucfirst(htmlspecialchars($letter['type'])) ?></span>
          </div>
          <div>
            <p class="label mb-2"><i class="fa fa-info-circle"></i> Status</p>
            <?php
              $status = strtolower($letter['status']);
              $color = match($status) {
                'new' => 'status-new',
                'read' => 'status-read',
                'pending' => 'status-pending',
                'sent' => 'status-sent',
                'archived' => 'status-archived',
                default => 'status-read'
              };
            ?>
            <span class="status-indicator <?= $color ?>">
              <i class="fa fa-circle me-1" style="font-size: 8px;"></i> <?= ucfirst($status) ?>
            </span>
          </div>
        </div>

        <?php if (!empty($letter['file_path']) && file_exists($letter['file_path'])): ?>
          <div class="preview-box">
            <h5 class="mb-3"><i class="fa fa-file-pdf text-danger me-2"></i>Attached Document</h5>
            <embed src="<?= htmlspecialchars($letter['file_path']) ?>" type="application/pdf" width="100%" height="500px" />
            <div class="mt-4 text-center">
              <a href="<?= htmlspecialchars($letter['file_path']) ?>" download class="btn btn-custom-primary">
                <i class="fa fa-download me-2"></i> Download PDF
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-custom-warning">
            <i class="fa fa-exclamation-triangle me-2"></i> No attached file found for this letter.
          </div>
        <?php endif; ?>
        
        <!-- Edit Button (only show if not in edit mode and status allows editing) -->
        <?php if (!$edit_mode && $letter['status'] == 'new'): ?>
          <div class="text-center mt-4">
            <a href="?id=<?= $id ?>&edit=true" class="btn btn-custom-warning">
              <i class="fa fa-edit me-2"></i> Edit Letter Details
            </a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Department Selection - Only show if:
       - NOT in edit mode 
       - Letter status is 'new'
       - Current user department is 'Bureau' 
       - Current user position is 'director' -->
  <?php if ($can_send_to_department): ?>
  <div class="form-section">
    <form method="post" action="">
      <div class="text-center mb-4">
        <h5 class="form-label-custom">
          <i class="fa fa-paper-plane me-2 text-primary"></i>Forward Letter to Department
        </h5>
        <p class="text-muted mb-3">Select a department to send this letter for further processing</p>
        <div class="user-info-badge">
          <i class="fa fa-user-shield me-2"></i>
          Bureau Director Access
        </div>
      </div>
      
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="mb-4">
            <label class="form-label label">
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
            <button type="submit" class="btn btn-custom-primary btn-lg">
              <i class="fa fa-paper-plane me-2"></i>Send Letter to Department
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <?php elseif (!$edit_mode && !empty($letter['department'])): ?>
    <!-- Show message if letter already sent -->
    <div class="alert alert-custom-info text-center">
      <i class="fa fa-info-circle me-2"></i>
      This letter has already been sent to <strong><?= htmlspecialchars($letter['department']) ?></strong> department.
    </div>
  <?php elseif (!$edit_mode && $letter['status'] == 'new' && !($current_user_department == 'Bureau' && $current_user_position == 'director')): ?>
    <!-- Show message if user doesn't have permission to send -->
    <div class="alert alert-custom-warning text-center">
      <i class="fa fa-exclamation-triangle me-2"></i>
      You don't have permission to send this letter to departments. 
      Only <strong>Bureau Directors</strong> can forward letters.
    </div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Enhanced interactive feedback
document.addEventListener('DOMContentLoaded', function() {
    const sendButton = document.querySelector('.btn-custom-primary');
    const departmentSelect = document.querySelector('select[name="department"]');
    
    if (sendButton && departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            if (this.value) {
                sendButton.style.opacity = '1';
                sendButton.style.transform = 'scale(1.02)';
            } else {
                sendButton.style.opacity = '0.9';
                sendButton.style.transform = 'scale(1)';
            }
        });
    }
    
    // Add animation to cards on load
    const cards = document.querySelectorAll('.letter-card, .form-section');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
    });
    
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