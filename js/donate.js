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
    initPaymentMethods();
    
    // Stripe initialization removed - now using direct payment links
    
    // Test data functionality removed
});

/**
 * Initialize the donation form functionality
 */
function initDonationForm() {
    // Amount selection
    const amountOptions = document.querySelectorAll('.amount-option');
    const customAmountInput = document.getElementById('customAmount');
    
    // Initialize anonymous donation checkbox
    initAnonymousDonation();
    
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
        // Remove active class from all amount options
        amountOptions.forEach(opt => opt.classList.remove('active'));
        
        // Update donation summary
        updateDonationSummary();
    });
    
    // Handle donation frequency selection
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
    
    // Handle designation selection
    const designationSelect = document.getElementById('designation');
    if (designationSelect) {
        designationSelect.addEventListener('change', updateDonationSummary);
    }
    
    // Handle tribute checkbox
    const tributeCheckbox = document.getElementById('is_tribute');
    const tributeFields = document.getElementById('tribute-fields');
    
    if (tributeCheckbox && tributeFields) {
        tributeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                tributeFields.style.display = 'block';
            } else {
                tributeFields.style.display = 'none';
            }
            
            // Update donation summary
            updateDonationSummary();
        });
    }
    
    // Handle tribute type selection
    const tributeTypeRadios = document.querySelectorAll('input[name="tribute_type"]');
    tributeTypeRadios.forEach(radio => {
        radio.addEventListener('change', updateDonationSummary);
    });
    
    // Handle tribute name input
    const tributeNameInput = document.getElementById('tribute_name');
    if (tributeNameInput) {
        tributeNameInput.addEventListener('input', updateDonationSummary);
    }
    
    // Form submission
    const donationForm = document.getElementById('donationForm');
    if (donationForm) {
        donationForm.addEventListener('submit', function(event) {
            // Validate form
            if (!validateForm(this)) {
                event.preventDefault();
                return false;
            }
            
            // Form is valid, continue with submission
            return true;
        });
    }
}

/**
 * Initialize the memorial/honor fields functionality
 */
function initMemorialFields() {
    const tributeCheckbox = document.getElementById('is_tribute');
    const tributeFields = document.getElementById('tribute-fields');
    const tributeTypeRadios = document.querySelectorAll('input[name="tribute_type"]');
    const honorLabel = document.getElementById('honor-label');
    const memoryLabel = document.getElementById('memory-label');
    
    // Hide tribute fields initially
    if (tributeFields) {
        tributeFields.style.display = 'none';
    }
    
    // Handle tribute type changes
    tributeTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'honor') {
                honorLabel.style.display = 'inline';
                memoryLabel.style.display = 'none';
            } else {
                honorLabel.style.display = 'none';
                memoryLabel.style.display = 'inline';
            }
        });
    });
}

/**
 * Update the donation summary based on user selections
 */
function updateDonationSummary() {
    // Get summary elements
    const summaryAmount = document.getElementById('summary-amount');
    const summaryFrequency = document.getElementById('summary-frequency');
    const summaryDesignation = document.getElementById('summary-designation');
    const summaryTributeRow = document.getElementById('summary-tribute-row');
    const summaryTributeType = document.getElementById('summary-tribute-type');
    const summaryTributeName = document.getElementById('summary-tribute-name');
    
    // Get selected values
    let amount = 0;
    const activeAmountOption = document.querySelector('.amount-option.active');
    const customAmountInput = document.getElementById('customAmount');
    
    if (activeAmountOption) {
        amount = parseFloat(activeAmountOption.getAttribute('data-amount'));
    } else if (customAmountInput && customAmountInput.value) {
        amount = parseFloat(customAmountInput.value);
    }
    
    // Update amount in summary
    if (summaryAmount) {
        summaryAmount.textContent = amount > 0 ? '$' + amount.toFixed(2) : 'Not selected';
    }
    
    // Update frequency in summary
    const activeFrequency = document.querySelector('.frequency-option.active');
    if (summaryFrequency && activeFrequency) {
        const frequencyText = activeFrequency.getAttribute('data-frequency') === 'one-time' ? 'One-time' : 'Monthly';
        summaryFrequency.textContent = frequencyText;
    }
    
    // Update designation in summary
    const designationSelect = document.getElementById('designation');
    if (summaryDesignation && designationSelect) {
        const selectedOption = designationSelect.options[designationSelect.selectedIndex];
        summaryDesignation.textContent = selectedOption.text;
    }
    
    // Update tribute information in summary
    const isTributeCheckbox = document.getElementById('is_tribute');
    if (summaryTributeRow && isTributeCheckbox) {
        if (isTributeCheckbox.checked) {
            const tributeType = document.querySelector('input[name="tribute_type"]:checked');
            const tributeName = document.getElementById('tribute_name');
            
            if (tributeType && tributeName) {
                summaryTributeRow.style.display = 'table-row';
                summaryTributeType.textContent = tributeType.value === 'honor' ? 'In Honor Of:' : 'In Memory Of:';
                summaryTributeName.textContent = tributeName.value || 'Not specified';
            }
        } else {
            summaryTributeRow.style.display = 'none';
        }
    }
}

