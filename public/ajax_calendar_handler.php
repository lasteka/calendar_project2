<?php
require_once '../middleware.php'; // Handles session start and other middleware
require_once '../config/db_connection.php'; // Ensure this is your correct path to PDO
require_once '../includes/availability_functions.php'; // Path to your new functions

// Define calendar variables
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

// Calculate calendar data
$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfWeek = date('w', mktime(0, 0, 0, $month, 1, $year)); // 0 (for Sunday) through 6 (for Saturday)
$prevMonth = ($month == 1) ? 12 : $month - 1;
$prevYear = ($month == 1) ? $year - 1 : $year;
$nextMonth = ($month == 12) ? 1 : $month + 1;
$nextYear = ($month == 12) ? $year + 1 : $year;

// The following is the HTML structure from includes/calendar.php,
// modified to ensure it only outputs the calendar section.
// Note: The main container div now includes data attributes for current month, year, and selected date
// to help the JavaScript keep track of state.
?>
<div id="calendar-container-ajax" class="calendar-container" data-current-month="<?php echo $month; ?>" data-current-year="<?php echo $year; ?>" data-selected-date="<?php echo $selected_date; ?>">
    <h2><?php echo $monthName . " " . $year; ?></h2>
    <div class="month-navigation">
        <div class="month-dropdown">
            <span class="month-name"><?php echo $monthName; ?></span>
            <span class="arrow">▲</span>
        </div>
        <div class="nav-arrows">
            <a class="ajax-nav-link prev" href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>&selected_date=<?php echo urlencode($selected_date); ?>" data-month="<?php echo $prevMonth; ?>" data-year="<?php echo $prevYear; ?>"><</a>
            <a class="ajax-nav-link next" href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>&selected_date=<?php echo urlencode($selected_date); ?>" data-month="<?php echo $nextMonth; ?>" data-year="<?php echo $nextYear; ?>">></a>
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
                // Adjust firstDayOfWeek if it's 0 (Sunday) to be 7 for calculation, then back to 0 if needed or handle appropriately in loop
                $displayFirstDayOfWeek = ($firstDayOfWeek == 0) ? 0 : $firstDayOfWeek; // Assuming your week starts Sunday

                for ($i = 0; $i < $displayFirstDayOfWeek; $i++) {
                    echo "<td></td>";
                }

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = $year . "-" . sprintf("%02d", $month) . "-" . sprintf("%02d", $day);
                    $isSelected = ($currentDate === $selected_date) ? 'selected' : '';
                    $tdClass = 'selectable ' . $isSelected;

                    if ($service_id && strtotime($currentDate) >= strtotime(date('Y-m-d'))) { // Only check for current or future dates
                        if (hasAvailableSlotsForService($pdo, $currentDate, $service_id)) {
                            $tdClass .= ' has-available-slots';
                        }
                    }
                    
                    echo "<td class='$tdClass'>";
                    echo "<a class='ajax-date-link' href='?selected_date=$currentDate&month=$month&year=$year' data-date='$currentDate'>$day</a>";
                    echo "</td>";

                    if ((($day + $displayFirstDayOfWeek) % 7) == 0) {
                        echo "</tr><tr>";
                    }
                }
                // Fill remaining cells in the last week
                $remainingCells = (7 - (($daysInMonth + $displayFirstDayOfWeek) % 7)) % 7;
                for ($i = 0; $i < $remainingCells; $i++) {
                    echo "<td></td>";
                }
                ?>
            </tr>
        </tbody>
    </table>
</div>
