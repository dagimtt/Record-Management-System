<?php
session_start();

// Check if user is logged in and is an admin
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
$_SESSION['last_activity'] = time();$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

include("db.php");

$text = [
    'en' => [
        'title' => '📂 Archive Dashboard',
        'search' => 'Search Letters',
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
        'no_record' => 'No record found'
    ],
    'am' => [
        'title' => '📂 የሰነዶች መዝገብ',
        'search' => 'ፈልግ',
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
        'no_record' => 'ምንም መዝገብ አልተገኘም'
    ]
][$lang];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$query = "SELECT * FROM letters WHERE 1=1";
if ($search) $query .= " AND (subject LIKE :search OR ref_no LIKE :search OR sender LIKE :search OR receiver LIKE :search)";
if ($type != 'all') $query .= " AND type=:type";

$stmt = $conn->prepare($query);
if ($search) $stmt->bindValue(':search', "%$search%");
if ($type != 'all') $stmt->bindValue(':type', $type);
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
</style>
</head>
<body>

<div class="wrapper">
  <?php include("sidebar.php"); ?>

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
          <input type="text" name="search" class="search-bar" placeholder="<?= htmlspecialchars($text['search']) ?>" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <select name="type" class="form-select">
            <option value="all" <?= $type == 'all' ? 'selected' : '' ?>><?= htmlspecialchars($text['all']) ?></option>
            <option value="incoming" <?= $type == 'incoming' ? 'selected' : '' ?>><?= htmlspecialchars($text['incoming']) ?></option>
            <option value="outgoing" <?= $type == 'outgoing' ? 'selected' : '' ?>><?= htmlspecialchars($text['outgoing']) ?></option>

          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100"><i class="fa fa-search"></i> <?= htmlspecialchars($text['search']) ?></button>
        </div>
      </form>
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
    <th><?= htmlspecialchars($text['type']) ?></th>
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
        <td><?= htmlspecialchars($row['created_at'] ?? '') ?></td>

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
          <span class="badge bg-<?= $badgeClass ?>">
            <?= ucfirst($status) ?>
          </span>
        </td>

        <td><?= ucfirst(htmlspecialchars($row['type'] ?? '')) ?></td>

       <td class="text-center">
 <a href="detail_letter.php?id=<?= urlencode($row['id']) ?>" 
   class="btn btn-sm btn-outline-success btn-action me-1">
    <i class="fa fa-eye"></i>
</a>

  <?php if (!empty($row['file_path']) && file_exists($row['file_path'])): ?>
    <a href="<?= htmlspecialchars($row['file_path']) ?>" 
       download 
       class="btn btn-sm btn-outline-primary btn-action me-1">
        <i class="fa fa-download"></i>
    </a>
  <?php else: ?>
    <button class="btn btn-sm btn-outline-secondary btn-action me-1" disabled>
      <i class="fa fa-ban"></i>
    </button>
  <?php endif; ?>

  <a href="replay_letter.php?ref_no=<?= urlencode($row['ref_no']) ?>" 
     class="btn btn-sm btn-outline-warning btn-action">
      <i class="fa fa-reply"></i>
  </a>
</td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="9" class="text-center text-muted">
        <?= htmlspecialchars($text['no_record']) ?>
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
</script>
</body>
</html>
