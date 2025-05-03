/**
 * Holistic Prosperity Ministry Website
 * Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix for mobile dropdown menus
    const fixMobileDropdowns = function() {
        // Get all dropdown toggles in the navigation
        const dropdownToggles = document.querySelectorAll('.navbar-nav .dropdown-toggle');
        
        // Fix for Bootstrap 5 mobile navigation dropdowns
        dropdownToggles.forEach(toggle => {
            // Remove any existing click handlers by cloning and replacing
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            // Add our custom click handler
            newToggle.addEventListener('click', function(e) {
                // Only apply custom behavior on mobile
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get the dropdown menu
                    const dropdownMenu = this.nextElementSibling;
                    
                    // Toggle the dropdown menu
                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        const isOpen = dropdownMenu.classList.contains('show');
                        
                        // Close all other dropdowns first
                        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                            menu.classList.remove('show');
                        });
                        
                        // Toggle the current dropdown
                        if (!isOpen) {
                            dropdownMenu.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                        } else {
                            dropdownMenu.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            });
        });
        
        // Handle clicks outside dropdowns
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown') && window.innerWidth < 992) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
                
                dropdownToggles.forEach(toggle => {
                    toggle.setAttribute('aria-expanded', 'false');
                });
            }
        });
    };
    
    // Run the fix
    fixMobileDropdowns();
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    const backToTopButton = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
            backToTopButton.classList.add('show');
        } else {
            navbar.classList.remove('scrolled');
            backToTopButton.classList.remove('show');
        }
    });
    
    // Back to top button functionality
    backToTopButton.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#') {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput.value) {
                // In a real implementation, you would send this to your backend
                alert('Thank you for subscribing to our newsletter!');
                emailInput.value = '';
            }
        });
    }
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Add animation to elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 50) {
                element.classList.add('animated');
            }
        });
    };
    
    // Run animation check on load and scroll
    window.addEventListener('load', animateOnScroll);
    window.addEventListener('scroll', animateOnScroll);
    
    // Mobile menu collapse when clicking on a link
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                navbarCollapse.classList.remove('show');
            }
        });
    });
    
    // Testimonial carousel autoplay settings
    const testimonialCarousel = document.getElementById('testimonialCarousel');
    if (testimonialCarousel) {
        const carousel = new bootstrap.Carousel(testimonialCarousel, {
            interval: 5000,
            wrap: true
        });
    }
});
