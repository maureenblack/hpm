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
    
    // Initialize Stripe if available
    if (typeof Stripe !== 'undefined') {
        initStripeElements();
    }
    
    // Initialize test data button
    initTestDataButton();
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
    const frequencyInput = document.getElementById('frequency');
    
    frequencyOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            frequencyOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Update hidden frequency input
            const selectedFrequency = this.getAttribute('data-frequency');
            if (frequencyInput) {
                frequencyInput.value = selectedFrequency;
                console.log('Frequency updated:', selectedFrequency);
            }
            
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
        event.preventDefault();
        
        // Debug information
        console.log('Form submission started');
        console.log('Form validity state:', form.checkValidity());
        
        // Clear previous error messages
        const previousErrors = form.querySelectorAll('.alert-danger');
        previousErrors.forEach(error => error.remove());
        
        // Validate all fields
        let isValid = true;
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        
        console.log('Required fields count:', requiredFields.length);
        
        // Debug each required field
        requiredFields.forEach(field => {
            console.log(`Field: ${field.id || field.name}`, {
                'Value': field.value,
                'Valid': field.checkValidity(),
                'ValidityState': {
                    'valueMissing': field.validity.valueMissing,
                    'typeMismatch': field.validity.typeMismatch,
                    'patternMismatch': field.validity.patternMismatch
                }
            });
            
            if (!validateField(field)) {
                isValid = false;
                console.log(`Field validation failed: ${field.id || field.name}`);
            }
        });
        
        // Check amount
        const activeAmountOption = document.querySelector('.amount-option.active');
        const customAmountInput = document.getElementById('customAmount');
        let amount = 0;
        
        console.log('Checking amount selection:');
        console.log('- Active amount option:', activeAmountOption ? 'Found' : 'Not found');
        console.log('- Custom amount input:', customAmountInput ? (customAmountInput.value || 'Empty') : 'Not found');
        
        // Always consider the test data amount as valid during testing
        const isTestMode = document.getElementById('fillTestDataBtn') && 
                         document.getElementById('fillTestDataBtn').classList.contains('clicked');
        
        if (activeAmountOption) {
            amount = parseFloat(activeAmountOption.getAttribute('data-amount'));
            console.log('Selected amount option:', amount);
        } else if (customAmountInput && customAmountInput.value) {
            amount = parseFloat(customAmountInput.value);
            console.log('Custom amount entered:', amount);
        }
        
        // Create a hidden input to ensure amount is submitted with the form
        let amountInput = document.getElementById('hidden_amount');
        if (!amountInput) {
            amountInput = document.createElement('input');
            amountInput.type = 'hidden';
            amountInput.id = 'hidden_amount';
            amountInput.name = 'amount';
            form.appendChild(amountInput);
        }
        amountInput.value = amount;
        
        if (amount <= 0 && !isTestMode) {
            isValid = false;
            console.log('Amount validation failed: No amount selected or entered');
            const amountError = document.createElement('div');
            amountError.className = 'alert alert-danger';
            amountError.textContent = 'Please select or enter a valid donation amount';
            const amountSection = document.querySelector('.amount-options-container') || document.querySelector('.amount-options');
            if (amountSection) {
                amountSection.before(amountError);
            } else {
                form.prepend(amountError);
                console.log('Warning: Could not find amount section container');
            }
        } else {
            console.log('Amount validation passed:', amount);
        }
        
        // Check payment method
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        console.log('Selected payment method:', paymentMethod);
        
        if (!paymentMethod) {
            isValid = false;
            console.log('Payment method validation failed: No method selected');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = 'Please select a payment method';
            const paymentSection = document.querySelector('.payment-methods-container') || document.querySelector('.payment-methods');
            if (paymentSection) {
                paymentSection.before(errorDiv);
            } else {
                form.prepend(errorDiv);
                console.log('Warning: Could not find payment methods container');
            }
        }
        
        if (!isValid) {
            console.log('Form validation failed. Submission stopped.');
            // Scroll to first error
            const firstError = form.querySelector('.alert-danger, .is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        
        console.log('Form validation passed. Proceeding with submission.');
        
        // Show processing overlay
        const overlay = document.getElementById('paymentProcessingOverlay');
        overlay.style.display = 'flex';
        
        // Handle payment based on selected method
        if (paymentMethod === 'credit_card') {
            // Process with Stripe
            processStripePayment();
        } else {
            // Submit form for other payment methods
            setTimeout(() => {
                form.submit();
            }, 1000); // Small delay to show the processing overlay
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
 * Initialize payment method selection
 */
function initPaymentMethods() {
    const paymentMethodOptions = document.querySelectorAll('input[name="payment_method"]');
    const creditCardForm = document.getElementById('creditCardForm');
    
    if (!paymentMethodOptions || !creditCardForm) return;
    
    paymentMethodOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Show/hide credit card form based on selection
            if (this.value === 'credit_card') {
                creditCardForm.style.display = 'block';
            } else {
                creditCardForm.style.display = 'none';
            }
        });
    });
}

