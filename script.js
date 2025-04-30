/**
 * Holistic Prosperity Ministries - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Enhanced dropdown functionality for mobile
    const dropdownToggleList = document.querySelectorAll('.dropdown-toggle');
    
    // Function to close all dropdowns
    function closeAllDropdowns() {
        dropdownToggleList.forEach(dropdown => {
            const dropdownMenu = dropdown.nextElementSibling;
            if (dropdownMenu.classList.contains('show')) {
                dropdown.classList.remove('show');
                dropdown.setAttribute('aria-expanded', 'false');
                dropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            closeAllDropdowns();
        }
    });
    
    // Keyboard navigation for dropdowns
    dropdownToggleList.forEach(dropdown => {
        dropdown.addEventListener('keydown', function(e) {
            // Open dropdown on Enter or Space
            if ((e.key === 'Enter' || e.key === ' ') && !dropdown.nextElementSibling.classList.contains('show')) {
                e.preventDefault();
                dropdown.click();
            }
            
            // Close dropdown on Escape
            if (e.key === 'Escape' && dropdown.nextElementSibling.classList.contains('show')) {
                dropdown.click();
                dropdown.focus();
            }
            
            // Navigate to first dropdown item on Down Arrow
            if (e.key === 'ArrowDown' && dropdown.nextElementSibling.classList.contains('show')) {
                e.preventDefault();
                const firstItem = dropdown.nextElementSibling.querySelector('.dropdown-item');
                if (firstItem) firstItem.focus();
            }
        });
    });
    
    // Keyboard navigation for dropdown items
    document.querySelectorAll('.dropdown-item').forEach((item, index, items) => {
        item.addEventListener('keydown', function(e) {
            // Navigate to previous item on Up Arrow
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (index > 0) {
                    items[index - 1].focus();
                } else {
                    // If first item, focus back on dropdown toggle
                    const dropdownToggle = item.closest('.dropdown-menu').previousElementSibling;
                    dropdownToggle.focus();
                }
            }
            
            // Navigate to next item on Down Arrow
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (index < items.length - 1) {
                    items[index + 1].focus();
                }
            }
            
            // Close dropdown and focus on toggle on Escape
            if (e.key === 'Escape') {
                e.preventDefault();
                const dropdownToggle = item.closest('.dropdown-menu').previousElementSibling;
                dropdownToggle.click();
                dropdownToggle.focus();
            }
        });
    });
    
    // Handle tab navigation with URL hash
    function handleTabNavigation() {
        const hash = window.location.hash;
        if (hash) {
            const tabId = hash.substring(1); // Remove the # character
            const tabElement = document.querySelector(`a[data-bs-target="#${tabId}"]`);
            
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
                
                // If tab is in a dropdown, highlight parent dropdown
                const dropdownParent = tabElement.closest('.dropdown-menu');
                if (dropdownParent) {
                    const dropdownToggle = dropdownParent.previousElementSibling;
                    dropdownToggle.classList.add('active');
                }
                
                // Scroll to top after tab change
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }
    }
    
    // Initial check for hash in URL
    handleTabNavigation();
    
    // Listen for hash changes
    window.addEventListener('hashchange', handleTabNavigation);
    
    // Add animation to elements when they come into view
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const animation = element.dataset.animation || 'fade-in';
                element.classList.add(animation);
                observer.unobserve(element);
            }
        });
    }, { threshold: 0.1 });
    
    animateElements.forEach(element => {
        observer.observe(element);
    });
    
    // Handle form submissions
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Simple form validation
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (isValid) {
                // In a real application, this would send the form data to a server
                // For this demo, we'll just show a success message
                
                // Create success alert
                const formContainer = form.closest('.contact-form, .donation-form, .footer-form');
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success mt-3';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = 'Thank you for your submission! We will get back to you soon.';
                
                // Add the alert to the form container
                formContainer.appendChild(alertDiv);
                
                // Reset the form
                form.reset();
                
                // Remove the alert after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        });
    });
    
    // Add event listeners to donation amount buttons if they exist
    const donationButtons = document.querySelectorAll('.donation-amount-btn');
    const donationInput = document.getElementById('donationAmount');
    
    if (donationButtons.length > 0 && donationInput) {
        donationButtons.forEach(button => {
            button.addEventListener('click', function() {
                const amount = this.dataset.amount;
                donationInput.value = amount;
                
                // Remove active class from all buttons
                donationButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
            });
        });
    }
    
    // Testimonial carousel autoplay configuration
    const testimonialCarousel = document.getElementById('testimonialCarousel');
    if (testimonialCarousel) {
        const carousel = new bootstrap.Carousel(testimonialCarousel, {
            interval: 5000,
            wrap: true
        });
    }
    
    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Only apply smooth scroll for same-page links, not tab navigation
            if (targetId.length > 1 && !this.hasAttribute('data-bs-toggle')) {
                e.preventDefault();
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Add class to animate elements when they come into view
    function animateOnScroll() {
        const elements = document.querySelectorAll('.value-card, .program-card, .team-card, .ministry-card, .impact-card, .event-card, .resource-card, .involvement-card, .support-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('slide-up');
            }
        });
    }
    
    // Run animation check on load and scroll
    window.addEventListener('load', animateOnScroll);
    window.addEventListener('scroll', animateOnScroll);
    
    // Initialize current year in footer copyright
    const yearElement = document.querySelector('.copyright');
    if (yearElement) {
        const currentYear = new Date().getFullYear();
        yearElement.innerHTML = yearElement.innerHTML.replace('2025', currentYear);
    }
});
