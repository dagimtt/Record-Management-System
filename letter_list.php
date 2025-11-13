<?php
session_start();

/*// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['position'] !== 'director') {
    header("Location: login.php");
    exit();
}
    */

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

// Display success message if set
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['success_message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['success_message']); // Clear the message after displaying
}

$text = [
    'en' => [
        'title' => '📂 Archive',
        'search' => 'Search Letters',
        'search_placeholder' => 'Search by Subject, Ref No, Req No, Sender, Receiver...',
        'incoming' => 'Incoming Letters',
        'outgoing' => 'Outgoing Letters',
        'all' => 'All',
        'from' => 'From',
        'to' => 'To',
        'subject' => 'Subject',
        'date' => 'Date',
        'type' => 'Type',
        'actions' => 'Actions',
        'view' => 'View',
        'download' => 'download',
        'no_record' => 'No record found',
        'req_num' => 'Req Num',
        'ref_no' => 'Ref No',
        'status' => 'Status'
    ],
    'am' => [
        'title' => '📂 የሰነዶች መዝገብ',
        'search' => 'ፈልግ',
        'search_placeholder' => 'በርዕሰ ጉዳይ፣ Ref No፣ Req No፣ ላኪ፣ ተቀባይ ፈልግ...',
        'incoming' => 'የመጣ ደብዳቤ',
        'outgoing' => 'የተላከ ደብዳቤ',
        'all' => 'ሁሉም',
        'from' => 'ከ',
        'to' => 'ወደ',
        'subject' => 'ርዕስ',
        'date' => 'ቀን',
        'type' => 'አይነት',
        'actions' => 'ተግባሮች',
        'view' => 'እይ',
        'download' => 'አዉርድ',
        'no_record' => 'ምንም መዝገብ አልተገኘም',
        'req_num' => 'Req Num',
        'ref_no' => 'Ref No',
        'status' => 'ሁኔታ'
    ]
][$lang];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Build the search query - EXCLUDE 'new' and 'pending' status
$query = "SELECT * FROM letters WHERE status NOT IN ('new', 'pending')";
$params = [];

