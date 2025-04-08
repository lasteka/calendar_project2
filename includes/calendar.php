<div class="calendar-container">
    <h2><?php echo $monthName . " " . $year; ?></h2>
    <div class="month-navigation">
        <div class="month-dropdown">
            <span class="month-name"><?php echo $monthName; ?></span>
            <span class="arrow">▲</span>
        </div>
        <div class="nav-arrows">
            <a class="prev" href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>&selected_date=<?php echo $selectedDate; ?>"><</a>
            <a class="next" href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>&selected_date=<?php echo $selectedDate; ?>">></a>
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
                    $isSelected = ($currentDate === $selectedDate) ? 'selected' : '';
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

<?php if (isset($cancelMessage)): ?>
    <div class="message">
        <?php echo htmlspecialchars($cancelMessage); ?>
    </div>
<?php endif; ?>