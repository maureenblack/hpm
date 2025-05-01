/**
 * Holistic Prosperity Ministry Website
 * Donation Page JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all donation page features
    initDonationForm();
    initAllocationChart();
    initMemorialFields();
    initCounterAnimation();
});

/**
 * Initialize the donation form functionality
 */
function initDonationForm() {
    // Amount selection
    const amountOptions = document.querySelectorAll('.amount-option');
    const customAmountInput = document.getElementById('customAmount');
    
    // Add click event to amount options
    amountOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            amountOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Clear custom amount input
            customAmountInput.value = '';
            
            // Update donation summary
            updateDonationSummary();
        });
    });
    
    // Handle custom amount input
    customAmountInput.addEventListener('input', function() {
        // Remove active class from all preset options
        amountOptions.forEach(opt => opt.classList.remove('active'));
        
        // Format input to allow only numbers and decimals
        this.value = this.value.replace(/[^0-9.]/g, '');
        
        // Update donation summary
        updateDonationSummary();
    });
    
    // Frequency selection
    const frequencyOptions = document.querySelectorAll('.frequency-option');
    
    frequencyOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            frequencyOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Update donation summary
            updateDonationSummary();
        });
    });
    
    // Designation selection
    const designationSelect = document.getElementById('designation');
    
    designationSelect.addEventListener('change', function() {
        // Update donation summary
        updateDonationSummary();
    });
    
    // Form validation
    const form = document.getElementById('donationForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            
            // Show validation messages
            form.classList.add('was-validated');
        } else {
            event.preventDefault();
            
            // Show success message (in a real implementation, this would submit to payment processor)
            showDonationSuccess();
        }
    });
}

/**
 * Initialize the memorial/honor fields functionality
 */
function initMemorialFields() {
    const memorialCheckbox = document.getElementById('memorialGift');
    const memorialFields = document.getElementById('memorialFields');
    const notificationCheckbox = document.getElementById('sendNotification');
    const notificationFields = document.getElementById('notificationFields');
    
    // Toggle memorial fields visibility
    memorialCheckbox.addEventListener('change', function() {
        if (this.checked) {
            memorialFields.classList.remove('d-none');
        } else {
            memorialFields.classList.add('d-none');
            notificationFields.classList.add('d-none');
            notificationCheckbox.checked = false;
        }
    });
    
    // Toggle notification fields visibility
    notificationCheckbox.addEventListener('change', function() {
        if (this.checked) {
            notificationFields.classList.remove('d-none');
        } else {
            notificationFields.classList.add('d-none');
        }
    });
}

/**
 * Update the donation summary based on user selections
 */
function updateDonationSummary() {
    // In a real implementation, this would update a summary section
    // showing the selected amount, frequency, and designation
    
    // Get selected amount
    let amount = 0;
    const activeAmountOption = document.querySelector('.amount-option.active');
    const customAmountInput = document.getElementById('customAmount');
    
    if (activeAmountOption) {
        amount = activeAmountOption.getAttribute('data-amount');
    } else if (customAmountInput.value) {
        amount = parseFloat(customAmountInput.value);
    }
    
    // Get selected frequency
    const activeFrequencyOption = document.querySelector('.frequency-option.active');
    const frequency = activeFrequencyOption ? activeFrequencyOption.getAttribute('data-frequency') : 'one-time';
    
    // Get selected designation
    const designationSelect = document.getElementById('designation');
    const designation = designationSelect.options[designationSelect.selectedIndex] ? 
                       designationSelect.options[designationSelect.selectedIndex].text : '';
    
    console.log(`Donation Summary: $${amount} ${frequency} to ${designation}`);
    
    // This would update a visible summary section in a real implementation
}

/**
 * Show donation success message
 */
function showDonationSuccess() {
    // Create success message
    const formContainer = document.querySelector('.donation-form-container');
    const successMessage = document.createElement('div');
    
    successMessage.className = 'alert alert-success text-center p-4';
    successMessage.innerHTML = `
        <i class="fas fa-check-circle fa-3x mb-3"></i>
        <h3>Thank You for Your Generosity!</h3>
        <p class="mb-4">Your donation will help transform lives through biblical prosperity principles and community impact.</p>
        <p>A confirmation email has been sent to your inbox with the details of your donation.</p>
        <div class="mt-4">
            <a href="index.html" class="btn btn-outline-primary me-2">Return to Homepage</a>
            <a href="#" class="btn btn-primary" onclick="location.reload()">Make Another Donation</a>
        </div>
    `;
    
    // Replace form with success message
    formContainer.innerHTML = '';
    formContainer.appendChild(successMessage);
    
    // Scroll to success message
    successMessage.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Initialize the fund allocation chart
 */
function initAllocationChart() {
    const ctx = document.getElementById('allocationChart');
    
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['CrypStock Academy', 'Community Outreach', 'Worship & Ministry', 'Operations', 'Future Growth'],
            datasets: [{
                data: [40, 25, 20, 10, 5],
                backgroundColor: [
                    '#4B0082', // Royal Purple
                    '#FFD700', // Gold
                    '#9370DB', // Medium Purple
                    '#20B2AA', // Light Sea Green
                    '#87CEEB'  // Sky Blue
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize counter animation for impact statistics
 */
function initCounterAnimation() {
    const counters = document.querySelectorAll('.counter');
    
    // Intersection Observer to trigger counter animation when in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-count'));
                let count = 0;
                const duration = 2000; // 2 seconds
                const interval = duration / target;
                
                const timer = setInterval(() => {
                    count++;
                    counter.innerText = count < target ? count : target + '+';
                    
                    if (count >= target) {
                        clearInterval(timer);
                    }
                }, interval);
                
                // Unobserve after animation starts
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });
    
    // Observe all counter elements
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Handle form field validation in real-time
 */
function validateFormField(field) {
    if (field.checkValidity()) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
}

// Add real-time validation to required fields
document.querySelectorAll('form input[required], form select[required]').forEach(field => {
    field.addEventListener('blur', function() {
        validateFormField(this);
    });
    
    field.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateFormField(this);
        }
    });
});