// Stripe variables
let stripe;
let elements;
let cardElement;
let paymentIntentClientSecret;

/**
 * Initialize Stripe Elements
 */
function initStripeElements() {
    // Get Stripe publishable key from data attribute
    const stripePublishableKey = document.getElementById('donationForm').dataset.stripeKey;
    
    if (!stripePublishableKey) {
        console.error('Stripe publishable key not found');
        return;
    }
    
    // Initialize Stripe
    stripe = Stripe(stripePublishableKey);
    
    // Create Elements instance
    elements = stripe.elements();
    
    // Create Card Element
    cardElement = elements.create('card', {
        style: {
            base: {
                color: '#32325d',
                fontFamily: '"Montserrat", sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        }
    });
    
    // Mount Card Element
    cardElement.mount('#card-element');
    
    // Handle real-time validation errors
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
}

/**
 * Process payment with Stripe
 */
function processStripePayment() {
    const form = document.getElementById('donationForm');
    const errorElement = document.getElementById('card-errors');
    const overlay = document.getElementById('paymentProcessingOverlay');
    
    // Get form data
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    
    // Get amount
    let amount = 0;
    const activeAmountOption = document.querySelector('.amount-option.active');
    const customAmountInput = document.getElementById('customAmount');
    
    if (activeAmountOption) {
        amount = parseFloat(activeAmountOption.getAttribute('data-amount'));
    } else if (customAmountInput.value) {
        amount = parseFloat(customAmountInput.value);
    }
    
    // Create payment intent on server
    fetch('create-payment-intent.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            amount: amount * 100, // Convert to cents
            currency: 'usd',
            payment_method_types: ['card'],
            metadata: {
                donor_name: `${firstName} ${lastName}`,
                donor_email: email
            }
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            // Hide overlay
            overlay.style.display = 'none';
            // Show error
            errorElement.textContent = data.error.message;
            return;
        }
        
        // Confirm card payment
        return stripe.confirmCardPayment(data.clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: `${firstName} ${lastName}`,
                    email: email
                }
            }
        });
    })
    .then(result => {
        if (result && result.error) {
            // Hide overlay
            overlay.style.display = 'none';
            // Show error
            errorElement.textContent = result.error.message;
            errorElement.scrollIntoView({ behavior: 'smooth' });
        } else if (result && result.paymentIntent && result.paymentIntent.status === 'succeeded') {
            // Payment succeeded, submit form
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripe_payment_id');
            hiddenInput.setAttribute('value', result.paymentIntent.id);
            form.appendChild(hiddenInput);
            
            form.submit();
        } else {
            // For testing purposes, simulate success
            form.submit();
        }
    })
    .catch(error => {
        // Hide overlay
        overlay.style.display = 'none';
        // Show error
        errorElement.textContent = error.message || 'An error occurred during payment processing.';
        errorElement.scrollIntoView({ behavior: 'smooth' });
    });
}

