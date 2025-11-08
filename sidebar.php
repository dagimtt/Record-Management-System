<?php
// sidebar.php
if (!isset($lang)) {
    $lang = 'en';
}

$text_sidebar = [
    'en' => [
        'dashboard' => 'Dashboard',
        'view_letters' => 'View Letters',
        'incoming' => 'Incoming',
        'outgoing' => 'Outgoing',
        'upload' => 'Upload',
        'logout' => 'Logout'
    ],
    'am' => [
        'dashboard' => 'ዳሽቦርድ',
        'view_letters' => 'ደብዳቤዎችን እይ',
        'incoming' => 'የመጣ',
        'outgoing' => 'የተላከ',
        'upload' => 'መጫን',
        'logout' => 'ውጣ'
    ]
][$lang];
?>
<div class="sidebar" id="sidebar">
  <div class="logo d-flex justify-content-between align-items-center">
    <span><i class="fa fa-building me-2"></i> <span class="logo-text">Archive</span></span>
    <button class="toggle-btn" id="toggleBtn"><i class="fa fa-bars"></i></button>
  </div>
  <ul>
    <li><a href="chief_officer.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'chief_officer.php' ? 'active' : '' ?>"><i class="fa fa-home"></i> <span><?= htmlspecialchars($text_sidebar['dashboard']) ?></span></a></li>
    <li><a href="dashboard.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fa fa-envelope"></i> <span><?= htmlspecialchars($text_sidebar['view_letters']) ?></span></a></li>
    <li><a href="upload_letter.php?lang=<?= $lang ?>"><i class="fa fa-upload"></i> <span><?= htmlspecialchars($text_sidebar['upload']) ?></span></a></li>
    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> <span><?= htmlspecialchars($text_sidebar['logout']) ?></span></a></li>
  </ul>
</div>