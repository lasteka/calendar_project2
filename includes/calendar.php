<?php
// Definējam kalendāra mainīgos
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Aprēķinām kalendāra datus
$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfWeek = date('w', mktime(0, 0, 0, $month, 1, $year));
$prevMonth = ($month == 1) ? 12 : $month - 1;
$prevYear = ($month == 1) ? $year - 1 : $year;
$nextMonth = ($month == 12) ? 1 : $month + 1;
$nextYear = ($month == 12) ? $year + 1 : $year;
?>
<div class="calendar-container">
    <h2><?php echo $monthName . " " . $year; ?></h2>
    <div class="month-navigation">
        <div class="month-dropdown">
            <span class="month-name"><?php echo $monthName; ?></span>
            <span class="arrow">▲</span>
        </div>
        <div class="nav-arrows">
            <a class="prev" href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>&selected_date=<?php echo urlencode($selected_date); ?>"><</a>
            <a class="next" href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>&selected_date=<?php echo urlencode($selected_date); ?>">></a>
        </div>
    </div>
    <table class="calendar">
        <thead>
            <tr class="header">
                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php
                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    echo "<td></td>";
                }
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = "$year-$month-" . sprintf("%02d", $day);
                    $isSelected = ($currentDate === $selected_date) ? 'selected' : '';
                    echo "<td class='selectable $isSelected'>";
                    echo "<a href='?selected_date=$currentDate&month=$month&year=$year'>$day</a>";
                    echo "</td>";
                    if ((($day + $firstDayOfWeek) % 7) == 0) {
                        echo "</tr><tr>";
                    }
                }
                $remaining = (7 - (($day + $firstDayOfWeek - 1) % 7)) % 7;
                for ($i = 0; $i < $remaining; $i++) {
                    echo "<td></td>";
                }
                ?>
            </tr>
        </tbody>
    </table>
</div>