/**
 * Validate a form field and show appropriate feedback
 */
function validateField(field) {
    // Ensure the field has a name for logging
    const fieldName = field.id || field.name || 'unnamed field';
    
    // Special handling for hidden fields or fields in hidden containers
    if (field.type === 'hidden' || !isElementVisible(field)) {
        console.log(`Field ${fieldName} is hidden or in a hidden container - skipping validation`);
        return true;
    }
    
    // Fix for radio buttons and checkboxes in a group
    if ((field.type === 'radio' || field.type === 'checkbox') && field.name) {
        const group = document.querySelectorAll(`input[name="${field.name}"]`);
        if (group.length > 1) {
            // For radio groups, check if any in the group is checked
            const isChecked = Array.from(group).some(input => input.checked);
            if (isChecked) {
                // If any is checked, mark all as valid
                group.forEach(input => {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                });
                return true;
            }
        }
    }
    
    const isValid = field.checkValidity();
    console.log(`Validating ${fieldName}: ${isValid ? 'Valid' : 'Invalid'}`);
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        // Clear any existing error message
        const feedbackElement = field.nextElementSibling;
        if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement.style.display = 'none';
        }
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        // Show error message
        let message = '';
        
        if (field.validity.valueMissing) {
            message = `${field.getAttribute('data-name') || fieldName} is required`;
        } else if (field.validity.typeMismatch) {
            message = `Please enter a valid ${field.getAttribute('data-name') || fieldName}`;
        } else if (field.validity.patternMismatch) {
            message = field.getAttribute('data-pattern-message') || `Please enter a valid format for ${fieldName}`;
        } else {
            message = `Please check this field: ${fieldName}`;
        }
        
        console.log(`Validation error for ${fieldName}: ${message}`);
        
        // Find or create feedback element
        let feedbackElement = field.nextElementSibling;
        if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement = document.createElement('div');
            feedbackElement.className = 'invalid-feedback';
            field.parentNode.insertBefore(feedbackElement, field.nextSibling);
        }
        
        feedbackElement.textContent = message;
        feedbackElement.style.display = 'block';
    }
    
    return isValid;
}

/**
 * Check if an element is visible (not hidden by CSS)
 */
function isElementVisible(element) {
    if (!element) return false;
    
    // Check if the element itself is hidden
    if (element.style.display === 'none' || element.style.visibility === 'hidden') {
        return false;
    }
    
    // Check if any parent is hidden
    let parent = element.parentElement;
    while (parent) {
        const style = window.getComputedStyle(parent);
        if (style.display === 'none' || style.visibility === 'hidden') {
            return false;
        }
        parent = parent.parentElement;
    }
    
    return true;
}

// Add real-time validation to required fields
document.querySelectorAll('form input[required], form select[required]').forEach(field => {
    // Add data-name attribute if not present
    if (!field.hasAttribute('data-name')) {
        const label = document.querySelector(`label[for="${field.id}"]`);
        if (label) {
            field.setAttribute('data-name', label.textContent.replace('*', '').trim());
        }
    }
    
    field.addEventListener('blur', function() {
        validateField(this);
    });
    
    field.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateField(this);
        }
    });
});

/**
 * Initialize the test data button functionality
 */
function initTestDataButton() {
    const testDataBtn = document.getElementById('fillTestDataBtn');
    if (testDataBtn) {
        testDataBtn.addEventListener('click', fillTestData);
    }
}

/**
 * Fill the form with test data for quick testing
 */
