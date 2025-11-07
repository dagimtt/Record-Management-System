<?php
session_start();
include("db.php");

// Check if user is logged in and is a director
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['director_position'] !== 'director') {
    header("Location: login.php");
    exit();
}

// Get director's department from session (assuming it's stored during login)
$director_department = $_SESSION['director_department'] ?? '';

// If department is not in session, fetch it from database
if (empty($director_department)) {
    $stmt = $conn->prepare("SELECT department FROM users WHERE email = ? AND position = 'director'");
    $stmt->execute([$_SESSION['director_email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $director_department = $user['department'] ?? '';
    $_SESSION['director_department'] = $director_department;
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

$text = [
    'en' => [
        'title' => '📨 Director Panel',
        'incoming_letters' => 'Incoming Letters',
        'add_letter' => 'Add Letter',
        'from' => 'From',
        'to' => 'To',
        'subject' => 'Subject',
        'date' => 'Date',
        'actions' => 'Actions',
        'view' => 'View Detail',
        'read' => 'Mark as Read',
        'read_success' => 'Status updated to seen',
        'no_record' => 'No incoming letters found',
        'logout' => 'Logout',
        'notifications' => 'Notifications',
        'profile' => 'Profile',
        'lang_en' => 'English',
        'lang_am' => 'አማርኛ',
        'status' => 'Status',
        'new' => 'New',
        'seen' => 'Seen',
        'filter_by_status' => 'Filter by Status',
        'all' => 'All',
        'department' => 'Department',
        'my_department' => 'My Department',
    ],
    'am' => [
        'title' => '📨 የዳይሬክተር ፓነል',
        'incoming_letters' => 'የመጣ ደብዳቤ',
        'add_letter' => 'ደብዳቤ ጨምር',
        'from' => 'ከ',
        'to' => 'ወደ',
        'subject' => 'ርዕስ',
        'date' => 'ቀን',
        'actions' => 'ተግባሮች',
        'view' => 'ዝርዝር እይ',
        'read' => 'እንደተነበበ ምልክት አድርግ',
        'read_success' => 'ሁኔታ ወደ ተነበበ ተቀይሯል',
        'no_record' => 'ምንም የመጣ ደብዳቤ አልተገኘም',
        'logout' => 'ውጣ',
        'notifications' => 'ማሳወቂያዎች',
        'profile' => 'መገለጫ',
        'lang_en' => 'English',
        'lang_am' => 'አማርኛ',
        'status' => 'ሁኔታ',
        'new' => 'አዲስ',
        'seen' => 'ተነትቷል',
        'filter_by_status' => 'በሁኔታ አጣራ',
        'all' => 'ሁሉም',
        'department' => 'የስራ ክፍል',
        'my_department' => 'የኔ የስራ ክፍል',
    ]
][$lang];

// Handle Read button action
if (isset($_POST['mark_as_read'])) {
    $letter_id = $_POST['letter_id'];
    $stmt = $conn->prepare("UPDATE letters SET status = 'seen' WHERE id = ?");
    if ($stmt->execute([$letter_id])) {
        $_SESSION['success_message'] = $text['read_success'];
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?lang=" . $lang);
    exit();
}

// Get filter status from GET parameter, default to 'new'
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'new';

// Build query based on filter AND director's department
if ($filter_status === 'all') {
    $stmt = $conn->prepare("SELECT * FROM letters WHERE type = 'incoming' AND department = ? ORDER BY created_at DESC");
    $stmt->execute([$director_department]);
} else {
    $stmt = $conn->prepare("SELECT * FROM letters WHERE type = 'incoming' AND status = ? AND department = ? ORDER BY created_at DESC");
    $stmt->execute([$filter_status, $director_department]);
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count new (unread) letters for notification - FILTERED BY DEPARTMENT
$stmt = $conn->prepare("SELECT COUNT(*) FROM letters WHERE type='incoming' AND status='new' AND department = ?");
$stmt->execute([$director_department]);
$notificationCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($text['title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
  background-color: #f4f6fa;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
}
.wrapper { display: flex; min-height: 100vh; }

.sidebar {
  width: 250px;
  background: #0f2a7a;
  color: #fff;
  position: fixed;
  height: 100%;
  transition: all 0.3s ease;
  box-shadow: 3px 0 10px rgba(0,0,0,0.1);
}
.sidebar .logo {
  padding: 20px;
  font-weight: bold;
  font-size: 20px;
  text-align: center;
  background: rgba(255,255,255,0.1);
  border-bottom: 1px solid rgba(255,255,255,0.2);
}
.sidebar ul { list-style: none; margin: 0; padding: 0; }
.sidebar ul li a {
  display: flex;
  align-items: center;
  color: #fff;
  padding: 14px 20px;
  text-decoration: none;
  transition: background 0.2s;
}
.sidebar ul li a:hover,
.sidebar ul li a.active {
  background: rgba(255,255,255,0.15);
}
.sidebar ul li a i {
  width: 25px;
  font-size: 18px;
}

.content {
  margin-left: 250px;
  flex-grow: 1;
  padding: 20px;
}

.header {
  background: white;
  border-radius: 10px;
  padding: 12px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}
.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}
.notification {
  position: relative;
  cursor: pointer;
}
.notification .count {
  position: absolute;
  top: -5px;
  right: -8px;
  background: red;
  color: white;
  border-radius: 50%;
  font-size: 10px;
  padding: 3px 6px;
}
.profile-btn {
  display: flex;
  align-items: center;
  background: none;
  border: none;
  cursor: pointer;
}
.profile-btn img {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  margin-right: 8px;
}
.dropdown-menu {
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.table thead th {
  background: #0f2a7a;
  color: white;
  text-align: center;
}
.table-hover tbody tr:hover {
  background-color: rgba(15,42,122,0.05);
}
.btn-view {
  background: #0f2a7a;
  color: white;
}
.btn-view:hover {
  background: #143bb0;
}
.btn-read {
  background: #28a745;
  color: white;
  border: none;
}
.btn-read:hover {
  background: #218838;
}
.btn-read:disabled {
  background: #6c757d;
  cursor: not-allowed;
}
.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}
.status-new {
  background: #dc3545;
  color: white;
}
.status-seen {
  background: #28a745;
  color: white;
}
.modal-content {
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.alert-success {
  border-radius: 10px;
}
.filter-section {
  background: white;
  border-radius: 10px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.filter-label {
  font-weight: 600;
  margin-bottom: 8px;
  color: #0f2a7a;
}
.department-badge {
  background: #6f42c1;
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}
</style>
</head>

<body>
<div class="wrapper">
    <?php include("directorSidbar.php"); ?>

  <div class="content">
    <div class="header">
      <div>
        <h4 class="m-0"><?= htmlspecialchars($text['incoming_letters']) ?></h4>
        <small class="text-muted">
          <span class="department-badge">
            <i class="fa fa-building me-1"></i>
            <?= htmlspecialchars($text['my_department']) ?>: <?= htmlspecialchars($director_department) ?>
          </span>
        </small>
      </div>
      <div class="header-right">
        <div class="notification" data-bs-toggle="modal" data-bs-target="#notificationModal">
          <i class="fa fa-bell fa-lg text-primary"></i>
          <?php if ($notificationCount > 0): ?>
            <span class="count"><?= $notificationCount ?></span>
          <?php endif; ?>
        </div>

        <div class="dropdown">
          <button class="profile-btn dropdown-toggle" data-bs-toggle="dropdown">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <span><?= htmlspecialchars($_SESSION['director_name']) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header"><?= htmlspecialchars($text['profile']) ?></h6></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item"><strong>Name:</strong> <?= htmlspecialchars($_SESSION['director_name']) ?></a></li>
            <li><a class="dropdown-item"><strong>Email:</strong> <?= htmlspecialchars($_SESSION['director_email']) ?></a></li>
            <li><a class="dropdown-item"><strong>Position:</strong> <?= htmlspecialchars($_SESSION['director_position']) ?></a></li>
            <li><a class="dropdown-item"><strong><?= $text['department'] ?>:</strong> <?= htmlspecialchars($director_department) ?></a></li>
          </ul>
        </div>

        <div>
          <a href="?lang=en" class="btn btn-sm btn-outline-primary <?= $lang == 'en' ? 'active' : '' ?>"><?= $text['lang_en'] ?></a>
          <a href="?lang=am" class="btn btn-sm btn-outline-primary <?= $lang == 'am' ? 'active' : '' ?>"><?= $text['lang_am'] ?></a>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="filter-section">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="filter-label">
            <i class="fa fa-filter me-2"></i><?= htmlspecialchars($text['filter_by_status']) ?>
          </div>
        </div>
        <div class="col-md-6">
          <select class="form-select" id="statusFilter" onchange="window.location.href=this.value">
            <option value="?lang=<?= $lang ?>&status=new" <?= $filter_status == 'new' ? 'selected' : '' ?>>
              <?= htmlspecialchars($text['new']) ?>
            </option>
            <option value="?lang=<?= $lang ?>&status=seen" <?= $filter_status == 'seen' ? 'selected' : '' ?>>
              <?= htmlspecialchars($text['seen']) ?>
            </option>
            <option value="?lang=<?= $lang ?>&status=all" <?= $filter_status == 'all' ? 'selected' : '' ?>>
              <?= htmlspecialchars($text['all']) ?>
            </option>
          </select>
        </div>
      </div>
    </div>

    <div class="card p-3">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Ref No</th>
              <th><?= htmlspecialchars($text['from']) ?></th>
              <th><?= htmlspecialchars($text['to']) ?></th>
              <th><?= htmlspecialchars($text['subject']) ?></th>
              <th><?= htmlspecialchars($text['date']) ?></th>
              <th><?= htmlspecialchars($text['status']) ?></th>
              <th><?= htmlspecialchars($text['actions']) ?></th>
            </tr>
          </thead>
          <tbody>
          <?php if (count($rows) > 0): $i = 1; foreach ($rows as $row): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['ref_no'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['sender'] ?? '-') ?></td>
              <td>
                <?= htmlspecialchars($row['receiver'] ?? '-') ?>
                <?php if (!empty($row['to_department'])): ?>
                  <br><small class="text-muted">Dept: <?= htmlspecialchars($row['to_department']) ?></small>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['subject'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['created_at'] ?? '-') ?></td>
              <td>
                <span class="status-badge <?= $row['status'] == 'new' ? 'status-new' : 'status-seen' ?>">
                  <?= $row['status'] == 'new' ? $text['new'] : $text['seen'] ?>
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-view" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id'] ?>">
                  <i class="fa fa-eye"></i> <?= htmlspecialchars($text['view']) ?>
                </button>
              </td>
            </tr>

            <!-- View Modal -->
            <div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-envelope-open-text me-2"></i><?= htmlspecialchars($row['subject'] ?? 'Letter Detail') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <p><strong>Ref No:</strong> <?= htmlspecialchars($row['ref_no'] ?? '-') ?></p>
                        <p><strong><?= $text['from'] ?>:</strong> <?= htmlspecialchars($row['sender'] ?? '-') ?></p>
                        <p><strong><?= $text['to'] ?>:</strong> <?= htmlspecialchars($row['receiver'] ?? '-') ?></p>
                        <?php if (!empty($row['to_department'])): ?>
                          <p><strong><?= $text['department'] ?>:</strong> <?= htmlspecialchars($row['to_department']) ?></p>
                        <?php endif; ?>
                      </div>
                      <div class="col-md-6">
                        <p><strong><?= $text['subject'] ?>:</strong> <?= htmlspecialchars($row['subject'] ?? '-') ?></p>
                        <p><strong><?= $text['date'] ?>:</strong> <?= htmlspecialchars($row['created_at'] ?? '-') ?></p>
                        <p><strong><?= $text['status'] ?>:</strong> 
                          <span class="status-badge <?= $row['status'] == 'new' ? 'status-new' : 'status-seen' ?>">
                            <?= $row['status'] == 'new' ? $text['new'] : $text['seen'] ?>
                          </span>
                        </p>
                      </div>
                    </div>

                    <?php if (!empty($row['file_path']) && file_exists($row['file_path'])): ?>
                      <div class="mt-3">
                        <h6>Attached File:</h6>
                        <iframe src="<?= htmlspecialchars($row['file_path']) ?>" style="width:100%;height:400px;border:1px solid #ddd;border-radius:8px;"></iframe>
                      </div>
                    <?php else: ?>
                      <p class="text-muted mt-3">No attached file</p>
                    <?php endif; ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?php if ($row['status'] == 'new'): ?>
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="letter_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="mark_as_read" class="btn btn-read">
                          <i class="fa fa-check"></i> <?= htmlspecialchars($text['read']) ?>
                        </button>
                      </form>
                    <?php else: ?>
                      <button type="button" class="btn btn-read" disabled>
                        <i class="fa fa-check"></i> <?= htmlspecialchars($text['seen']) ?>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                <?= htmlspecialchars($text['no_record']) ?><br>
                <small class="text-muted">No letters found for <?= htmlspecialchars($director_department) ?> department</small>
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fa fa-bell me-2"></i><?= htmlspecialchars($text['notifications']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if ($notificationCount > 0): ?>
          <ul class="list-group">
            <?php foreach ($rows as $r): if ($r['status'] == 'new'): ?>
              <li class="list-group-item">
                <strong><?= htmlspecialchars($r['subject']) ?></strong><br>
                <small><?= htmlspecialchars($r['sender']) ?> • <?= htmlspecialchars($r['created_at']) ?></small>
              </li>
            <?php endif; endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted text-center">No new notifications</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-close success alert after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert-success');
    if (alert) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 3000);
    }
});
</script>
</body>
</html>