/**
 * Show donation success message
 */
function showDonationSuccess(data) {
    // Hide donation form
    const donationForm = document.getElementById('donation-form-container');
    if (donationForm) {
        donationForm.style.display = 'none';
    }
    
    // Show success message
    const successMessage = document.getElementById('donation-success');
    if (successMessage) {
        successMessage.style.display = 'block';
        
        // Update success message with donation details
        const donorName = document.getElementById('success-donor-name');
        const donationAmount = document.getElementById('success-amount');
        const donationId = document.getElementById('success-donation-id');
        
        if (donorName && data.donor_name) {
            donorName.textContent = data.donor_name;
        }
        
        if (donationAmount && data.amount) {
            donationAmount.textContent = '$' + parseFloat(data.amount).toFixed(2);
        }
        
        if (donationId && data.donation_id) {
            donationId.textContent = data.donation_id;
        }
        
        // Scroll to success message
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Initialize the fund allocation chart
 */
function initAllocationChart() {
    const ctx = document.getElementById('allocationChart');
    
    if (!ctx) {
        return;
    }
    
    // Chart data
    const data = {
        labels: [
            'CrypStock Academy',
            'Community Outreach',
            'Worship & Ministry',
            'Operations',
            'Future Growth'
        ],
        datasets: [{
            data: [40, 25, 20, 10, 5],
            backgroundColor: [
                '#4B0082', // Royal Purple
                '#FFD700', // Gold
                '#9370DB', // Medium Purple
                '#20B2AA', // Light Sea Green
                '#87CEEB'  // Sky Blue
            ],
            borderWidth: 0
        }]
    };
    
    // Chart options
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.raw + '%';
                    }
                }
            }
        }
    };
    
    // Create chart
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: options
    });
}

/**
 * Initialize counter animation for impact statistics
 */
function initCounterAnimation() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // Animation speed - lower is faster
    
    // Intersection Observer to start animation when counters are in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                let count = 0;
                
                const updateCount = () => {
                    const increment = target / speed;
                    
                    if (count < target) {
                        count += increment;
                        counter.innerText = Math.ceil(count).toLocaleString();
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                };
                
                updateCount();
                
                // Unobserve after animation starts
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });
    
    // Observe all counters
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Initialize payment method selection
 */
function initPaymentMethods() {
    // Payment method cards
    const paymentCards = document.querySelectorAll('.payment-method-card');
    
    paymentCards.forEach(card => {
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
    
    // Mobile Money button - direct redirect
    const mobileMoneyBtn = document.getElementById('mobile-money-btn');
    if (mobileMoneyBtn) {
        mobileMoneyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'mobile-money-instructions.php';
        });
    }
}

/**
 * Payment system has been simplified to use direct Stripe payment links
 * All Stripe-related code has been removed as it's no longer needed
 * 
 * One-Time Donation: https://buy.stripe.com/eVa6p68qadtrg4EeUU
 * Monthly Recurring: https://buy.stripe.com/bIYeVCgWGexvbOo3cd
 * Subscription Management: https://billing.stripe.com/p/login/fZeg2RfMacaQagU288
 */

/**
 * Validate a form field and show appropriate feedback
 */
