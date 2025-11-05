<?php
// sidebar.php
if (!isset($lang)) {
    $lang = 'en';
}

$text_sidebar = [
    'en' => [
        'dashboard' => 'Dashboard',
        'incoming' => 'Incoming',
        'outgoing' => 'Outgoing',
        'upload' => 'Upload',
        'logout' => 'Logout'
    ],
    'am' => [
        'dashboard' => 'ዳሽቦርድ',
        'incoming' => 'የመጣ',
        'outgoing' => 'የተላከ',
        'upload' => 'መጫን',
        'logout' => 'ውጣ'
    ]
][$lang];
?>
<div class="sidebar" id="sidebar">
  <div class="logo d-flex justify-content-between align-items-center">
    <span><i class="fa fa-building me-2"></i> <span class="logo-text">Incoming</span></span>
    <button class="toggle-btn" id="toggleBtn"><i class="fa fa-bars"></i></button>
  </div>
  <ul>
    <li><a href="#" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fa fa-home"></i> <span><?= htmlspecialchars($text_sidebar['dashboard']) ?></span></a></li>
   <li><a href="incoming.php?lang=<?= $lang ?>"><i class="fa fa-inbox"></i> <span><?= htmlspecialchars($text_sidebar['incoming']) ?></span></a></li>
    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> <span><?= htmlspecialchars($text_sidebar['logout']) ?></span></a></li>
  </ul>
</div>
