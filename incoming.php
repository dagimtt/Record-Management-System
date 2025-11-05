<?php
session_start();
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
include("db.php");

// Check if user is logged in and is an officer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['position'] !== 'officer') {
    header("Location: login.php");
    exit();
}

$text = [
    'en' => [
        'title' => '📥 Incoming Letters',
        'search' => 'Search Letters',
        'new' => 'New',
        'from' => 'From',
        'to' => 'To',
        'subject' => 'Subject',
        'date' => 'Date',
        'type' => 'Type',
        'actions' => 'Actions',
        'view' => 'View',
        'download' => 'Download',
        'no_record' => 'No record found'
    ],
    'am' => [
        'title' => '📥 የመጣ ደብዳቤ',
        'search' => 'ፈልግ',
        'new' => 'አዲስ',
        'from' => 'ከ',
        'to' => 'ወደ',
        'subject' => 'ርዕስ',
        'date' => 'ቀን',
        'type' => 'አይነት',
        'actions' => 'ተግባሮች',
        'view' => 'እይ',
        'download' => 'አዉርድ',
        'no_record' => 'ምንም መዝገብ አልተገኘም'
    ]
][$lang];

// Search only incoming letters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM letters WHERE type='incoming'";
if ($search) {
    $query .= " AND (subject LIKE :search OR ref_no LIKE :search OR sender LIKE :search OR receiver LIKE :search)";
}

$stmt = $conn->prepare($query);
if ($search) $stmt->bindValue(':search', "%$search%");
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
.btn-new {
  background: linear-gradient(45deg, #123AAE, #007bff);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 18px;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn-new:hover {
  background: linear-gradient(45deg, #0f2f8a, #0056b3);
  color: white;
  transform: translateY(-2px);
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
.btn-new {
  background: linear-gradient(45deg, #ffffff, #f0f0f0);
  color: #123AAE !important;
  border: 2px solid #123AAE;
  border-radius: 10px;
  padding: 8px 22px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(18, 58, 174, 0.2);
}

.btn-new:hover {
  background: #123AAE;
  color: white !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(18, 58, 174, 0.3);
}

.table thead th {
  background-color: #123AAE;
  color: white;
  text-align: center;
}
.table-hover tbody tr:hover { background-color: rgba(18,58,174,0.05); }
</style>
</head>
<body>

<div class="wrapper">
  <?php include("officerSidbar.php"); ?>

  <div class="content">
    <div class="topbar">
      <h4 class="m-0"><?= htmlspecialchars($text['title']) ?></h4>
      <div class="d-flex align-items-center gap-2">
        <a href="?lang=en" class="btn btn-sm btn-outline-primary <?= $lang == 'en' ? 'active' : '' ?>">English</a>
        <a href="?lang=am" class="btn btn-sm btn-outline-primary <?= $lang == 'am' ? 'active' : '' ?>">አማርኛ</a>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message']['type'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="search-container mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <form method="get" class="row g-2 align-items-center flex-grow-1">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">

        <div class="col-md-8 position-relative">
          <i class="fa fa-search search-icon"></i>
          <input type="text" name="search" class="search-bar" placeholder="<?= htmlspecialchars($text['search']) ?>" value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-2">
          <button class="btn btn-primary w-100">
            <i class="fa fa-search"></i> <?= htmlspecialchars($text['search']) ?>
          </button>
        </div>
      </form>

      <a href="new_letter.php?lang=<?= htmlspecialchars($lang) ?>" class="btn btn-new ms-auto">
        <i class="fa fa-plus"></i> <?= htmlspecialchars($text['new']) ?>
      </a>
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
              <th>Status</th>
              <th><?= htmlspecialchars($text['actions']) ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($rows) > 0): $i = 1; ?>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['ref_no'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['sender'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['receiver'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['subject'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['date_received_sent'] ?? $row['created_at'] ?? '') ?></td>
                  <td>
                    <?php
                      $status = strtolower($row['status'] ?? 'pending');
                      $badgeClass = match($status) {
                        'new' => 'success',
                        'read' => 'secondary',
                        'pending' => 'warning',
                        'archived' => 'dark',
                        default => 'info'
                      };
                    ?>
                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                  </td>
                  <td class="text-center">
                    <a href="detail_letter.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-outline-success me-1">
                      <i class="fa fa-eye"></i>
                    </a>
                    <?php if (!empty($row['file_path']) && file_exists($row['file_path'])): ?>
                      <a href="<?= htmlspecialchars($row['file_path']) ?>" download class="btn btn-sm btn-outline-primary me-1">
                        <i class="fa fa-download"></i>
                      </a>
                    <?php else: ?>
                      <button class="btn btn-sm btn-outline-secondary me-1" disabled>
                        <i class="fa fa-ban"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted"><?= htmlspecialchars($text['no_record']) ?></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('toggleBtn')?.addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('collapsed');
});
</script>
</body>
</html>