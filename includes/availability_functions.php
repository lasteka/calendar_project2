<?php

/**
 * Checks if there are any available time slots for a given date and service.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $date The date to check (YYYY-MM-DD).
 * @param int $service_id The ID of the service.
 * @return bool True if available slots exist, false otherwise.
 */
function hasAvailableSlotsForService(PDO $pdo, string $date, int $service_id): bool {
    // Prevent processing for past dates
    $today = date('Y-m-d');
    if ($date < $today) {
        return false;
    }

    try {
        // 1. Fetch service duration
        $stmt_service = $pdo->prepare("SELECT duration FROM services WHERE id = ?");
        $stmt_service->execute([$service_id]);
        $service = $stmt_service->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            // Service not found, so no slots available
            return false;
        }
        $duration = $service['duration'];
        $required_slots_count = ceil($duration / 30);

        // 2. Fetch all active timeslots for the given day (not specific to day_part initially)
        // This simplifies as we only need to know if *any* slot sequence exists.
        $stmt_all_slots = $pdo->prepare("SELECT time_slot FROM timeslots WHERE is_active = ? ORDER BY STR_TO_DATE(time_slot, '%H:%i')");
        $stmt_all_slots->execute([1]);
        $all_active_timeslots_for_day = $stmt_all_slots->fetchAll(PDO::FETCH_COLUMN);

        if (empty($all_active_timeslots_for_day)) {
            return false; // No active slots at all
        }
        
        // Create a quick lookup map for active slots
        $active_slots_map = array_flip($all_active_timeslots_for_day);

        // 3. Fetch booked slots for the selected date to create a map of booked intervals
        $stmt_booked = $pdo->prepare("
            SELECT b.time_slot, s.duration 
            FROM bookings b 
            JOIN services s ON b.service_id = s.id 
            WHERE b.booking_date = ?
        ");
        $stmt_booked->execute([$date]);
        $booked_slots_info = $stmt_booked->fetchAll(PDO::FETCH_ASSOC);

        $booked_intervals = []; // Key: 'HH:MM' => true if booked
        foreach ($booked_slots_info as $booked) {
            $start_time = strtotime($booked['time_slot']);
            // Duration in minutes for the booked service
            $booked_service_duration = $booked['duration']; 
            $num_30_min_slots_for_booked_service = ceil($booked_service_duration / 30);

            for ($i = 0; $i < $num_30_min_slots_for_booked_service; $i++) {
                 $current_interval_time_str = date('H:i', $start_time + ($i * 30 * 60));
                 $booked_intervals[$current_interval_time_str] = true;
            }
        }

        // 4. Check for at least one sequence of free slots
        foreach ($all_active_timeslots_for_day as $slot_time_str) {
            $slot_start_time = strtotime($slot_time_str);
            $can_book = true;

            for ($i = 0; $i < $required_slots_count; $i++) {
                $current_check_time_str = date('H:i', $slot_start_time + ($i * 30 * 60));

                // Check if the slot itself is an active timeslot
                if (!isset($active_slots_map[$current_check_time_str])) {
                    $can_book = false;
                    break;
                }
                // Check if the slot is booked
                if (isset($booked_intervals[$current_check_time_str])) {
                    $can_book = false;
                    break;
                }
            }

            if ($can_book) {
                return true; // Found an available sequence
            }
        }

        return false; // No sequence found
    } catch (PDOException $e) {
        // Log error: error_log("Database error in hasAvailableSlotsForService: " . $e->getMessage());
        return false; // On error, assume no availability
    } catch (Exception $e) {
        // Log error: error_log("General error in hasAvailableSlotsForService: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetches available timeslots grouped by day_part for a given date and service.
 * (This is the refactored logic from ajax_timeslots_handler.php)
 *
 * @param PDO $pdo
 * @param string $date
 * @param int $service_id
 * @return array
 */
function getAvailableTimeslotsForService(PDO $pdo, string $date, int $service_id): array {
    $available_timeslots_grouped = ['Morning' => [], 'Day' => [], 'Evening' => []];

    // Prevent processing for past dates
    $today = date('Y-m-d');
    if ($date < $today) {
        // Return empty structure, possibly with a message if the client is set up to handle it
        // For now, just empty slots, which will lead to "no available slots" message.
        return $available_timeslots_grouped; 
    }

    try {
        // 1. Fetch service duration
        $stmt_service = $pdo->prepare("SELECT duration FROM services WHERE id = ?");
        $stmt_service->execute([$service_id]);
        $service = $stmt_service->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            return $available_timeslots_grouped; // Or throw exception
        }
        $duration = $service['duration'];
        $required_slots_count = ceil($duration / 30);

        // 2. Fetch all active timeslots with their day_part
        $stmt_all_slots = $pdo->prepare("
            SELECT id, day_part, time_slot 
            FROM timeslots 
            WHERE is_active = ? 
            ORDER BY STR_TO_DATE(time_slot, '%H:%i')
        ");
        $stmt_all_slots->execute([1]);
        $all_active_timeslots = $stmt_all_slots->fetchAll(PDO::FETCH_ASSOC);

        if (empty($all_active_timeslots)) {
            return $available_timeslots_grouped;
        }
        
        $active_slots_map = []; // For checking existence of subsequent slots
        foreach($all_active_timeslots as $ts){
            $active_slots_map[$ts['time_slot']] = $ts;
        }


        // 3. Fetch booked slots for the selected date
        $stmt_booked = $pdo->prepare("
            SELECT b.time_slot, s.duration 
            FROM bookings b 
            JOIN services s ON b.service_id = s.id 
            WHERE b.booking_date = ?
        ");
        $stmt_booked->execute([$date]);
        $booked_slots_info = $stmt_booked->fetchAll(PDO::FETCH_ASSOC);

        $booked_intervals = [];
        foreach ($booked_slots_info as $booked) {
            $start_time = strtotime($booked['time_slot']);
            $booked_service_duration = $booked['duration'];
            $num_30_min_slots_for_booked_service = ceil($booked_service_duration / 30);
            for ($i = 0; $i < $num_30_min_slots_for_booked_service; $i++) {
                 $current_interval_time_str = date('H:i', $start_time + ($i * 30 * 60));
                 $booked_intervals[$current_interval_time_str] = true;
            }
        }

        // 4. Determine available slots
        foreach ($all_active_timeslots as $slot) {
            $slot_time_str = $slot['time_slot'];
            $slot_start_time = strtotime($slot_time_str);
            $can_book_this_slot = true;

            for ($i = 0; $i < $required_slots_count; $i++) {
                $current_check_time_str = date('H:i', $slot_start_time + ($i * 30 * 60));
                if (!isset($active_slots_map[$current_check_time_str])) { // Check if subsequent slot is active
                    $can_book_this_slot = false;
                    break;
                }
                if (isset($booked_intervals[$current_check_time_str])) { // Check if any part of sequence is booked
                    $can_book_this_slot = false;
                    break;
                }
            }

            if ($can_book_this_slot) {
                $day_part = $slot['day_part'];
                if (!isset($available_timeslots_grouped[$day_part])) {
                    // This case should ideally not happen if day_part in DB is consistent
                    $available_timeslots_grouped[$day_part] = [];
                }
                $available_timeslots_grouped[$day_part][] = [
                    'time' => $slot_time_str,
                    'id' => $slot['id'] 
                ];
            }
        }
        return $available_timeslots_grouped;

    } catch (PDOException $e) {
        // error_log("Database error in getAvailableTimeslotsForService: " . $e->getMessage());
        return ['error' => 'Database error.'];
    } catch (Exception $e) {
        // error_log("General error in getAvailableTimeslotsForService: " . $e->getMessage());
        return ['error' => 'General error.'];
    }
}

?>
