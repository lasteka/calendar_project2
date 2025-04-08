<div class="container">
    <?php foreach ($timeslots as $dayPart => $slotArray): ?>
        <div class="day-part">
            <h2><?php echo htmlspecialchars($dayPart); ?></h2>
            <div class="time-slots">
                <?php foreach ($slotArray as $slot): ?>
                    <div class="time-slot">
                        <a href="?action=show_services&slot=<?php echo urlencode($slot); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>&selected_date=<?php echo $selectedDate; ?>">
                            <?php echo htmlspecialchars($slot); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>