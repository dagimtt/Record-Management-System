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
$_SESSION['last_activity'] = time();

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

include("db.php");

// Get statistics for dashboard
$total_letters_query = "SELECT COUNT(*) as total FROM letters";
$incoming_query = "SELECT COUNT(*) as incoming FROM letters WHERE type='incoming'";
$outgoing_query = "SELECT COUNT(*) as outgoing FROM letters WHERE type='outgoing'";
$pending_query = "SELECT COUNT(*) as pending FROM letters WHERE status='pending' OR status='new'";

$total_letters = $conn->query($total_letters_query)->fetch(PDO::FETCH_ASSOC)['total'];
$incoming_letters = $conn->query($incoming_query)->fetch(PDO::FETCH_ASSOC)['incoming'];
$outgoing_letters = $conn->query($outgoing_query)->fetch(PDO::FETCH_ASSOC)['outgoing'];
$pending_letters = $conn->query($pending_query)->fetch(PDO::FETCH_ASSOC)['pending'];

// Get recent letters
$recent_query = "SELECT * FROM letters ORDER BY created_at DESC LIMIT 5";
$recent_letters = $conn->query($recent_query)->fetchAll(PDO::FETCH_ASSOC);

$text = [
    'en' => [
        'title' => 'Dashboard',
        'welcome' => 'Welcome, Chief Officer',
        'overview' => 'Overview',
        'total_letters' => 'Total Letters',
        'incoming' => 'Incoming',
        'outgoing' => 'Outgoing',
        'Packed' => 'Packed',
        'recent_activity' => 'Recent Activity',
        'quick_actions' => 'Quick Actions',
        'add_letter' => 'Add New Letter',
        'view_all' => 'View All Letters',
        'search' => 'Search',
        'from' => 'From',
        'to' => 'To',
        'subject' => 'Subject',
        'date' => 'Date',
        'type' => 'Type',
        'status' => 'Status',
        'actions' => 'Actions',
        'view' => 'View',
        'download' => 'Download',
        'no_record' => 'No recent activity',
        'view_details' => 'View Details',
        'manage_users' => 'Manage Users',
        'reports' => 'Reports',
        'settings' => 'Settings'
    ],
    'am' => [
        'title' => 'ዳሽቦርድ',
        'welcome' => 'እንኳን ደህና መጡ ሹም ባለሙያ',
        'overview' => 'አጠቃላይ እይታ',
        'total_letters' => 'ጠቅላላ ደብዳቤዎች',
        'incoming' => 'የመጡ',
        'outgoing' => 'የተላኩ',
        'Packed' => 'የታሸጉ',
        'recent_activity' => 'የቅርብ እንቅስቃሴ',
        'quick_actions' => 'ፈጣን ተግባራት',
        'add_letter' => 'አዲስ ደብዳቤ ጨምር',
        'view_all' => 'ሁሉንም ደብዳቤዎች እይ',
        'search' => 'ፈልግ',
        'from' => 'ከ',
        'to' => 'ወደ',
        'subject' => 'ርዕስ',
        'date' => 'ቀን',
        'type' => 'አይነት',
        'status' => 'ሁኔታ',
        'actions' => 'ተግባሮች',
        'view' => 'እይ',
        'download' => 'አውርድ',
        'no_record' => 'ምንም የቅርብ እንቅስቃሴ የለም',
        'view_details' => 'ዝርዝሮችን እይ',
        'manage_users' => 'ተጠቃሚዎችን አስተዳድር',
        'reports' => 'ሪፖርቶች',
        'settings' => 'ቅንብሮች'
    ]
][$lang];
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

.dashboard-header {
  background: linear-gradient(135deg, #123AAE 0%, #1e4fd8 100%);
  color: white;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 5px 15px rgba(18,58,174,0.2);
}

.stats-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%;
  border-left: 4px solid #123AAE;
}

.stats-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.stats-card.total { border-left-color: #123AAE; }
.stats-card.incoming { border-left-color: #28a745; }
.stats-card.outgoing { border-left-color: #17a2b8; }
.stats-card.pending { border-left-color: #ffc107; }

.stats-icon {
  font-size: 2.5rem;
  opacity: 0.8;
  margin-bottom: 15px;
}

.stats-number {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 5px;
}

.stats-label {
  font-size: 0.9rem;
  color: #6c757d;
}

.activity-card, .actions-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.08);
  height: 100%;
}

.card-title {
  font-weight: 600;
  margin-bottom: 20px;
  color: #123AAE;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.quick-action {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 10px;
  transition: all 0.2s;
  text-decoration: none;
  color: #333;
}

.quick-action:hover {
  background: #e9ecef;
  transform: translateX(5px);
  color: #123AAE;
}

.action-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: #123AAE;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 1.2rem;
}

.btn-action {
  padding: 5px 10px;
  font-size: 0.85rem;
}

.table thead th {
  background-color: #123AAE;
  color: white;
  text-align: center;
}

.table-hover tbody tr:hover { background-color: rgba(18,58,174,0.05); }

.welcome-text {
  font-size: 1.8rem;
  font-weight: 600;
  margin-bottom: 5px;
}

.welcome-subtext {
  opacity: 0.9;
  font-size: 1rem;
}

@media (max-width: 768px) {
  .content {
    margin-left: 0;
    padding: 15px;
  }
  
  .sidebar {
    width: 70px;
  }
  
  .sidebar .logo-text {
    display: none;
  }
  
  .stats-card {
    margin-bottom: 15px;
  }
}
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

    <div class="dashboard-header">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="welcome-text"><?= htmlspecialchars($text['welcome']) ?></div>
          <div class="welcome-subtext"><?= htmlspecialchars($text['overview']) ?></div>
        </div>
        <div class="col-md-4 text-md-end">
          <div class="d-inline-block bg-white text-dark rounded-pill px-4 py-2">
            <i class="fas fa-calendar-alt me-2"></i>
            <?= date('F j, Y') ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="stats-card total">
          <div class="stats-icon text-primary">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="stats-number"><?= $total_letters ?></div>
          <div class="stats-label"><?= htmlspecialchars($text['total_letters']) ?></div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stats-card incoming">
          <div class="stats-icon text-success">
            <i class="fas fa-download"></i>
          </div>
          <div class="stats-number"><?= $incoming_letters ?></div>
          <div class="stats-label"><?= htmlspecialchars($text['incoming']) ?></div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stats-card outgoing">
          <div class="stats-icon text-info">
            <i class="fas fa-upload"></i>
          </div>
          <div class="stats-number"><?= $outgoing_letters ?></div>
          <div class="stats-label"><?= htmlspecialchars($text['outgoing']) ?></div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="stats-card pending">
          <div class="stats-icon text-warning">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stats-number"><?= $pending_letters ?></div>
          <div class="stats-label"><?= htmlspecialchars($text['Packed']) ?></div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Sidebar toggle functionality
  const toggleBtn = document.getElementById('toggleBtn');
  const sidebar = document.getElementById('sidebar');
  
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('collapsed');
    });
  }
  
  // Update the current time
  function updateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString('en-US', options);
    document.querySelector('.dashboard-header .bg-white').innerHTML = 
      `<i class="fas fa-calendar-alt me-2"></i>${dateString}`;
  }
  
  // Update time every minute
  updateTime();
  setInterval(updateTime, 60000);
});
</script>
</body>
</html>