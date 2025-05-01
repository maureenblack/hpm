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
            
            // Get selected payment method
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            
            if (!paymentMethod) {
                // Show error if no payment method selected
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.textContent = 'Please select a payment method';
                form.prepend(errorDiv);
                errorDiv.scrollIntoView({ behavior: 'smooth' });
                return;
            }
            
            // Handle payment based on selected method
            if (paymentMethod === 'credit_card') {
                // Process with Stripe
                processStripePayment();
            } else {
                // Submit form for other payment methods
                form.submit();
            }
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
async function processStripePayment() {
    const form = document.getElementById('donationForm');
    const submitButton = document.getElementById('donateButton');
    
    // Disable the submit button to prevent multiple clicks
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';
    
    try {
        // Get form data
        const formData = new FormData(form);
        
        // Create payment intent on the server
        const response = await fetch('create-payment-intent.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const result = await response.json();
        
        if (result.error) {
            // Show error
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = result.error.message;
            submitButton.disabled = false;
            submitButton.textContent = 'Complete Donation';
            return;
        }
        
        // Confirm card payment
        const { paymentIntent, error } = await stripe.confirmCardPayment(result.clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: formData.get('firstName') + ' ' + formData.get('lastName'),
                    email: formData.get('email')
                }
            }
        });
        
        if (error) {
            // Show error
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
            submitButton.disabled = false;
            submitButton.textContent = 'Complete Donation';
        } else if (paymentIntent.status === 'succeeded') {
            // Payment succeeded, submit form with payment intent ID
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'payment_intent_id');
            hiddenInput.setAttribute('value', paymentIntent.id);
            form.appendChild(hiddenInput);
            
            // Submit the form
            form.submit();
        }
    } catch (error) {
        console.error('Error:', error);
        const errorElement = document.getElementById('card-errors');
        errorElement.textContent = 'An unexpected error occurred. Please try again.';
        submitButton.disabled = false;
        submitButton.textContent = 'Complete Donation';
    }
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