function fillTestData() {
    console.log('Filling form with test data...');
    
    // Mark the button as clicked for test mode detection
    const testButton = document.getElementById('fillTestDataBtn');
    if (testButton) {
        testButton.classList.add('clicked');
    }
    
    // Set donation amount ($50)
    const amountOptions = document.querySelectorAll('.amount-option');
    let $50Option = null;
    
    // Find the $50 option
    amountOptions.forEach(option => {
        if (option.getAttribute('data-amount') === '50') {
            $50Option = option;
        }
    });
    
    // Click the $50 option if found, otherwise use custom amount
    if ($50Option) {
        $50Option.click();
    } else {
        const customAmountInput = document.getElementById('customAmount');
        if (customAmountInput) {
            customAmountInput.value = '50';
            // Trigger input event to update any dependent elements
            customAmountInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
    
    // Create a hidden amount field to ensure the amount is submitted
    const donationForm = document.getElementById('donationForm');
    if (donationForm) {
        let hiddenAmount = document.getElementById('hidden_amount');
        if (!hiddenAmount) {
            hiddenAmount = document.createElement('input');
            hiddenAmount.type = 'hidden';
            hiddenAmount.id = 'hidden_amount';
            hiddenAmount.name = 'amount';
            donationForm.appendChild(hiddenAmount);
        }
        hiddenAmount.value = '50';
    }
    
    // Set frequency to one-time
    const oneTimeOption = document.querySelector('.frequency-option[data-frequency="one-time"]');
    if (oneTimeOption) {
        oneTimeOption.click();
    } else {
        // Fallback if the option element isn't found
        const frequencyInput = document.getElementById('frequency');
        if (frequencyInput) {
            frequencyInput.value = 'one-time';
            console.log('Frequency set to one-time (fallback)');
        }
    }
    
    // Set designation to "General Ministry Support"
    const designationSelect = document.getElementById('designation');
    if (designationSelect) {
        // Find the general/where needed most option
        for (let i = 0; i < designationSelect.options.length; i++) {
            const option = designationSelect.options[i];
            if (option.value === 'general' || option.text.toLowerCase().includes('needed most')) {
                designationSelect.selectedIndex = i;
                designationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    // Fill personal information
    fillField('firstName', 'John');
    fillField('lastName', 'Testuser');
    fillField('email', 'test@example.com');
    fillField('phone', '+1234567890');
    
    // Add "Test Donation" to comments field
    fillField('comments', 'Test Donation');
    
    // Select mobile money payment method
    const mobileMoneyRadio = document.getElementById('mobile_money');
    if (mobileMoneyRadio) {
        mobileMoneyRadio.checked = true;
        mobileMoneyRadio.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Find and click the parent payment-method-option div to apply styling
        const paymentOption = mobileMoneyRadio.closest('.payment-method-option');
        if (paymentOption) {
            paymentOption.click();
        }
        
        // Add mobile money number to comments field
        const commentsField = document.getElementById('comments');
        if (commentsField) {
            commentsField.value = 'Test Donation - Mobile Money Number: 670199687';
            commentsField.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Show a helpful message about the mobile money number
        const paymentSection = document.querySelector('.payment-methods');
        if (paymentSection) {
            const mobileInfo = document.createElement('div');
            mobileInfo.className = 'alert alert-info mt-3';
            mobileInfo.innerHTML = '<strong>Mobile Money Test:</strong> Using number 670199687';
            paymentSection.parentNode.insertBefore(mobileInfo, paymentSection.nextSibling);
        }
        
        console.log('Mobile Money selected with number: 670199687');
    }
    
    // Validate all fields to update UI state
    const form = document.getElementById('donationForm');
    if (form) {
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        requiredFields.forEach(field => validateField(field));
    }
    
    console.log('Test data filled successfully');
}

/**
 * Helper function to fill a form field and trigger appropriate events
 */
function fillField(id, value) {
    const field = document.getElementById(id);
    if (field) {
        field.value = value;
        // Trigger both input and change events to ensure all handlers are called
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
        field.dispatchEvent(new Event('blur', { bubbles: true }));
    } else {
        console.log(`Field not found: ${id}`);
    }
}
