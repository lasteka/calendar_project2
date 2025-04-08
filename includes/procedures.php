<?php if ($showServices && $selectedSlot): ?>
    <div class="procedures">
        <h2>Pakalpojumi pieejami laika slotam: <?php echo htmlspecialchars($selectedSlot); ?></h2>
        <?php foreach ($services as $srv): ?>
            <div class="procedure">
                <p class="procedure-name">
                    <a href="?action=book_service&service_id=<?php echo $srv['id']; ?>&slot=<?php echo urlencode($selectedSlot); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>&selected_date=<?php echo $selectedDate; ?>">
                        <?php echo htmlspecialchars($srv['name']); ?>
                    </a>
                </p>
                <p class="procedure-price">Cena: <?php echo htmlspecialchars($srv['price']); ?> €</p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['action']) && $_GET['action'] === 'book_service' && isset($_GET['service_id']) && isset($_GET['slot'])): 
    $serviceId = (int)$_GET['service_id'];
    $selectedSlot = $_GET['slot'];
    $serviceQuery = "SELECT * FROM services WHERE id = ?";
    $stmt = $conn->prepare($serviceQuery);
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $serviceResult = $stmt->get_result();
    $service = $serviceResult->fetch_assoc();
    $serviceResult->free();
?>
    <div class="procedures">
        <h2>Reģistrācija pakalpojumam: <?php echo htmlspecialchars($service['name']); ?>, laiks: <?php echo htmlspecialchars($selectedSlot); ?>, datums: <?php echo htmlspecialchars($selectedDate); ?></h2>
        <?php if ($bookingMessage): ?>
            <p><?php echo htmlspecialchars($bookingMessage); ?></p>
        <?php else: ?>
            <form action="?selected_date=<?php echo urlencode($selectedDate); ?>" method="post">
                <input type="hidden" name="action" value="book_service">
                <input type="hidden" name="service_id" value="<?php echo $serviceId; ?>">
                <input type="hidden" name="slot" value="<?php echo htmlspecialchars($selectedSlot); ?>">
                <input type="hidden" name="booking_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                <label for="user_name">Vārds:</label><br>
                <input type="text" id="user_name" name="user_name" required><br><br>
                <label for="phone">Telefona numurs:</label><br>
                <input type="text" id="phone" name="phone" required><br><br>
                <button type="submit">Apstiprināt rezervāciju</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>