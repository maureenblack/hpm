/**
 * Holistic Prosperity Ministry Website
 * Events JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize events calendar
    initEventsCalendar();
    
    // Initialize event filters
    initEventFilters();
    
    // Initialize registration form validation
    initFormValidation();
    
    // Initialize accordion functionality
    initAccordions();
    
    // Initialize counter animations for event metrics
    initCounters();
});

/**
 * Initialize the events calendar
 */
function initEventsCalendar() {
    const calendarContainer = document.getElementById('eventsCalendar');
    if (!calendarContainer) return;
    
    const currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    // Event data - in a real application, this would come from a database
    const events = [
        { id: 1, title: 'Annual Prosperity Summit', start: new Date(2025, 5, 15), end: new Date(2025, 5, 17), category: 'conference', featured: true },
        { id: 2, title: 'Faith & Finance Bootcamp', start: new Date(2025, 6, 8), end: new Date(2025, 6, 9), category: 'workshop', featured: true },
        { id: 3, title: 'Community Giveback Day', start: new Date(2025, 7, 22), end: new Date(2025, 7, 22), category: 'community', featured: true },
        { id: 4, title: 'Revival Night', start: new Date(2025, 4, 25), end: new Date(2025, 4, 25), category: 'worship', featured: false },
        { id: 5, title: 'Financial Literacy Workshop', start: new Date(2025, 5, 5), end: new Date(2025, 5, 5), category: 'workshop', featured: false },
        { id: 6, title: 'Prayer & Fasting Retreat', start: new Date(2025, 5, 10), end: new Date(2025, 5, 12), category: 'worship', featured: false },
        { id: 7, title: 'Youth Financial Summit', start: new Date(2025, 5, 20), end: new Date(2025, 5, 20), category: 'workshop', featured: false },
        { id: 8, title: 'Entrepreneurship Seminar', start: new Date(2025, 6, 15), end: new Date(2025, 6, 15), category: 'workshop', featured: false },
        { id: 9, title: 'Women in Business Luncheon', start: new Date(2025, 6, 22), end: new Date(2025, 6, 22), category: 'workshop', featured: false },
        { id: 10, title: 'Revival Night', start: new Date(2025, 6, 30), end: new Date(2025, 6, 30), category: 'worship', featured: false },
        { id: 11, title: 'Community Giveback Day', start: new Date(2025, 8, 15), end: new Date(2025, 8, 15), category: 'community', featured: false },
        { id: 12, title: 'Financial Literacy Workshop', start: new Date(2025, 8, 22), end: new Date(2025, 8, 22), category: 'workshop', featured: false }
    ];
    
    // Regular events - these repeat weekly or monthly
    const regularEvents = [
        { title: 'Sunday Service', day: 0, time: '10:00 AM', category: 'worship' },
        { title: 'Prayer & Meditation Group', day: 1, time: '6:00 AM', category: 'worship' },
        { title: 'Food Pantry Distribution', day: 2, time: '4:00 PM', category: 'community' },
        { title: 'Prosperity Principles Bible Study', day: 3, time: '7:00 PM', category: 'worship' },
        { title: 'Financial Counseling Sessions', day: 4, time: '5:00 PM', category: 'workshop' }
    ];
    
    // Render the calendar
    renderCalendar(currentMonth, currentYear, events, regularEvents);
    
    // Previous month button
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar(currentMonth, currentYear, events, regularEvents);
    });
    
    // Next month button
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar(currentMonth, currentYear, events, regularEvents);
    });
    
    // View toggle buttons
    document.getElementById('calendarViewBtn').addEventListener('click', function() {
        document.getElementById('calendarView').classList.add('active');
        document.getElementById('listView').classList.remove('active');
        document.getElementById('cardView').classList.remove('active');
        document.getElementById('calendarViewBtn').classList.add('active');
        document.getElementById('listViewBtn').classList.remove('active');
        document.getElementById('cardViewBtn').classList.remove('active');
    });
    
    document.getElementById('listViewBtn').addEventListener('click', function() {
        document.getElementById('calendarView').classList.remove('active');
        document.getElementById('listView').classList.add('active');
        document.getElementById('cardView').classList.remove('active');
        document.getElementById('calendarViewBtn').classList.remove('active');
        document.getElementById('listViewBtn').classList.add('active');
        document.getElementById('cardViewBtn').classList.remove('active');
        
        renderListView(events, currentMonth, currentYear);
    });
    
    document.getElementById('cardViewBtn').addEventListener('click', function() {
        document.getElementById('calendarView').classList.remove('active');
        document.getElementById('listView').classList.remove('active');
        document.getElementById('cardView').classList.add('active');
        document.getElementById('calendarViewBtn').classList.remove('active');
        document.getElementById('listViewBtn').classList.remove('active');
        document.getElementById('cardViewBtn').classList.add('active');
        
        renderCardView(events, currentMonth, currentYear);
    });
}

