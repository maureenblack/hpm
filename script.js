/**
 * Holistic Prosperity Ministry - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize current year
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    // Initialize gallery modal functionality
    initGalleryModal();
    
    // Initialize initiatives filter
    initInitiativesFilter();
    
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
        const elements = document.querySelectorAll('.value-card, .program-card, .team-card, .ministry-card, .impact-card, .event-card, .resource-card, .involvement-card, .support-card, .ministry-feature, .ministry-detail-card');
        
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
    
    // Ministry Pages Interactive Elements
    
    // Counter animation for ministry metrics
    function initCounters() {
        const counters = document.querySelectorAll('.counter-value');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 seconds
            const step = target / (duration / 16); // ~60fps
            let current = 0;
            
            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.ceil(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        });
    }
    
    // Initialize counter animation when metrics section is in view
    const metricsSection = document.querySelector('.ministry-metrics');
    if (metricsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    initCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(metricsSection);
    }
    
    // Bible Study Group Toggle
    const bibleStudyHeaders = document.querySelectorAll('.bible-study-header');
    bibleStudyHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const card = this.closest('.bible-study-card');
            
            // Toggle active class
            card.classList.toggle('active');
            
            // Toggle aria-expanded
            const expanded = this.getAttribute('aria-expanded') === 'true' || false;
            this.setAttribute('aria-expanded', !expanded);
            
            // Toggle content visibility
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
    
    // Fellowship Group Filtering
    const filterButtons = document.querySelectorAll('.group-filter-btn');
    const fellowshipCards = document.querySelectorAll('.fellowship-card');
    
    if (filterButtons.length > 0 && fellowshipCards.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.getAttribute('data-filter');
                
                // Filter fellowship cards
                fellowshipCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'block';
                    } else if (card.getAttribute('data-category') === filterValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // Gallery Modal Functionality
    function initGalleryModal() {
        const galleryCards = document.querySelectorAll('.gallery-card');
        const modalImage = document.getElementById('galleryModalImage');
        const modalCaption = document.getElementById('galleryModalCaption');
        
        if (galleryCards && modalImage) {
            galleryCards.forEach(card => {
                card.addEventListener('click', function() {
                    const imgSrc = this.getAttribute('data-img');
                    const imgTitle = this.querySelector('.gallery-info h5')?.textContent || '';
                    const imgDate = this.querySelector('.gallery-info p')?.textContent || '';
                    
                    modalImage.src = imgSrc;
                    if (modalCaption) {
                        modalCaption.innerHTML = `<h5>${imgTitle}</h5><p>${imgDate}</p>`;
                    }
                });
            });
        }
    }
    
    // Initiatives Filter Functionality
    function initInitiativesFilter() {
        const filterButtons = document.querySelectorAll('.initiatives-filter .btn');
        const initiativeItems = document.querySelectorAll('.initiative-item');
        
        if (filterButtons && initiativeItems.length > 0) {
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Show/hide items based on filter
                    initiativeItems.forEach(item => {
                        if (filterValue === 'all') {
                            item.style.display = 'block';
                        } else {
                            const categories = item.getAttribute('data-category').split(' ');
                            if (categories.includes(filterValue)) {
                                item.style.display = 'block';
                            } else {
                                item.style.display = 'none';
                            }
                        }
                    });
                });
            });
        }
    }
    
    // Program Details Collapse
    const programHeaders = document.querySelectorAll('.program-header');
    
    programHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const card = this.closest('.program-card');
            
            // Toggle active class
            card.classList.toggle('active');
            
            // Toggle aria-expanded
            const expanded = this.getAttribute('aria-expanded') === 'true' || false;
            this.setAttribute('aria-expanded', !expanded);
            
            // Toggle content visibility
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
    
    // Testimonial Carousel for Ministry Pages
    const ministryCarousels = document.querySelectorAll('.ministry-testimonial-carousel');
    ministryCarousels.forEach(carousel => {
        new bootstrap.Carousel(carousel, {
            interval: 6000,
            wrap: true
        });
    });
    
    // Image Gallery Lightbox
    const galleryImages = document.querySelectorAll('.gallery-image');
    
    galleryImages.forEach(image => {
        image.addEventListener('click', function() {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const captionText = document.getElementById('imageCaption');
            
            if (modal && modalImg) {
                modal.style.display = 'block';
                modalImg.src = this.src;
                if (captionText) {
                    captionText.innerHTML = this.alt;
                }
            }
        });
    });
    
    // Close Modal
    const closeBtn = document.querySelector('.close-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            const modal = document.getElementById('imageModal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Initialize current year in footer copyright
    const yearElement = document.querySelector('.copyright');
    if (yearElement) {
        const currentYear = new Date().getFullYear();
        yearElement.innerHTML = yearElement.innerHTML.replace('2025', currentYear);
    }
});
