// Debounce function
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const calendarContainerId = 'calendar-container-ajax';
    let calendarContainer = document.getElementById(calendarContainerId);
    let currentFetchCalendarController = null; // For AbortController
    let currentFetchTimeslotsController = null; // For AbortController

    // Function to initialize or re-initialize event listeners
    function initializeCalendarEventListeners() {
        calendarContainer = document.getElementById(calendarContainerId);
        if (!calendarContainer) {
            console.error('Calendar container not found.');
            return;
        }

        // Use event delegation
        calendarContainer.addEventListener('click', async (event) => {
            const target = event.target.closest('a'); // Find the closest anchor tag
            if (!target) return;

            event.preventDefault();

            let currentSelectedDate = calendarContainer.dataset.selectedDate;
            let currentMonth = parseInt(calendarContainer.dataset.currentMonth, 10);
            let currentYear = parseInt(calendarContainer.dataset.currentYear, 10);
            const selectedServiceId = getSelectedServiceId(); // Get service_id for all interactions

            if (target.classList.contains('ajax-nav-link')) {
                // Debounce the action triggered by nav link click
                // The actual fetchCalendar call is already async, debouncing the direct call.
                // If fetchCalendar itself were being called multiple times rapidly outside of this event,
                // we might debounce fetchCalendar. Here, we debounce the handler logic.
                debouncedMonthNavigationHandler(target, currentSelectedDate, selectedServiceId);

            } else if (target.classList.contains('ajax-date-link')) {
                const newSelectedDate = target.dataset.date;
                // Date selection is less critical for debounce but can be added for consistency if needed.
                // For now, let's assume direct action is fine.
                // console.log('Selected date:', newSelectedDate); // Removed for Observation 3
                if(document.getElementById('selected_date_display')) { // Check if element exists
                    document.getElementById('selected_date_display').textContent = newSelectedDate;
                }
                
                // When a date is clicked, re-render calendar with the new selected date
                // and pass current service_id to highlight availability.
                // No debounce here as it's a singular action, but fetchCalendar itself will handle AbortController
                await fetchCalendar(currentMonth, currentYear, newSelectedDate, selectedServiceId);

                // After calendar updates (which now includes new selected date), fetch timeslots.
                if (newSelectedDate && selectedServiceId) { // Ensure serviceId is still valid
                    // fetchTimeslots will handle its own AbortController
                    fetchTimeslots(newSelectedDate, selectedServiceId);
                } else if (!selectedServiceId) { // If no service selected, clear timeslots
                    clearTimeslotsDisplay("Please select a service to see available times.");
                } else { // Potentially newSelectedDate is null/invalid - though less likely here
                     clearTimeslotsDisplay("Please select a date and service.");
                }
            }
        });
    }

    function getSelectedServiceId() {
        const selectedServiceInput = document.querySelector('input[name="service_id"]:checked');
        return selectedServiceInput ? selectedServiceInput.value : null;
    }

    function clearTimeslotsDisplay(message = '') {
        const timeslotsContainer = document.getElementById('timeslots-ajax-container');
        if (timeslotsContainer) {
            timeslotsContainer.innerHTML = message ? `<p>${message}</p>` : '';
             // Also clear the heading or update it
            const timeslotsHeading = timeslotsContainer.querySelector('h3');
            if (timeslotsHeading) {
                timeslotsHeading.innerHTML = message ? '' : 'Available Time Slots'; // Reset or clear
            }
        }
    }

    async function fetchTimeslots(selectedDate, serviceId) {
        const timeslotsContainer = document.getElementById('timeslots-ajax-container');
        if (!timeslotsContainer) {
            console.error('Timeslots container not found.');
            return;
        }
        
        // Abort previous fetchTimeslots if any
        if (currentFetchTimeslotsController) {
            currentFetchTimeslotsController.abort();
        }
        currentFetchTimeslotsController = new AbortController();
        const signal = currentFetchTimeslotsController.signal;

        // Update heading for the timeslots section
        const timeslotsHeading = timeslotsContainer.querySelector('h3');
        if (timeslotsHeading) {
            timeslotsHeading.textContent = `Available Time Slots for ${selectedDate}`;
        }

        const url = `ajax_timeslots_handler.php?selected_date=${encodeURIComponent(selectedDate)}&service_id=${encodeURIComponent(serviceId)}`;

        try {
            const response = await fetch(url, { signal });
            if (!response.ok) {
                if (response.name === 'AbortError') {
                    console.log('Fetch timeslots aborted');
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json(); // Expecting JSON
            currentFetchTimeslotsController = null; // Clear controller once fetch is successful

            // Clear previous timeslots
            // Keep the h3, clear the rest.
            let contentHTML = timeslotsHeading ? timeslotsHeading.outerHTML : `<h3>Available Time Slots for ${selectedDate}</h3>`;


            if (data.error) {
                contentHTML += `<p>Error: ${data.error}</p>`;
            } else if (Object.keys(data).every(dayPart => data[dayPart].length === 0)) {
                contentHTML += '<p>No available time slots for this date and service.</p>';
            } else {
                for (const dayPart in data) {
                    if (data[dayPart].length > 0) {
                        contentHTML += `<div class="day-part"><h4>${dayPart}</h4><ul class="time-slots-list">`;
                        data[dayPart].forEach(slot => {
                            // Construct the booking link carefully, matching public/index.php structure
                            // index.php?action=show_services&slot=TIME&selected_date=DATE&service_id=SERVICE_ID&month=MONTH&year=YEAR
                            // We need month and year of the selectedDate for the link to maintain calendar context if user navigates away and back.
                            const dateObj = new Date(selectedDate + 'T00:00:00'); // Ensure parsing as local date
                            const month = dateObj.getMonth() + 1;
                            const year = dateObj.getFullYear();

                            const link = `index.php?action=show_services&slot=${encodeURIComponent(slot.time)}&selected_date=${encodeURIComponent(selectedDate)}&service_id=${encodeURIComponent(serviceId)}&month=${month}&year=${year}`;
                            contentHTML += `<li><a href="${link}" class="time-slot-link">${slot.time}</a></li>`;
                        });
                        contentHTML += `</ul></div>`;
                    }
                }
            }
            timeslotsContainer.innerHTML = contentHTML;

        } catch (error) {
            console.error('Error fetching timeslots:', error);
            if (error.name === 'AbortError') {
                console.log('Fetch timeslots aborted successfully during error handling.');
            } else if (timeslotsContainer) {
                 let errorHTML = timeslotsHeading ? timeslotsHeading.outerHTML : `<h3>Available Time Slots for ${selectedDate}</h3>`;
                errorHTML += '<p>Could not load timeslots. Please try again later.</p>';
                timeslotsContainer.innerHTML = errorHTML;
            }
        } finally {
            // If the fetch was not aborted by a new request, clear the controller
            if (currentFetchTimeslotsController && !currentFetchTimeslotsController.signal.aborted) {
                currentFetchTimeslotsController = null;
            }
        }
    }
    
    // Debounced handler for month navigation
    const debouncedMonthNavigationHandler = debounce(async (target, currentSelectedDate, selectedServiceId) => {
        const month = target.dataset.month;
        const year = target.dataset.year;
        await fetchCalendar(month, year, currentSelectedDate, selectedServiceId);
    }, 300);


    async function fetchCalendar(month, year, selectedDate, serviceId) {
        if (calendarContainer) {
             calendarContainer.dataset.selectedDate = selectedDate; // Update selected date state
        }

        if (!calendarContainer) {
            console.error('Calendar container not found for fetching.');
            return;
        }

        // Abort previous fetchCalendar if any
        if (currentFetchCalendarController) {
            currentFetchCalendarController.abort();
        }
        currentFetchCalendarController = new AbortController();
        const signal = currentFetchCalendarController.signal;

        let url = `ajax_calendar_handler.php?month=${month}&year=${year}`;
        if (selectedDate) {
            url += `&selected_date=${encodeURIComponent(selectedDate)}`;
        }
        if (serviceId) { // Add service_id to the request
            url += `&service_id=${encodeURIComponent(serviceId)}`;
        }

        try {
            const response = await fetch(url, { signal });
            if (!response.ok) {
                 if (response.name === 'AbortError') {
                    console.log('Fetch calendar aborted');
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const html = await response.text();
            currentFetchCalendarController = null; // Clear controller once fetch is successful

            if (calendarContainer) {
                calendarContainer.innerHTML = html;
                // Data attributes (currentMonth, currentYear, selectedDate) are updated via the new HTML
            }
        } catch (error) {
            console.error('Error fetching calendar:', error);
             if (error.name === 'AbortError') {
                console.log('Fetch calendar aborted successfully during error handling.');
            }
        } finally {
             // If the fetch was not aborted by a new request, clear the controller
            if (currentFetchCalendarController && !currentFetchCalendarController.signal.aborted) {
                currentFetchCalendarController = null;
            }
        }
    }

    // Debounced handler for service changes
    const handleServiceChange = async (event) => {
        if (event.target.name === 'service_id') {
            const newServiceId = event.target.value;
            const currentSelectedDate = calendarContainer ? calendarContainer.dataset.selectedDate : null; // Use null if no date
            const currentMonth = calendarContainer ? parseInt(calendarContainer.dataset.currentMonth, 10) : new Date().getMonth() + 1;
            const currentYear = calendarContainer ? parseInt(calendarContainer.dataset.currentYear, 10) : new Date().getFullYear();

            // Re-fetch calendar to update availability highlights
            await fetchCalendar(currentMonth, currentYear, currentSelectedDate, newServiceId);
            
            // Then fetch timeslots for the selected date and new service
            if (currentSelectedDate && newServiceId) {
                fetchTimeslots(currentSelectedDate, newServiceId);
            } else if (!currentSelectedDate && newServiceId) { // Service selected, but no date yet
                clearTimeslotsDisplay("Please select a date to see available times for this service.");
            } else { // No new service ID or no date selected
                clearTimeslotsDisplay("Please select a service and a date to see available times.");
            }
        }
    };

    // Initial setup
    if (calendarContainer) {
        initializeCalendarEventListeners(); // Sets up click listeners on calendar
        
        let initialSelectedDate = calendarContainer.dataset.selectedDate;
        let initialMonth = parseInt(calendarContainer.dataset.currentMonth, 10);
        let initialYear = parseInt(calendarContainer.dataset.currentYear, 10);
        const initialServiceId = getSelectedServiceId();

        if (document.getElementById('selected_date_display') && initialSelectedDate) {
             document.getElementById('selected_date_display').textContent = initialSelectedDate;
        }

        // On initial load, if a service is selected, refresh calendar to show availability
        if (initialServiceId) {
            fetchCalendar(initialMonth, initialYear, initialSelectedDate, initialServiceId).then(() => {
                // After calendar is updated with availability, fetch timeslots if date is also selected
                if (initialSelectedDate) {
                    const timeslotsContainer = document.getElementById('timeslots-ajax-container');
                    const isEmpty = !timeslotsContainer || !timeslotsContainer.querySelector('.day-part');
                    if (isEmpty) {
                         fetchTimeslots(initialSelectedDate, initialServiceId);
                    }
                } else {
                    clearTimeslotsDisplay("Please select a date to see available times.");
                }
            });
        } else {
            // No service selected on load
             clearTimeslotsDisplay("Please select a service to see available times.");
             // Calendar will show default state (no highlights)
        }

    } else {
        console.log('Calendar container not found on initial load.');
    }

    // Event listener for service selection changes
    const serviceSelectionForm = document.querySelector('.service-selection form');
    if (serviceSelectionForm) {
        // Apply debounce to the event handler for service changes
        serviceSelectionForm.addEventListener('change', debounce(handleServiceChange, 300));
    }
});