/**
 * Render the calendar for a specific month and year
 */
function renderCalendar(month, year, events, regularEvents) {
    const calendarContainer = document.getElementById('calendarDays');
    const monthYearText = document.querySelector('.current-month');
    
    // Update month/year display
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    monthYearText.textContent = `${monthNames[month]} ${year}`;
    
    // Get the first day of the month
    const firstDay = new Date(year, month, 1).getDay();
    
    // Get the number of days in the month
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Clear previous calendar
    calendarContainer.innerHTML = '';
    
    // Create day headers
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const headerRow = document.createElement('div');
    headerRow.className = 'calendar-row days-header';
    
    dayNames.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-day-header';
        dayHeader.textContent = day;
        headerRow.appendChild(dayHeader);
    });
    
    calendarContainer.appendChild(headerRow);
    
    // Create calendar grid
    let date = 1;
    for (let i = 0; i < 6; i++) {
        // Create a calendar row
        const row = document.createElement('div');
        row.className = 'calendar-row';
        
        // Create 7 days for each row
        for (let j = 0; j < 7; j++) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day';
            
            if (i === 0 && j < firstDay) {
                // Empty cells before the first day of the month
                cell.classList.add('empty');
            } else if (date > daysInMonth) {
                // Empty cells after the last day of the month
                cell.classList.add('empty');
            } else {
                // Regular day cells
                const dayNumber = document.createElement('div');
                dayNumber.className = 'day-number';
                dayNumber.textContent = date;
                cell.appendChild(dayNumber);
                
                // Check if this day has any events
                const dayEvents = events.filter(event => {
                    const eventStart = new Date(event.start);
                    const eventEnd = new Date(event.end);
                    const currentDate = new Date(year, month, date);
                    
                    return currentDate >= eventStart && currentDate <= eventEnd;
                });
                
                // Check for regular events based on day of week
                const dayOfWeek = new Date(year, month, date).getDay();
                const dayRegularEvents = regularEvents.filter(event => event.day === dayOfWeek);
                
                // Add events to the day cell
                if (dayEvents.length > 0 || dayRegularEvents.length > 0) {
                    const eventsContainer = document.createElement('div');
                    eventsContainer.className = 'day-events';
                    
                    // Add special events
                    dayEvents.forEach(event => {
                        const eventEl = document.createElement('div');
                        eventEl.className = `calendar-event ${event.category}`;
                        eventEl.textContent = event.title;
                        eventEl.setAttribute('data-event-id', event.id);
                        eventEl.addEventListener('click', function() {
                            window.location.href = `event-details.html?id=${event.id}`;
                        });
                        eventsContainer.appendChild(eventEl);
                    });
                    
                    // Add regular events
                    dayRegularEvents.forEach(event => {
                        const eventEl = document.createElement('div');
                        eventEl.className = `calendar-event regular ${event.category}`;
                        eventEl.textContent = event.title;
                        eventsContainer.appendChild(eventEl);
                    });
                    
                    cell.appendChild(eventsContainer);
                    cell.classList.add('has-events');
                }
                
                // Highlight today's date
                const today = new Date();
                if (date === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    cell.classList.add('today');
                }
                
                date++;
            }
            
            row.appendChild(cell);
        }
        
        calendarContainer.appendChild(row);
        
        // Stop creating rows if we've used all the days
        if (date > daysInMonth) {
            break;
        }
    }
}

/**
 * Render events in list view
 */
