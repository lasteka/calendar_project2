<div class="container">
    <?php foreach ($timeslots as $day_part => $slot_array): ?>
        <div class="day-part">
            <h2><?php echo htmlspecialchars($day_part); ?></h2>
            <div class="time-slots">
                <?php foreach ($slot_array as $slot): ?>
                    <div class="time-slot">
                        <a href="?action=show_services&slot=<?php echo urlencode($slot); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>&selected_date=<?php echo urlencode($selected_date); ?>&service_id=<?php echo $selected_service_id; ?>">
                            <?php echo htmlspecialchars($slot); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>