function validateField(field) {
    // Get field name for error messages
    const fieldName = field.dataset.name || field.name || 'This field';
    
    // Check if field is required and empty
    if (field.required && !field.value.trim()) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        // Set custom validation message
        const invalidFeedback = field.parentNode.querySelector('.invalid-feedback');
        if (invalidFeedback) {
            invalidFeedback.textContent = `${fieldName} is required.`;
        }
        
        return false;
    }
    
    // Check email format
    if (field.type === 'email' && field.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            
            // Set custom validation message
            const invalidFeedback = field.parentNode.querySelector('.invalid-feedback');
            if (invalidFeedback) {
                invalidFeedback.textContent = 'Please enter a valid email address.';
            }
            
            return false;
        }
    }
    
    // Check number fields
    if (field.type === 'number' && field.value) {
        const value = parseFloat(field.value);
        
        // Check min attribute
        if (field.min && value < parseFloat(field.min)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            
            // Set custom validation message
            const invalidFeedback = field.parentNode.querySelector('.invalid-feedback');
            if (invalidFeedback) {
                invalidFeedback.textContent = `${fieldName} must be at least ${field.min}.`;
            }
            
            return false;
        }
        
        // Check max attribute
        if (field.max && value > parseFloat(field.max)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            
            // Set custom validation message
            const invalidFeedback = field.parentNode.querySelector('.invalid-feedback');
            if (invalidFeedback) {
                invalidFeedback.textContent = `${fieldName} must be at most ${field.max}.`;
            }
            
            return false;
        }
    }
    
    // Field is valid
    field.classList.add('is-valid');
    field.classList.remove('is-invalid');
    return true;
}

/**
 * Check if an element is visible (not hidden by CSS)
 */
function isElementVisible(element) {
    if (!element) return false;
    
    // Check if element or any parent has display: none
    let currentElement = element;
    while (currentElement) {
        const style = window.getComputedStyle(currentElement);
        if (style.display === 'none') {
            return false;
        }
        
        // Move up to parent element
        currentElement = currentElement.parentElement;
    }
    
    return true;
}

// Add real-time validation to required fields
document.querySelectorAll('form input[required], form select[required]').forEach(field => {
    // Add data-name attribute if not present
    if (!field.hasAttribute('data-name')) {
        field.dataset.name = field.name.replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    // Add validation on blur
    field.addEventListener('blur', function() {
        validateField(this);
    });
    
    // Add validation on input (for immediate feedback)
    field.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateField(this);
        }
    });
});

/**
 * Initialize the anonymous donation functionality
 */
function initAnonymousDonation() {
    const anonymousCheckbox = document.getElementById('donateAnonymously');
    const personalInfoFields = document.getElementById('personalInfoFields');
    
    if (anonymousCheckbox && personalInfoFields) {
        anonymousCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Hide personal info fields
                personalInfoFields.style.display = 'none';
                
                // Make fields not required
                personalInfoFields.querySelectorAll('input[required]').forEach(input => {
                    input.required = false;
                    input.dataset.wasRequired = 'true';
                });
            } else {
                // Show personal info fields
                personalInfoFields.style.display = 'block';
                
                // Restore required attribute
                personalInfoFields.querySelectorAll('input[data-was-required="true"]').forEach(input => {
                    input.required = true;
                });
            }
        });
    }
}

/**
 * Validate the entire form
 */
function validateForm(form) {
    let isValid = true;
    
    // Validate all visible required fields
    form.querySelectorAll('input[required], select[required]').forEach(field => {
        // Only validate visible fields
        if (isElementVisible(field)) {
            if (!validateField(field)) {
                isValid = false;
            }
        }
    });
    
    // Validate donation amount
    const amountOptions = document.querySelectorAll('.amount-option');
    const customAmountInput = document.getElementById('customAmount');
    const activeOption = document.querySelector('.amount-option.active');
    
    let hasAmount = false;
    
    if (activeOption) {
        hasAmount = true;
    } else if (customAmountInput && customAmountInput.value) {
        const amount = parseFloat(customAmountInput.value);
        if (amount > 0) {
            hasAmount = true;
        }
    }
    
    if (!hasAmount) {
        isValid = false;
        alert('Please select or enter a donation amount.');
        
        // Scroll to amount section
        const amountSection = document.querySelector('.amount-options');
        if (amountSection) {
            amountSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    if (!isValid) {
        // Scroll to first invalid field
        const firstInvalidField = form.querySelector('.is-invalid');
        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    return isValid;
}
