<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>MR ONLAINS Calendar</title>
    <link rel="stylesheet" href="../css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/calendar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/timeslots.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/procedures.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/bookings.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/book.css?v=<?php echo time(); ?>">
    <script src="../js/calendar.js" defer></script>
</head>
<body>
    <header>
        <h1>MR ONLAINS</h1>
        <p id="selected_date_display_container">Procedūras: Izvēlētais datums: <span id="selected_date_display"><?php echo isset($selected_date) ? htmlspecialchars($selected_date) : date('Y-m-d'); ?></span></p>
        
        <nav class="auth-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php 
                // In a real application, user's name might be fetched and displayed
                // For example, if $user_name is available in this scope:
                // if (isset($user_name)) {
                //     echo '<span class="user-greeting">Sveiki, ' . htmlspecialchars($user_name) . '!</span>';
                // }
                ?>
                <a href="../logout.php">Izlogoties (Logout)</a>
            <?php else: ?>
                <a href="login.php">Ielogoties (Login)</a>
                <a href="register.php" style="margin-left: 10px;">Reģistrēties (Register)</a>
            <?php endif; ?>
        </nav>
    </header>