<?php
// directorsidbar.php
if (!isset($lang)) {
    $lang = 'en';
}

$text_sidebar = [
    'en' => [
        'dashboard' => 'Dashboard',
        'incoming' => 'Incoming',
        'outgoing' => 'Outgoing',
        'upload' => 'Upload',
        'Letter List' => 'Letter List',
        'logout' => 'Logout'
    ],
    'am' => [
        'dashboard' => 'ዳሽቦርድ',
        'incoming' => 'የመጣ',
        'outgoing' => 'የተላከ',
        'upload' => 'መጫን',
        'Letter List' => 'ደብዳበዎህች',
        'logout' => 'ውጣ'
    ]
][$lang];

// Check if the current director's department is "Bureau"
$show_letter_list = false;
if (isset($_SESSION['director_department']) && $_SESSION['director_department'] === 'Bureau') {
    $show_letter_list = true;
}
?>
<div class="sidebar" id="sidebar">
  <div class="logo d-flex justify-content-between align-items-center">
    <span><i class="fa fa-building me-2"></i> <span class="logo-text">Incoming</span></span>
    <button class="toggle-btn" id="toggleBtn"><i class="fa fa-bars"></i></button>
  </div>
  <ul>
    <li><a href="director_panel.php" class="<?= basename($_SERVER['PHP_SELF']) == 'director_panel.php' ? 'active' : '' ?>"><i class="fa fa-inbox"></i> <span><?= htmlspecialchars($text_sidebar['incoming']) ?></span></a></li>
    <li><a href="director_outgoing.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'director_outgoing.php' ? 'active' : '' ?>"><i class="fa fa-paper-plane"></i> <span><?= htmlspecialchars($text_sidebar['outgoing']) ?></span></a></li>
    
    <?php if ($show_letter_list): ?>
    <li><a href="letter_list.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'letter_list.php' ? 'active' : '' ?>"><i class="fa fa-list"></i> <span><?= htmlspecialchars($text_sidebar['Letter List']) ?></span></a></li>
    <?php endif; ?>
    
    <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> <span><?= htmlspecialchars($text_sidebar['logout']) ?></span></a></li>
  </ul>
</div>