if ($search) {
    $query .= " AND (subject LIKE :search 
                OR ref_no LIKE :search 
                OR request_number LIKE :search 
                OR sender LIKE :search 
                OR receiver LIKE :search 
                OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($type != 'all') {
    $query .= " AND type = :type";
    $params[':type'] = $type;
}

// Add ordering by latest first
$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
.search-container {
  background: white;
  padding: 15px;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.search-bar {
  border-radius: 25px;
  border: 1px solid #ccc;
  padding: 10px 40px;
  width: 100%;
  transition: all 0.3s ease;
}
.search-bar:focus {
  border-color: #123AAE;
  box-shadow: 0 0 5px rgba(18,58,174,0.4);
}
.search-icon {
  position: absolute;
  left: 15px;
  top: 10px;
  color: #777;
}
.table thead th {
  background-color: #123AAE;
  color: white;
  text-align: center;
}
.table-hover tbody tr:hover { background-color: rgba(18,58,174,0.05); }
.badge { font-size: 0.75em; }
.btn-action { padding: 4px 8px; }

/* New styles for archive page */
.archive-info {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.archive-info h5 {
  margin: 0;
  font-weight: 600;
}
.archive-info p {
  margin: 5px 0 0 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

.status-badge {
  font-size: 0.7rem;
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: 600;
}

/* Enhanced card styling */
.enhanced-card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.enhanced-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

/* Filter badges */
.filter-badge {
  background: #e9ecef;
  color: #495057;
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  margin-right: 5px;
}
</style>
</head>
<body>

<div class="wrapper">
  <?php include("directorsidbar.php"); ?>

  <div class="content">
    <div class="topbar">
      <h4 class="m-0"><?= htmlspecialchars($text['title']) ?></h4>
      <div>
        <a href="?lang=en" class="btn btn-sm btn-outline-primary <?= $lang == 'en' ? 'active' : '' ?>">English</a>
        <a href="?lang=am" class="btn btn-sm btn-outline-primary <?= $lang == 'am' ? 'active' : '' ?>">አማርኛ</a>
      </div>
    </div>


    <div class="search-container mb-4">
      <form method="get" class="row g-2 align-items-center">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
        <div class="col-md-5 position-relative">
          <i class="fa fa-search search-icon"></i>
          <input type="text" name="search" class="search-bar" 
                 placeholder="<?= htmlspecialchars($text['search_placeholder']) ?>" 
                 value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <select name="type" class="form-select">
            <option value="all" <?= $type == 'all' ? 'selected' : '' ?>><?= htmlspecialchars($text['all']) ?></option>
            <option value="incoming" <?= $type == 'incoming' ? 'selected' : '' ?>><?= htmlspecialchars($text['incoming']) ?></option>
            <option value="outgoing" <?= $type == 'outgoing' ? 'selected' : '' ?>><?= htmlspecialchars($text['outgoing']) ?></option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100">
            <i class="fa fa-search"></i> <?= htmlspecialchars($text['search']) ?>
          </button>
        </div>
      </form>
      
      <!-- Search results info -->
      <?php if ($search || $type != 'all'): ?>
        <div class="mt-3">
          <small class="text-muted">
            <?php 
              $result_count = count($rows);
              if ($search && $type != 'all') {
                echo "Showing $result_count results for \"$search\" in " . ucfirst($type) . " letters";
              } elseif ($search) {
                echo "Showing $result_count results for \"$search\"";
              } elseif ($type != 'all') {
                echo "Showing $result_count " . ucfirst($type) . " letters";
              }
            ?>
            <?php if ($search || $type != 'all'): ?>
              <a href="?lang=<?= $lang ?>" class="text-danger ms-2">
                <i class="fa fa-times"></i> Clear filters
              </a>
            <?php endif; ?>
          </small>
        </div>
      <?php endif; ?>
    </div>

    <div class="card enhanced-card p-3">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th><?= htmlspecialchars($text['ref_no']) ?></th>
              <th><?= htmlspecialchars($text['req_num']) ?></th>
              <th><?= htmlspecialchars($text['from']) ?></th>
              <th><?= htmlspecialchars($text['to']) ?></th>
              <th><?= htmlspecialchars($text['subject']) ?></th>
              <th><?= htmlspecialchars($text['date']) ?></th>
              <th><?= htmlspecialchars($text['status']) ?></th>
              <th><?= htmlspecialchars($text['type']) ?></th>
              <th><?= htmlspecialchars($text['actions']) ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($rows) > 0): $i = 1; ?>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td>
                    <?php if (!empty($row['ref_no'])): ?>
                      <span class="fw-bold"><?= htmlspecialchars($row['ref_no']) ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($row['request_number'])): ?>
                      <span class="fw-bold text-primary"><?= htmlspecialchars($row['request_number']) ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($row['sender'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['receiver'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['subject'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['date_received_sent'] ?? $row['created_at'] ?? '') ?></td>
                  <td>
                    <?php
                      $status = strtolower($row['status'] ?? '');
                      $badgeClass = match($status) {
                        'read' => 'secondary',
                        'sent' => 'info',
                        'archived' => 'dark',
                        'completed' => 'success',
                        'replied' => 'success',
                        default => 'info'
                      };
                    ?>
                    <span class="badge bg-<?= $badgeClass ?> status-badge">
                      <?= ucfirst($status) ?>
                    </span>
                  </td>
                  <td>
                    <?php 
                      $typeBadge = $row['type'] ?? '';
                      $typeClass = $typeBadge == 'incoming' ? 'info' : 'primary';
                    ?>
                    <span class="badge bg-<?= $typeClass ?> status-badge">
                      <?= ucfirst(htmlspecialchars($typeBadge)) ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <!-- View Details Button 
                    <?php if (strtolower($row['status'] ?? '') != 'reply'): ?>
                      <a href="letter_detail.php?id=<?= $row['id'] ?>" 
                         class="btn btn-sm btn-outline-primary btn-action me-1"
                         title="View Details">
                        <i class="fa fa-eye"></i>
                      </a>
                      -->
                    <?php else: ?>
                      <button class="btn btn-sm btn-outline-secondary btn-action me-1" disabled
                              title="View Not Available for Reply Letters">
                        <i class="fa fa-eye"></i>
                      </button>
                    <?php endif; ?>

                    <!-- Download Button -->
                    <?php if (strtolower($row['status'] ?? '') != 'reply'): ?>
                      <?php if (!empty($row['file_path']) && file_exists($row['file_path'])): ?>
                        <a href="<?= htmlspecialchars($row['file_path']) ?>" 
                           download 
                           class="btn btn-sm btn-outline-success btn-action me-1"
                           title="Download File">
                          <i class="fa fa-download"></i>
                        </a>
                      <?php else: ?>
                        <button class="btn btn-sm btn-outline-secondary btn-action me-1" disabled
                                title="No file available">
                          <i class="fa fa-ban"></i>
                        </button>
                      <?php endif; ?>
                    <?php else: ?>
                      <button class="btn btn-sm btn-outline-secondary btn-action me-1" disabled
                              title="Download Not Available for Reply Letters">
                        <i class="fa fa-download"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="text-center text-muted py-4">
                  <i class="fa fa-inbox fa-3x mb-3"></i><br>
                  <?= htmlspecialchars($text['no_record']) ?>
                  <?php if ($search || $type != 'all'): ?>
                    <br>
                    <small>Try adjusting your search criteria</small>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('toggleBtn').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('collapsed');
});

// Add some interactive features
document.addEventListener('DOMContentLoaded', function() {
  // Add animation to table rows
  const tableRows = document.querySelectorAll('tbody tr');
  tableRows.forEach((row, index) => {
    row.style.animationDelay = `${index * 0.05}s`;
    row.classList.add('fade-in');
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

<style>
/* Fade in animation for table rows */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn 0.5s ease forwards;
}
</style>
</body>
</html>