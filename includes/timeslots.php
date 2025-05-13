<div class="container">
  <div class="accordion">
    <?php
    $day_parts = ['Morning', 'Day', 'Evening'];
    foreach ($day_parts as $day_part):
      $slot_array = isset($timeslots[$day_part]) ? $timeslots[$day_part] : [];
    ?>
      <div class="accordion-header">
        <?php echo htmlspecialchars($day_part); ?>
        <span class="arrow">▼</span>
      </div>
      <div class="accordion-content">
        <div class="time-slots">
          <?php if (empty($slot_array)): ?>
            <p>Nav pieejamu laika slotu šai dienas daļai.</p>
          <?php else: ?>
            <?php foreach ($slot_array as $slot): ?>
              <div class="time-slot">
                <a href="?action=show_services&slot=<?php echo urlencode($slot); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>&selected_date=<?php echo urlencode($selected_date); ?>&service_id=<?php echo $selected_service_id; ?>">
                  <?php echo htmlspecialchars($slot); ?>
                </a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>