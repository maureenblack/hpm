/**
 * Payment System JavaScript
 * Holistic Prosperity Ministry
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let currentStep = 1;
    let donationAmount = 0;
    let processingFee = 0;
    let totalAmount = 0;
    let paymentMethod = 'credit_card';
    let isRecurring = false;
    let stripePaymentIntent = null;
    
    // DOM elements
    const donationForm = document.getElementById('donation-form');
    const amountOptions = document.querySelectorAll('input[name="donation_amount_preset"]');
    const customAmountWrapper = document.querySelector('.custom-amount-wrapper');
    const customAmountInput = document.getElementById('custom_amount');
    const donationAmountInput = document.getElementById('donation_amount');
    const coverFeesCheckbox = document.getElementById('cover_fees');
    const feeExplanation = document.getElementById('fee-explanation');
    const feeAmountSpan = document.getElementById('fee-amount');
    const isTributeCheckbox = document.getElementById('is_tribute');
    const tributeFields = document.getElementById('tribute-fields');
    const frequencyOptions = document.querySelectorAll('input[name="donation_frequency"]');
    const paymentMethodOptions = document.querySelectorAll('input[name="payment_method"]');
    const paymentForms = document.querySelectorAll('.payment-form');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const formSteps = document.querySelectorAll('.form-step');
    const progressSteps = document.querySelectorAll('.donation-steps .step');
    const paymentButton = document.getElementById('payment-button');
    const submitButton = document.getElementById('submit-donation');
    
    // Summary elements
    const summaryAmount = document.getElementById('summary-amount');
    const summaryCategory = document.getElementById('summary-category');
    const summaryFrequency = document.getElementById('summary-frequency');
    const summaryFeesRow = document.getElementById('summary-fees-row');
    const summaryFees = document.getElementById('summary-fees');
    const summaryTotal = document.getElementById('summary-total');
    const summaryMethod = document.getElementById('summary-method');
    const summaryTributeRow = document.getElementById('summary-tribute-row');
    const summaryTributeType = document.getElementById('summary-tribute-type');
    const summaryTributeName = document.getElementById('summary-tribute-name');
    
    // Reference codes for different payment methods
    const momoReference = document.getElementById('momo-reference');
    const zelleReference = document.getElementById('zelle-reference');
    const cashappReference = document.getElementById('cashapp-reference');
    const bankReference = document.getElementById('bank-reference');
    
    // Initialize Stripe elements if available
    let stripe, elements, cardElement;
    if (typeof Stripe !== 'undefined') {
        stripe = Stripe(stripePublishableKey);
        elements = stripe.elements();
        
        // Create card element
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
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
        
        // Mount the card element
        if (document.getElementById('card-element')) {
            cardElement.mount('#card-element');
            
            // Handle card element errors
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        }
    }
    
    // Initialize functions
    function init() {
        // Set initial amount
        updateDonationAmount();
        
        // Add event listeners
        addEventListeners();
    }
    
    // Add event listeners
    function addEventListeners() {
        // Amount selection
        amountOptions.forEach(option => {
            option.addEventListener('change', handleAmountSelection);
        });
        
        // Custom amount input
        if (customAmountInput) {
            customAmountInput.addEventListener('input', updateDonationAmount);
        }
        
        // Cover fees checkbox
        if (coverFeesCheckbox) {
            coverFeesCheckbox.addEventListener('change', updateDonationAmount);
        }
        
        // Tribute checkbox
        if (isTributeCheckbox) {
            isTributeCheckbox.addEventListener('change', function() {
                tributeFields.style.display = this.checked ? 'block' : 'none';
            });
        }
        
        // Frequency options
        frequencyOptions.forEach(option => {
            option.addEventListener('change', function() {
                isRecurring = this.value !== 'one-time';
                updateRecurringPaymentMethods();
            });
        });
        
        // Payment method selection
        paymentMethodOptions.forEach(option => {
            option.addEventListener('change', function() {
                paymentMethod = this.value;
                showPaymentForm(paymentMethod);
            });
        });
        
        // Navigation buttons
        nextButtons.forEach(button => {
            button.addEventListener('click', goToNextStep);
        });
        
        prevButtons.forEach(button => {
            button.addEventListener('click', goToPreviousStep);
        });
        
        // Payment button
        if (paymentButton) {
            paymentButton.addEventListener('click', processPayment);
        }
        
        // Form submission
        if (donationForm) {
            donationForm.addEventListener('submit', handleFormSubmit);
        }
    }
    
    // Handle amount selection
    function handleAmountSelection() {
        const selectedOption = document.querySelector('input[name="donation_amount_preset"]:checked');
        
        if (selectedOption.value === 'custom') {
            customAmountWrapper.style.display = 'block';
            customAmountInput.focus();
        } else {
            customAmountWrapper.style.display = 'none';
            donationAmountInput.value = selectedOption.value;
        }
        
        updateDonationAmount();
    }
    
    // Update donation amount
    function updateDonationAmount() {
        const selectedOption = document.querySelector('input[name="donation_amount_preset"]:checked');
        
        if (selectedOption.value === 'custom') {
            donationAmount = parseFloat(customAmountInput.value) || 0;
        } else {
            donationAmount = parseFloat(selectedOption.value) || 0;
        }
        
        donationAmountInput.value = donationAmount.toFixed(2);
        
        // Calculate processing fee if cover fees is checked
        if (coverFeesCheckbox && coverFeesCheckbox.checked) {
            // Calculate fee: 2.9% + $0.30
            processingFee = (donationAmount * 0.029) + 0.30;
            totalAmount = donationAmount + processingFee;
            
            feeExplanation.style.display = 'block';
            feeAmountSpan.textContent = `This adds $${processingFee.toFixed(2)} to your donation.`;
        } else {
            processingFee = 0;
            totalAmount = donationAmount;
            
            if (feeExplanation) {
                feeExplanation.style.display = 'none';
            }
        }
        
        // Update summary if it exists
        updateSummary();
    }
    
    // Show payment form based on selected method
    function showPaymentForm(method) {
        paymentForms.forEach(form => {
            form.style.display = 'none';
        });
        
        const selectedForm = document.getElementById(`${method}-form`);
        if (selectedForm) {
            selectedForm.style.display = 'block';
        }
    }
    
    // Update recurring payment methods
    function updateRecurringPaymentMethods() {
        const recurringMethods = ['credit_card', 'paypal'];
        
        paymentMethodOptions.forEach(option => {
            if (isRecurring && !recurringMethods.includes(option.value)) {
                option.disabled = true;
                option.parentElement.classList.add('disabled');
                
                // If a non-recurring method was selected, switch to credit card
                if (option.checked) {
                    document.getElementById('method-card').checked = true;
                    showPaymentForm('credit_card');
                }
            } else {
                option.disabled = false;
                option.parentElement.classList.remove('disabled');
            }
        });
    }
    
    // Go to next step
    function goToNextStep() {
        // Validate current step
        if (!validateStep(currentStep)) {
            return;
        }
        
        // Hide current step
        formSteps[currentStep - 1].style.display = 'none';
        
        // Show next step
        currentStep++;
        formSteps[currentStep - 1].style.display = 'block';
        
        // Update progress indicator
        updateProgress();
        
        // Scroll to top of form
        scrollToTop();
        
        // If moving to payment step, update payment form
        if (currentStep === 3) {
            showPaymentForm(paymentMethod);
        }
        
        // If moving to confirmation step, update summary
        if (currentStep === 4) {
            updateSummary();
        }
    }
    
    // Go to previous step
    function goToPreviousStep() {
        // Hide current step
        formSteps[currentStep - 1].style.display = 'none';
        
        // Show previous step
        currentStep--;
        formSteps[currentStep - 1].style.display = 'block';
        
        // Update progress indicator
        updateProgress();
        
        // Scroll to top of form
        scrollToTop();
    }
    
    // Update progress indicator
    function updateProgress() {
        progressSteps.forEach((step, index) => {
            if (index < currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }
    
    // Validate current step
    function validateStep(step) {
        let isValid = true;
        
        switch (step) {
            case 1: // Donation Details
                // Check if amount is valid
                if (donationAmount <= 0) {
                    alert('Please enter a valid donation amount.');
                    isValid = false;
                }
                break;
                
            case 2: // Personal Information
                // Check required fields
                const firstName = document.getElementById('first_name').value.trim();
                const lastName = document.getElementById('last_name').value.trim();
                const email = document.getElementById('email').value.trim();
                
                if (!firstName) {
                    alert('Please enter your first name.');
                    isValid = false;
                } else if (!lastName) {
                    alert('Please enter your last name.');
                    isValid = false;
                } else if (!email) {
                    alert('Please enter your email address.');
                    isValid = false;
                } else if (!validateEmail(email)) {
                    alert('Please enter a valid email address.');
                    isValid = false;
                }
                
                // Check tribute fields if selected
                if (isTributeCheckbox && isTributeCheckbox.checked) {
                    const tributeName = document.getElementById('tribute_name').value.trim();
                    if (!tributeName) {
                        alert('Please enter the name of the honoree.');
                        isValid = false;
                    }
                }
                break;
                
            case 3: // Payment Method
                // Validate based on payment method
                if (paymentMethod === 'mobile_money') {
                    const mobileNumber = document.getElementById('mobile_number').value.trim();
                    if (!mobileNumber) {
                        alert('Please enter your mobile money number.');
                        isValid = false;
                    }
                } else if (paymentMethod === 'zelle') {
                    const zelleEmail = document.getElementById('zelle_email').value.trim();
                    if (!zelleEmail) {
                        alert('Please enter your Zelle email or phone number.');
                        isValid = false;
                    }
                } else if (paymentMethod === 'cashapp') {
                    const cashappName = document.getElementById('cashapp_name').value.trim();
                    if (!cashappName) {
                        alert('Please enter your CashApp name.');
                        isValid = false;
                    }
                } else if (paymentMethod === 'bank_transfer') {
                    const bankName = document.getElementById('bank_name').value.trim();
                    if (!bankName) {
                        alert('Please enter your bank name.');
                        isValid = false;
                    }
                }
                break;
        }
        
        return isValid;
    }
    
    // Validate email format
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
    
    // Update summary
    function updateSummary() {
        if (!summaryAmount) return;
        
        // Get selected category
        const categorySelect = document.getElementById('donation_category');
        const categoryText = categorySelect ? categorySelect.options[categorySelect.selectedIndex].text : '';
        
        // Get selected frequency
        const frequencyOption = document.querySelector('input[name="donation_frequency"]:checked');
        const frequencyText = frequencyOption ? 
            (frequencyOption.value === 'one-time' ? 'One-time Donation' : 
             frequencyOption.value === 'monthly' ? 'Monthly Recurring' : 
             frequencyOption.value === 'quarterly' ? 'Quarterly Recurring' : 
             'Annual Recurring') : '';
        
        // Get payment method text
        const paymentMethodText = 
            paymentMethod === 'credit_card' ? 'Credit/Debit Card' : 
            paymentMethod === 'mobile_money' ? 'Mobile Money' : 
            paymentMethod === 'paypal' ? 'PayPal' : 
            paymentMethod === 'zelle' ? 'Zelle' : 
            paymentMethod === 'cashapp' ? 'CashApp' : 
            'Bank Transfer';
        
        // Update summary fields
        summaryAmount.textContent = `$${donationAmount.toFixed(2)}`;
        summaryCategory.textContent = categoryText;
        summaryFrequency.textContent = frequencyText;
        
        if (processingFee > 0) {
            summaryFeesRow.style.display = 'table-row';
            summaryFees.textContent = `$${processingFee.toFixed(2)}`;
        } else {
            summaryFeesRow.style.display = 'none';
        }
        
        summaryTotal.textContent = `$${totalAmount.toFixed(2)}`;
        summaryMethod.textContent = paymentMethodText;
        
        // Update tribute information if applicable
        if (isTributeCheckbox && isTributeCheckbox.checked) {
            const tributeType = document.getElementById('tribute_type');
            const tributeName = document.getElementById('tribute_name');
            
            if (tributeType && tributeName) {
                summaryTributeRow.style.display = 'table-row';
                summaryTributeType.textContent = tributeType.value === 'honor' ? 'In Honor Of:' : 'In Memory Of:';
                summaryTributeName.textContent = tributeName.value;
            }
        } else {
            summaryTributeRow.style.display = 'none';
        }
    }
    
    // Process payment based on payment method
    async function processPayment() {
        // Validate current step
        if (!validateStep(currentStep)) {
            return;
        }
        
        if (paymentMethod === 'credit_card') {
            // Create payment intent with Stripe
            try {
                const response = await fetch('create-payment-intent.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: totalAmount,
                        currency: 'usd',
                        payment_method_types: ['card'],
                        description: 'Donation to Holistic Prosperity Ministry',
                    }),
                });
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error.message);
                }
                
                // Store the payment intent ID
                document.getElementById('payment_intent_id').value = data.id;
                stripePaymentIntent = data;
                
                // Move to confirmation step
                goToNextStep();
                
            } catch (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            }
        } else {
            // For other payment methods, just go to confirmation step
            goToNextStep();
        }
    }
    
    // Handle form submission
    async function handleFormSubmit(event) {
        event.preventDefault();
        
        // Check terms agreement
        const termsCheckbox = document.getElementById('terms_agreement');
        if (!termsCheckbox.checked) {
            alert('Please agree to the terms and conditions to proceed.');
            return;
        }
        
        // Process based on payment method
        if (paymentMethod === 'credit_card') {
            try {
                // Confirm card payment with Stripe
                const result = await stripe.confirmCardPayment(stripePaymentIntent.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: `${document.getElementById('first_name').value} ${document.getElementById('last_name').value}`,
                            email: document.getElementById('email').value
                        }
                    }
                });
                
                if (result.error) {
                    // Show error to customer
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Payment succeeded, submit form
                    document.getElementById('payment_intent_id').value = result.paymentIntent.id;
                    submitForm();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your payment. Please try again.');
            }
        } else {
            // For other payment methods, just submit the form
            submitForm();
        }
    }
    
    // Submit form
    function submitForm() {
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Submit form
        donationForm.submit();
    }
    
    // Scroll to top of form
    function scrollToTop() {
        window.scrollTo({
            top: donationForm.offsetTop - 100,
            behavior: 'smooth'
        });
    }
    
    // Initialize
    init();
});

// PayPal integration (if applicable)
function initPayPal() {
    if (typeof paypal !== 'undefined' && document.getElementById('paypal-button-container')) {
        paypal.Buttons({
            createOrder: function(data, actions) {
                // Get donation amount
                const amount = document.getElementById('donation_amount').value;
                
                // Create PayPal order
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount
                        },
                        description: 'Donation to Holistic Prosperity Ministry'
                    }]
                });
            },
            onApprove: function(data, actions) {
                // Capture the funds from the transaction
                return actions.order.capture().then(function(details) {
                    // Update form with PayPal details
                    document.getElementById('payment_intent_id').value = details.id;
                    
                    // Submit form
                    document.getElementById('donation-form').submit();
                });
            }
        }).render('#paypal-button-container');
    }
}