function renderListView(events, month, year) {
    const listContainer = document.getElementById('eventsList');
    listContainer.innerHTML = '';
    
    // Filter events for the current month and year
    const filteredEvents = events.filter(event => {
        const eventDate = new Date(event.start);
        return eventDate.getMonth() === month && eventDate.getFullYear() === year;
    });
    
    // Sort events by date
    filteredEvents.sort((a, b) => a.start - b.start);
    
    if (filteredEvents.length === 0) {
        listContainer.innerHTML = '<div class="alert alert-info">No events scheduled for this month.</div>';
        return;
    }
    
    // Create list items for each event
    filteredEvents.forEach(event => {
        const eventItem = document.createElement('div');
        eventItem.className = 'event-list-item';
        
        const eventDate = new Date(event.start);
        const endDate = new Date(event.end);
        
        let dateDisplay;
        if (eventDate.toDateString() === endDate.toDateString()) {
            dateDisplay = eventDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        } else {
            dateDisplay = `${eventDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
        }
        
        eventItem.innerHTML = `
            <div class="event-list-date">
                <span class="event-day">${eventDate.getDate()}</span>
                <span class="event-month">${eventDate.toLocaleDateString('en-US', { month: 'short' })}</span>
            </div>
            <div class="event-list-content">
                <h4>${event.title}</h4>
                <p class="event-date-time"><i class="fas fa-calendar-alt"></i> ${dateDisplay}</p>
                <span class="event-category ${event.category}">${event.category.charAt(0).toUpperCase() + event.category.slice(1)}</span>
            </div>
            <div class="event-list-action">
                <a href="event-details.html?id=${event.id}" class="btn btn-outline-primary btn-sm">Details</a>
            </div>
        `;
        
        listContainer.appendChild(eventItem);
    });
}

/**
 * Render events in card view
 */
function renderCardView(events, month, year) {
    const cardContainer = document.getElementById('eventsCards');
    cardContainer.innerHTML = '';
    
    // Filter events for the current month and year
    const filteredEvents = events.filter(event => {
        const eventDate = new Date(event.start);
        return eventDate.getMonth() === month && eventDate.getFullYear() === year;
    });
    
    // Sort events by date
    filteredEvents.sort((a, b) => a.start - b.start);
    
    if (filteredEvents.length === 0) {
        cardContainer.innerHTML = '<div class="alert alert-info">No events scheduled for this month.</div>';
        return;
    }
    
    // Create a row for the cards
    const row = document.createElement('div');
    row.className = 'row g-4';
    
    // Create card for each event
    filteredEvents.forEach(event => {
        const eventDate = new Date(event.start);
        const endDate = new Date(event.end);
        
        let dateDisplay;
        if (eventDate.toDateString() === endDate.toDateString()) {
            dateDisplay = eventDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        } else {
            dateDisplay = `${eventDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
        }
        
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6';
        
        col.innerHTML = `
            <div class="event-card h-100">
                <div class="event-date">
                    <span class="month">${eventDate.toLocaleDateString('en-US', { month: 'short' }).toUpperCase()}</span>
                    <span class="day">${eventDate.getDate()}</span>
                </div>
                <div class="card-body">
                    <h3 class="card-title">${event.title}</h3>
                    <div class="event-details">
                        <p><i class="fas fa-calendar-alt"></i> ${dateDisplay}</p>
                        <p><i class="fas fa-tag"></i> ${event.category.charAt(0).toUpperCase() + event.category.slice(1)}</p>
                    </div>
                    <a href="event-details.html?id=${event.id}" class="btn btn-primary">Learn More</a>
                </div>
            </div>
        `;
        
        row.appendChild(col);
    });
    
    cardContainer.appendChild(row);
}

/**
 * Initialize event filters
 */
function initEventFilters() {
    const filterForm = document.getElementById('eventFilterForm');
    if (!filterForm) return;
    
    const searchInput = document.getElementById('eventSearchInput');
    const categorySelect = document.getElementById('eventCategorySelect');
    const dateFromInput = document.getElementById('eventDateFrom');
    const dateToInput = document.getElementById('eventDateTo');
    const filterBtn = document.getElementById('applyFiltersBtn');
    const resetBtn = document.getElementById('resetFiltersBtn');
    
    // Set default date range (current month)
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    dateFromInput.valueAsDate = firstDayOfMonth;
    dateToInput.valueAsDate = lastDayOfMonth;
    
    // Apply filters
    filterBtn.addEventListener('click', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    // Reset filters
    resetBtn.addEventListener('click', function(e) {
        e.preventDefault();
        searchInput.value = '';
        categorySelect.value = 'all';
        dateFromInput.valueAsDate = firstDayOfMonth;
        dateToInput.valueAsDate = lastDayOfMonth;
        applyFilters();
    });
    
    // Search input real-time filtering
    searchInput.addEventListener('input', debounce(function() {
        applyFilters();
    }, 300));
    
    // Category select change
    categorySelect.addEventListener('change', function() {
        applyFilters();
    });
    
    // Date inputs change
    dateFromInput.addEventListener('change', function() {
        applyFilters();
    });
    
    dateToInput.addEventListener('change', function() {
        applyFilters();
    });
    
    // Function to apply filters
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const category = categorySelect.value;
        const dateFrom = dateFromInput.valueAsDate;
        const dateTo = dateToInput.valueAsDate;
        
        // Get all event elements
        const eventCards = document.querySelectorAll('.event-card');
        const eventListItems = document.querySelectorAll('.event-list-item');
        const calendarEvents = document.querySelectorAll('.calendar-event');
        
        // Filter function
        const filterEvent = (element, title, eventCategory, eventDate) => {
            // Search term filter
            const matchesSearch = !searchTerm || title.toLowerCase().includes(searchTerm);
            
            // Category filter
            const matchesCategory = category === 'all' || eventCategory === category;
            
            // Date filter
            const matchesDate = !eventDate || (eventDate >= dateFrom && eventDate <= dateTo);
            
            // Show/hide based on filter results
            if (matchesSearch && matchesCategory && matchesDate) {
                element.style.display = '';
                return true;
            } else {
                element.style.display = 'none';
                return false;
            }
        };
        
        // Apply filters to card view
        let visibleCardCount = 0;
        eventCards.forEach(card => {
            const title = card.querySelector('.event-title').textContent;
            const eventCategory = card.getAttribute('data-category');
            const eventDateStr = card.getAttribute('data-date');
            const eventDate = eventDateStr ? new Date(eventDateStr) : null;
            
            if (filterEvent(card, title, eventCategory, eventDate)) {
                visibleCardCount++;
            }
        });
        
        // Apply filters to list view
        let visibleListCount = 0;
        eventListItems.forEach(item => {
            const title = item.querySelector('.event-list-title').textContent;
            const eventCategory = item.getAttribute('data-category');
            const eventDateStr = item.getAttribute('data-date');
            const eventDate = eventDateStr ? new Date(eventDateStr) : null;
            
            if (filterEvent(item, title, eventCategory, eventDate)) {
                visibleListCount++;
            }
        });
        
        // Apply filters to calendar events
        calendarEvents.forEach(event => {
            const title = event.textContent;
            const eventCategory = event.classList.contains('conference') ? 'conference' :
                                event.classList.contains('workshop') ? 'workshop' :
                                event.classList.contains('community') ? 'community' :
                                event.classList.contains('worship') ? 'worship' : '';
            
            // Calendar events don't need date filtering as they're already positioned by date
            const matchesSearch = !searchTerm || title.toLowerCase().includes(searchTerm);
            const matchesCategory = category === 'all' || eventCategory === category;
            
            event.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
        });
        
        // Update filter results count
        const resultsCountEl = document.getElementById('filterResultsCount');
        if (resultsCountEl) {
            const activeView = document.querySelector('.view-container.active');
            const count = activeView.id === 'cardView' ? visibleCardCount : 
                         activeView.id === 'listView' ? visibleListCount : 
                         'N/A'; // Calendar view doesn't show a simple count
            
            resultsCountEl.textContent = count === 'N/A' ? '' : `Showing ${count} events`;
        }
    }
    
    // Helper function to debounce input events
    function debounce(func, delay) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }
}

