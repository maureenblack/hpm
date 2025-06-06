/**
 * Payment System JavaScript
 * Holistic Prosperity Ministry
 * Simplified for Mobile Money and Stripe payment links
 * 
 * IMPORTANT: This file has been simplified to remove all Stripe API integration.
 * The payment system now uses direct payment links for Stripe and a simple
 * redirect for Mobile Money payments.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let donationAmount = 0;
    let processingFee = 0;
    let totalAmount = 0;
    
    // DOM elements
    const donationForm = document.getElementById('donation-form');
    const amountOptions = document.querySelectorAll('.amount-option');
    const customAmountInput = document.getElementById('customAmount');
    const coverFeesCheckbox = document.getElementById('cover_fees');
    const feeExplanation = document.getElementById('fee-explanation');
    const feeAmountSpan = document.getElementById('fee-amount');
    const tributeCheckbox = document.getElementById('is_tribute');
    const tributeFields = document.getElementById('tribute-fields');
    const paymentProcessingOverlay = document.getElementById('paymentProcessingOverlay');
    
    // Initialize
    init();
    
    /**
     * Initialize the payment system
     */
    function init() {
        // Set default amount if amount options exist
        if (amountOptions.length > 0) {
            amountOptions[0].classList.add('active');
            updateAmount(amountOptions[0].getAttribute('data-amount'));
        }
        
        // Add event listeners
        addEventListeners();
    }
    
    /**
     * Add event listeners to form elements
     */
    function addEventListeners() {
        // Amount options
        amountOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                amountOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Clear custom amount
                if (customAmountInput) {
                    customAmountInput.value = '';
                }
                
                // Update amount
                updateAmount(this.getAttribute('data-amount'));
            });
        });
        
        // Custom amount input
        if (customAmountInput) {
            customAmountInput.addEventListener('input', function() {
                // Remove active class from preset options
                amountOptions.forEach(opt => opt.classList.remove('active'));
                
                // Update amount if valid
                if (this.value && !isNaN(this.value)) {
                    updateAmount(this.value);
                }
            });
        }
        
        // Cover fees checkbox
        if (coverFeesCheckbox) {
            coverFeesCheckbox.addEventListener('change', function() {
                updateAmount(donationAmount);
            });
        }
        
        // Tribute checkbox
        if (tributeCheckbox) {
            tributeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    tributeFields.style.display = 'block';
                } else {
                    tributeFields.style.display = 'none';
                }
            });
        }
        
        // Mobile Money button
        if (mobileMoneyBtn) {
            mobileMoneyBtn.addEventListener('click', function() {
                // Set payment method
                paymentMethodInput.value = 'mobile_money';
                
                // Highlight this card
                paymentMethodCards.forEach(card => card.classList.remove('selected'));
                const mobileMoneyCard = document.getElementById('mobile-money-option');
                if (mobileMoneyCard) {
                    mobileMoneyCard.classList.add('selected');
                }
                
                // Process the mobile money payment
                processMobileMoneyPayment();
            });
        }
        
        // Add hover effects to all payment method cards
        paymentMethodCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('hover');
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('hover');
            });
        });
        
        // Form submission
        if (donationForm) {
            donationForm.addEventListener('submit', function(event) {
                // Prevent default form submission
                event.preventDefault();
                
                // Validate form
                if (!validateForm()) {
                    return;
                }
                
                // Check if payment method is selected
                if (!paymentMethodInput.value) {
                    alert('Please select a payment method');
                    return;
                }
                
                // Process payment based on method
                if (paymentMethodInput.value === 'mobile_money') {
                    processMobileMoneyPayment();
                } else {
                    // For other methods, submit form normally
                    this.submit();
                }
            });
        }
    }
    
    /**
     * Update donation amount
     */
    function updateAmount(amount) {
        donationAmount = parseFloat(amount);
        
        // Update hidden amount field
        const hiddenAmountInput = document.getElementById('hidden_amount');
        if (hiddenAmountInput) {
            hiddenAmountInput.value = donationAmount;
        }
        
        // Calculate processing fee if cover fees is checked
        if (coverFeesCheckbox && coverFeesCheckbox.checked) {
            // Calculate fee: 2.9% + $0.30 (standard credit card processing fee)
            processingFee = (donationAmount * 0.029) + 0.30;
            totalAmount = donationAmount + processingFee;
            
            if (feeExplanation && feeAmountSpan) {
                feeExplanation.style.display = 'block';
                feeAmountSpan.textContent = processingFee.toFixed(2);
            }
        } else {
            processingFee = 0;
            totalAmount = donationAmount;
            
            if (feeExplanation) {
                feeExplanation.style.display = 'none';
            }
        }
        
        // Update total amount display
        const totalAmountDisplay = document.getElementById('total-amount');
        if (totalAmountDisplay) {
            totalAmountDisplay.textContent = totalAmount.toFixed(2);
        }
    }
    
    /**
     * Validate the donation form
     */
    function validateForm() {
        // Check if amount is selected
        if (donationAmount <= 0) {
            alert('Please select or enter a donation amount');
            return false;
        }
        
        // Validate personal information fields if they exist
        const firstNameInput = document.getElementById('firstName');
        const lastNameInput = document.getElementById('lastName');
        const emailInput = document.getElementById('email');
        
        if (firstNameInput && !firstNameInput.value) {
            alert('Please enter your first name');
            firstNameInput.focus();
            return false;
        }
        
        if (lastNameInput && !lastNameInput.value) {
            alert('Please enter your last name');
            lastNameInput.focus();
            return false;
        }
        
        if (emailInput && !emailInput.value) {
            alert('Please enter your email address');
            emailInput.focus();
            return false;
        }
        
        // Validate email format
        if (emailInput && emailInput.value && !validateEmail(emailInput.value)) {
            alert('Please enter a valid email address');
            emailInput.focus();
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate email format
     */
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Mobile Money button handling is now done directly with HTML links
    
    // All payment processing is now handled directly through HTML links
    // No JavaScript processing is required for Mobile Money or Stripe payments
});
