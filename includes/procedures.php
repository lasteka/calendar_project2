<?php if ($show_services): ?>
<div class="container">
    <h2>Izvēlieties Pakalpojumu</h2>
    <form method="POST" action="book.php">
        <input type="hidden" name="action" value="book_service">
        <input type="hidden" name="slot" value="<?= htmlspecialchars($selected_slot); ?>">
        <input type="hidden" name="booking_date" value="<?= htmlspecialchars($selected_date); ?>">
        <input type="hidden" name="month" value="<?= htmlspecialchars($month); ?>">
        <input type="hidden" name="year" value="<?= htmlspecialchars($year); ?>">
        <input type="hidden" name="selected_date" value="<?= htmlspecialchars($selected_date); ?>">
        <div class="form-group">
            <label for="service_id">Pakalpojums:</label>
            <select id="service_id" name="service_id" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id']; ?>" <?php echo $selected_service_id == $service['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($service['name']); ?> (<?= number_format($service['price'], 2); ?> EUR, <?= $service['duration']; ?> min)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Rezervēt</button>
    </form>
</div>
<?php endif; ?>