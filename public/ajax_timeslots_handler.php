<?php
require_once '../middleware.php'; // Handles session start and other middleware
runMiddleware(); // Executes middleware logic (e.g., maintenance check)
require_once '../config/db_connection.php';
require_once '../includes/availability_functions.php'; // Path to your new functions

header('Content-Type: application/json');

$selected_date = $_GET['selected_date'] ?? null;
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

if (!$selected_date || !$service_id) {
    echo json_encode(['error' => 'Missing selected_date or service_id']);
    http_response_code(400); // Bad Request
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $selected_date)) {
    echo json_encode(['error' => 'Invalid date format. Please use YYYY-MM-DD.']);
    http_response_code(400); // Bad Request
    exit;
}

try {
    // Use the refactored function
    $available_timeslots = getAvailableTimeslotsForService($pdo, $selected_date, $service_id);

    // Check if the function returned an error (e.g., service not found, DB error)
    if (isset($available_timeslots['error'])) {
        // Determine appropriate HTTP status code based on error type if possible
        // For simplicity, using 500 for server-side errors, 404 if service not found.
        // The function getAvailableTimeslotsForService would need to be more specific for this.
        // Assuming it returns ['error' => 'Service not found.'] or similar.
        if (strpos(strtolower($available_timeslots['error']), 'not found') !== false) {
            http_response_code(404);
        } else {
            http_response_code(500);
        }
        echo json_encode($available_timeslots);
        exit;
    }

    echo json_encode($available_timeslots);

} catch (Exception $e) {
    // Catch any other unexpected errors
    // Log error: error_log("Unexpected error in ajax_timeslots_handler: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An unexpected error occurred. Please try again later.']);
    exit;
}
?>
