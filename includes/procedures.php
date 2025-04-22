<?php if ($showServices): ?>
<div class="container">
    <h2>Izvēlieties Pakalpojumu</h2>
    <form method="POST" action="index.php?selected_date=<?= urlencode($selectedDate); ?>">
        <input type="hidden" name="action" value="book_service">
        <input type="hidden" name="slot" value="<?= htmlspecialchars($selectedSlot); ?>">
        <input type="hidden" name="booking_date" value="<?= htmlspecialchars($selectedDate); ?>">
        <div class="form-group">
            <label for="service_id">Pakalpojums:</label>
            <select id="service_id" name="service_id" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id']; ?>">
                        <?= htmlspecialchars($service['name']); ?> (<?= number_format($service['price'], 2); ?> EUR, <?= $service['duration']; ?> min)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Rezervēt</button>
    </form>
</div>
<?php endif; ?>