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
    <script src="../js/calendar.js" defer></script>
</head>
<body>
    <header>
        <h1>MR ONLAINS</h1>
        <p>Procedūras: Izvēlētais datums: <?php echo isset($selected_date) ? htmlspecialchars($selected_date) : date('Y-m-d'); ?></p>
    </header>