/**
 * Initialize registration form validation
 */
function initFormValidation() {
    const registrationForm = document.getElementById('eventRegistrationForm');
    if (!registrationForm) return;
    
    const submitBtn = registrationForm.querySelector('button[type="submit"]');
    const nameInput = document.getElementById('registrationName');
    const emailInput = document.getElementById('registrationEmail');
    const phoneInput = document.getElementById('registrationPhone');
    const eventSelect = document.getElementById('registrationEvent');
    const ticketsInput = document.getElementById('registrationTickets');
    const commentsInput = document.getElementById('registrationComments');
    const termsCheckbox = document.getElementById('registrationTerms');
    
    // Form validation patterns
    const patterns = {
        name: /^[a-zA-Z\s]{2,50}$/,
        email: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/,
        phone: /^[0-9()\-\+\s]{10,15}$/,
        tickets: /^[1-9][0-9]{0,2}$/
    };
    
    // Error messages
    const errorMessages = {
        name: 'Please enter a valid name (2-50 characters, letters only)',
        email: 'Please enter a valid email address',
        phone: 'Please enter a valid phone number (10-15 digits)',
        event: 'Please select an event',
        tickets: 'Please enter a valid number of tickets (1-999)',
        terms: 'You must agree to the terms and conditions'
    };
    
    // Validation function
    function validateInput(input, pattern, errorMessage) {
        const parent = input.parentElement;
        const errorElement = parent.querySelector('.invalid-feedback') || document.createElement('div');
        
        if (!pattern.test(input.value)) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            
            if (!parent.querySelector('.invalid-feedback')) {
                errorElement.className = 'invalid-feedback';
                errorElement.textContent = errorMessage;
                parent.appendChild(errorElement);
            }
            
            return false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            
            if (parent.querySelector('.invalid-feedback')) {
                parent.removeChild(errorElement);
            }
            
            return true;
        }
    }
    
    // Validate select input
    function validateSelect(select, errorMessage) {
        const parent = select.parentElement;
        const errorElement = parent.querySelector('.invalid-feedback') || document.createElement('div');
        
        if (select.value === '') {
            select.classList.add('is-invalid');
            select.classList.remove('is-valid');
            
            if (!parent.querySelector('.invalid-feedback')) {
                errorElement.className = 'invalid-feedback';
                errorElement.textContent = errorMessage;
                parent.appendChild(errorElement);
            }
            
            return false;
        } else {
            select.classList.remove('is-invalid');
            select.classList.add('is-valid');
            
            if (parent.querySelector('.invalid-feedback')) {
                parent.removeChild(errorElement);
            }
            
            return true;
        }
    }
    
    // Validate checkbox
    function validateCheckbox(checkbox, errorMessage) {
        const parent = checkbox.parentElement;
        const errorElement = parent.querySelector('.invalid-feedback') || document.createElement('div');
        
        if (!checkbox.checked) {
            checkbox.classList.add('is-invalid');
            checkbox.classList.remove('is-valid');
            
            if (!parent.querySelector('.invalid-feedback')) {
                errorElement.className = 'invalid-feedback';
                errorElement.textContent = errorMessage;
                parent.appendChild(errorElement);
            }
            
            return false;
        } else {
            checkbox.classList.remove('is-invalid');
            checkbox.classList.add('is-valid');
            
            if (parent.querySelector('.invalid-feedback')) {
                parent.removeChild(errorElement);
            }
            
            return true;
        }
    }
    
    // Input event listeners for real-time validation
    nameInput.addEventListener('input', function() {
        validateInput(this, patterns.name, errorMessages.name);
    });
    
    emailInput.addEventListener('input', function() {
        validateInput(this, patterns.email, errorMessages.email);
    });
    
    phoneInput.addEventListener('input', function() {
        validateInput(this, patterns.phone, errorMessages.phone);
    });
    
    eventSelect.addEventListener('change', function() {
        validateSelect(this, errorMessages.event);
    });
    
    ticketsInput.addEventListener('input', function() {
        validateInput(this, patterns.tickets, errorMessages.tickets);
    });
    
    termsCheckbox.addEventListener('change', function() {
        validateCheckbox(this, errorMessages.terms);
    });
    
    // Form submission
    registrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all inputs
        const isNameValid = validateInput(nameInput, patterns.name, errorMessages.name);
        const isEmailValid = validateInput(emailInput, patterns.email, errorMessages.email);
        const isPhoneValid = validateInput(phoneInput, patterns.phone, errorMessages.phone);
        const isEventValid = validateSelect(eventSelect, errorMessages.event);
        const isTicketsValid = validateInput(ticketsInput, patterns.tickets, errorMessages.tickets);
        const isTermsValid = validateCheckbox(termsCheckbox, errorMessages.terms);
        
        // If all inputs are valid, show success message
        if (isNameValid && isEmailValid && isPhoneValid && isEventValid && isTicketsValid && isTermsValid) {
            // Show registration confirmation modal
            const confirmationModal = new bootstrap.Modal(document.getElementById('registrationConfirmationModal'));
            confirmationModal.show();
            
            // Reset form
            registrationForm.reset();
            const validInputs = registrationForm.querySelectorAll('.is-valid');
            validInputs.forEach(input => input.classList.remove('is-valid'));
        }
    });
}

/**
 * Initialize counter animations for event metrics
 */
function initCounters() {
    const counters = document.querySelectorAll('.counter-value');
    if (counters.length === 0) return;
    
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                let count = 0;
                const updateCounter = () => {
                    const increment = target / 100;
                    if (count < target) {
                        count += increment;
                        counter.textContent = Math.ceil(count);
                        setTimeout(updateCounter, 10);
                    } else {
                        counter.textContent = target;
                    }
                };
                updateCounter();
                observer.unobserve(counter);
            }
        });
    }, options);
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}
