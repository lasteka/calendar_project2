<?php if ($show_services): ?>
<div class="booking-confirmation">
    <h2>Rezervācijas apstiprinājums</h2>
    <div class="booking-summary">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$selected_service_id]);
        $service = $stmt->fetch();
        ?>
        <p><strong>Datums:</strong> <?= date('d.m.Y', strtotime($selected_date)) ?></p>
        <p><strong>Laiks:</strong> <?= htmlspecialchars($selected_slot) ?></p>
        <p><strong>Pakalpojums:</strong> <?= htmlspecialchars($service['name']) ?></p>
        <p><strong>Ilgums:</strong> <?= $service['duration'] ?> minūtes</p>
        <p><strong>Cena:</strong> <?= number_format($service['price'], 2) ?> €</p>
    </div>

    <form method="POST" action="book.php" class="confirmation-form">
        <input type="hidden" name="action" value="book_service">
        <input type="hidden" name="service_id" value="<?= $selected_service_id ?>">
        <input type="hidden" name="booking_date" value="<?= $selected_date ?>">
        <input type="hidden" name="slot" value="<?= htmlspecialchars($selected_slot) ?>">
        
        <div class="form-actions">
            <a href="index.php?selected_date=<?= $selected_date ?>" class="btn cancel">Atcelt</a>
            <button type="submit" class="btn confirm">Apstiprināt</button>
        </div>
    </form>
</div>
<?php endif; ?>