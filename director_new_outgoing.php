<?php
session_start();
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
include("db.php");

// Check if user is logged in and is a director
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['director_position'] !== 'director') {
    header("Location: login.php");
    exit();
}

$text = [
    'en' => [
        'title' => '📤 New Outgoing Letter',
        'back' => 'Back to Letters',
        'save' => 'Save Letter',
        'cancel' => 'Cancel',
        'ref_no' => 'Reference Number',
        'sender' => 'Sender',
        'receiver' => 'Receiver',
        'subject' => 'Subject',
        'date' => 'Date Sent',
        'description' => 'Description',
        'type' => 'Type',
        'file' => 'Upload File',
        'choose_file' => 'Choose file',
        'generate_letter' => 'Generate Letter',
        'required' => 'Required fields',
        'success' => 'Letter saved successfully!',
        'error' => 'Error saving letter!',
        'login_required' => 'Please log in first'
    ],
    'am' => [
        'title' => '📤 አዲስ የሚላክ ደብዳቤ',
        'back' => 'ወደ ደብዳቤዎች ተመለስ',
        'save' => 'ደብዳቤ አስቀምጥ',
        'cancel' => 'ተወ',
        'ref_no' => 'ማጣቀሻ ቁጥር',
        'sender' => 'የላከ',
        'receiver' => 'የተላከለት',
        'subject' => 'ርዕስ',
        'date' => 'የተላከበት ቀን',
        'description' => 'መግለጫ',
        'type' => 'አይነት',
        'file' => 'ፋይል ስቀል',
        'choose_file' => 'ፋይል ምረጥ',
        'generate_letter' => 'ደብዳቤ ፍጠር',
        'required' => 'የሚጠይቁ መስኮች',
        'success' => 'ደብዳቤ በተሳካ ሁኔታ ተሸጧል!',
        'error' => 'ደብዳቤ ሲሸጥ ስህተት ተከስቷል!',
        'login_required' => 'እባክዎ መጀመሪያ ይግቡ'
    ]
][$lang];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref_no = $_POST['ref_no'] ?? '';
    $sender = $_POST['sender'] ?? '';
    $receiver = $_POST['receiver'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_received_sent = $_POST['date_received_sent'] ?? date('Y-m-d');
    $type = 'outgoing';
    
    // Use the correct session variable
    $created_by = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;
    
    // File upload handling - support both manual upload and generated letters
    $file_path = '';
    
    // Check if this is a generated letter upload
    if (isset($_POST['generated_letter']) && isset($_FILES['file_upload'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_generated_letter.pdf';
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
            $file_path = $target_path;
        }
    }
    // Regular file upload
    elseif (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['file_upload']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
            $file_path = $target_path;
        }
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO letters 
                (type, ref_no, subject, sender, receiver, date_received_sent, description, file_path, created_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        if ($stmt->execute([$type, $ref_no, $subject, $sender, $receiver, $date_received_sent, $description, $file_path, $created_by])) {
            $_SESSION['message'] = ['type' => 'success', 'text' => $text['success']];
            header("Location: outgoing.php?lang=$lang");
            exit();
        } else {
            throw new Exception("Execute failed");
        }
        
    } catch (PDOException $e) {
        $error_message = $text['error'] . ": " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        $error_message = $text['error'] . ": " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($text['title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
  background-color: #f4f6fa;
  font-family: 'Segoe UI';
  margin: 0;
  overflow-x: hidden;
}
.wrapper { display: flex; min-height: 100vh; }

.sidebar {
  width: 250px;
  background: #123AAE;
  color: white;
  position: fixed;
  height: 100%;
  transition: all 0.3s ease;
}
.sidebar.collapsed { width: 70px; }
.sidebar .logo {
  padding: 15px;
  text-align: center;
  font-weight: bold;
  font-size: 18px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.toggle-btn {
  border: none;
  background: transparent;
  color: white;
  font-size: 20px;
}
.sidebar ul { list-style: none; padding: 0; margin: 0; }
.sidebar ul li a {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: white;
  text-decoration: none;
  transition: all 0.2s;
}
.sidebar ul li a:hover, .sidebar ul li a.active {
  background: rgba(255,255,255,0.15);
}
.sidebar ul li a i { width: 25px; font-size: 18px; }
.sidebar.collapsed a span,
.sidebar.collapsed .logo-text { display: none; }

.content {
  margin-left: 250px;
  width: 100%;
  transition: all 0.3s ease;
  padding: 20px;
}
.collapsed + .content { margin-left: 70px; }

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  border-radius: 10px;
  padding: 10px 20px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.form-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  padding: 30px;
}

.btn-primary {
  background: #123AAE;
  border: none;
  padding: 10px 25px;
}

.btn-primary:hover {
  background: #0f2f8a;
}

.btn-outline-secondary {
  border: 1px solid #123AAE;
  color: #123AAE;
}

.btn-outline-secondary:hover {
  background: #123AAE;
  color: white;
}

.required::after {
  content: " *";
  color: red;
}

.type-display {
  background-color: #e9ecef;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.375rem 0.75rem;
  font-weight: 500;
  color: #495057;
}

.debug-info {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 5px;
  padding: 10px;
  margin-bottom: 20px;
  font-family: monospace;
  font-size: 12px;
}

.outgoing-badge {
  background-color: #28a745;
  color: white;
}

.letter-generator-link {
  border: 2px dashed #123AAE;
  border-radius: 8px;
  padding: 15px;
  background: #f8f9ff;
  margin-top: 10px;
}

.letter-generator-link:hover {
  background: #e8f4ff;
  text-decoration: none;
}

.upload-option {
  border-left: 4px solid #28a745;
  padding-left: 15px;
  margin-bottom: 15px;
}

.generated-file-info {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 5px;
  padding: 10px;
  margin-top: 10px;
  display: none;
}
</style>
</head>
<body>

<div class="wrapper">
  <?php include("directorSidbar.php"); ?>

  <div class="content">
    <div class="topbar">
      <h4 class="m-0"><?= htmlspecialchars($text['title']) ?></h4>
      <div class="d-flex align-items-center gap-2">
        <a href="?lang=en" class="btn btn-sm btn-outline-primary <?= $lang == 'en' ? 'active' : '' ?>">English</a>
        <a href="?lang=am" class="btn btn-sm btn-outline-primary <?= $lang == 'am' ? 'active' : '' ?>">አማርኛ</a>
      </div>
    </div>

    <!-- Debug Information -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="debug-info">
      <strong>Debug Info:</strong><br>
      Session user_id: <?= $_SESSION['user_id'] ?? 'Not set' ?><br>
      Session id: <?= $_SESSION['id'] ?? 'Not set' ?><br>
      Created_by value: <?= $created_by ?? 'Not set' ?>
    </div>
    <?php endif; ?>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message']['type'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST" enctype="multipart/form-data" class="row g-3" id="letterForm">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
        <input type="hidden" name="type" value="outgoing">
        
        <div class="col-md-6">
          <label for="ref_no" class="form-label required"><?= htmlspecialchars($text['ref_no']) ?></label>
          <input type="text" class="form-control" id="ref_no" name="ref_no" required>
        </div>

        <div class="col-md-6">
          <label class="form-label"><?= htmlspecialchars($text['type']) ?></label>
          <div class="type-display outgoing-badge">
            <i class="fa fa-paper-plane me-2"></i>Outgoing Letter
          </div>
          <small class="form-text text-muted">This form is for outgoing letters only</small>
        </div>

        <div class="col-md-6">
          <label for="sender" class="form-label required"><?= htmlspecialchars($text['sender']) ?></label>
          <input type="text" class="form-control" id="sender" name="sender" placeholder="Organization or person sending the letter" required>
        </div>

        <div class="col-md-6">
          <label for="receiver" class="form-label required"><?= htmlspecialchars($text['receiver']) ?></label>
          <input type="text" class="form-control" id="receiver" name="receiver" placeholder="Organization or person receiving the letter" required>
        </div>

        <div class="col-12">
          <label for="subject" class="form-label required"><?= htmlspecialchars($text['subject']) ?></label>
          <input type="text" class="form-control" id="subject" name="subject" required>
        </div>

        <div class="col-12">
          <label for="description" class="form-label"><?= htmlspecialchars($text['description']) ?></label>
          <textarea class="form-control" id="description" name="description" rows="4" placeholder="Additional details about the letter"></textarea>
        </div>

        <div class="col-md-6">
          <label for="date_received_sent" class="form-label required"><?= htmlspecialchars($text['date']) ?></label>
          <input type="date" class="form-control" id="date_received_sent" name="date_received_sent" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="col-md-6">
          <label for="file_upload" class="form-label required"><?= htmlspecialchars($text['file']) ?></label>
          <input type="file" class="form-control" id="file_upload" name="file_upload" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required
                 onchange="checkFileType(this)">
          <div class="form-text">
            <?= htmlspecialchars($text['choose_file']) ?> (PDF, Word, Images) - Required
          </div>
          
          <!-- Letter Generator Link -->
          <div class="letter-generator-link mt-3">
            <div class="upload-option">
              <h6 class="mb-2">
                <i class="fa fa-magic text-primary me-2"></i>
                <span class="lang-en">Create Professional Letter</span>
                <span class="lang-am">ፕሮፌሽናል ደብዳቤ ፍጠር</span>
              </h6>
              <p class="small mb-2 lang-en">
                Use our letter generator to create a professional formatted letter and upload it directly.
              </p>
              <p class="small mb-2 lang-am">
                የኛን ደብዳቤ ጀነሬተር በመጠቀም ፕሮፌሽናል የተቀየጠ ደብዳቤ ፍጠር እና በቀጥታ ስቀል።
              </p>
              <a href="letter_template2.php" target="_blank" class="btn btn-info btn-sm">
                <i class="fa fa-external-link-alt me-1"></i>
                <?= htmlspecialchars($text['generate_letter']) ?>
              </a>
            </div>
          </div>

          <!-- Generated File Info -->
          <div class="generated-file-info" id="generatedFileInfo">
            <i class="fa fa-info-circle me-2"></i>
            <span class="lang-en">Generated letter ready for upload</span>
            <span class="lang-am">የተፈጠረ ደብዳቤ ለማስገባት ዝግጁ ነው</span>
          </div>
        </div>

        <div class="col-12">
          <small class="text-muted"><?= htmlspecialchars($text['required']) ?></small>
        </div>

        <div class="col-12 mt-4">
          <button type="submit" class="btn btn-primary" id="saveButton">
            <i class="fa fa-save"></i> <?= htmlspecialchars($text['save']) ?>
          </button>
          <a href="outgoing.php?lang=<?= htmlspecialchars($lang) ?>" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> <?= htmlspecialchars($text['back']) ?>
          </a>
        </div>
      </form>
    </div>

   

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle sidebar
document.getElementById('toggleBtn')?.addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('collapsed');
});

// Check file type and show appropriate message
function checkFileType(input) {
  const fileInfo = document.getElementById('generatedFileInfo');
  if (input.files && input.files[0]) {
    const fileName = input.files[0].name.toLowerCase();
    if (fileName.includes('generated') || fileName.includes('letter')) {
      fileInfo.style.display = 'block';
    } else {
      fileInfo.style.display = 'none';
    }
  }
}

// Handle direct upload from letter generator
function handleGeneratedLetterUpload(fileData, letterData) {
  // Fill form fields with generated letter data
  document.getElementById('ref_no').value = letterData.refNo || '';
  document.getElementById('sender').value = letterData.sender || '';
  document.getElementById('receiver').value = letterData.receiver || '';
  document.getElementById('subject').value = letterData.subject || '';
  document.getElementById('description').value = letterData.description || '';
  
  // Set the file input (this would need additional handling for security)
  console.log('Generated letter data received:', letterData);
  
  // Show success message
  const isEnglish = document.querySelector('.lang-en').style.display !== 'none';
  alert(isEnglish ? 
    'Letter data received from generator. Please complete the remaining fields and submit.' : 
    'የደብዳቤ መረጃ ከጀነሬተር ተቀብሏል። እባክዎ የቀሩትን መስኮች ይሙሉ እና ያስገቡ።');
}

// Add form submission handler
document.getElementById('letterForm').addEventListener('submit', function(e) {
  console.log('Form submitted');
  const saveButton = document.getElementById('saveButton');
  saveButton.disabled = true;
  saveButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + 
    (document.querySelector('.lang-en').style.display !== 'none' ? 'Saving...' : 'በማስቀመጥ ላይ...');
});

// Check for URL parameters from letter generator
window.addEventListener('load', function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('generated')) {
    document.getElementById('generatedFileInfo').style.display = 'block';
    
    // Show success message
    const isEnglish = document.querySelector('.lang-en').style.display !== 'none';
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show';
    alertDiv.innerHTML = `
      <i class="fa fa-check-circle me-2"></i>
      <span class="lang-en">Letter generated successfully! Please complete the form and upload.</span>
      <span class="lang-am">ደብዳቤ በተሳካ ሁኔታ ተፈጥሯል! እባክዎ ቅጹን ይሙሉ እና ያስገቡ።</span>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.content').insertBefore(alertDiv, document.querySelector('.topbar').nextSibling);
  }
});

// Language detection for dynamic content
function updateDynamicContent() {
  const isEnglish = document.querySelector('.lang-en').style.display !== 'none';
  // Update any dynamic content here if needed
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  updateDynamicContent();
});
</script>

<!-- Support for cross-page communication with letter generator -->
<script>
// Listen for messages from letter generator
window.addEventListener('message', function(event) {
  // Verify the origin for security
  if (event.origin !== window.location.origin) return;
  
  if (event.data.type === 'GENERATED_LETTER') {
    handleGeneratedLetterUpload(event.data.file, event.data.letterData);
  }
});
</script>
</body>
</html>