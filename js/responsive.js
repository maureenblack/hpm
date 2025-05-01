/**
 * Holistic Prosperity Ministry Website
 * Responsive Features JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all responsive features
    initLazyLoading();
    initResponsiveTables();
    initTouchFriendlyNav();
    initResponsiveImages();
    handleMobileOptimizations();
});

/**
 * Lazy Loading for Images
 * Only loads images when they come into the viewport
 */
function initLazyLoading() {
    // Select all images with data-src attribute
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

/**
 * Make tables responsive
 * Wraps tables in a responsive container
 */
function initResponsiveTables() {
    const tables = document.querySelectorAll('table:not(.table-responsive)');
    
    tables.forEach(table => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('table-responsive');
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
}

/**
 * Make navigation more touch-friendly on mobile
 */
function initTouchFriendlyNav() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const dropdownMenus = document.querySelectorAll('.dropdown-menu');
    
    // Add touch-friendly classes to navigation
    navLinks.forEach(link => {
        link.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        link.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 300);
        });
    });
    
    // Make dropdown menus more touch-friendly
    dropdownMenus.forEach(menu => {
        const items = menu.querySelectorAll('.dropdown-item');
        items.forEach(item => {
            item.style.padding = '0.75rem 1.5rem';
        });
    });
}

/**
 * Handle responsive images with srcset
 */
function initResponsiveImages() {
    // Find all images with data-srcset attribute
    const responsiveImages = document.querySelectorAll('img[data-srcset]');
    
    responsiveImages.forEach(img => {
        if ('srcset' in img) {
            img.srcset = img.dataset.srcset;
        } else {
            // Fallback for browsers that don't support srcset
            const src = img.dataset.srcset.split(',')[0].trim().split(' ')[0];
            img.src = src;
        }
    });
}

/**
 * Mobile-specific optimizations
 */
function handleMobileOptimizations() {
    const isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        // Reduce animations on mobile
        document.body.classList.add('reduce-motion');
        
        // Simplify certain complex layouts
        const complexLayouts = document.querySelectorAll('.complex-layout');
        complexLayouts.forEach(layout => {
            layout.classList.add('simplified');
        });
        
        // Adjust font sizes for better readability on small screens
        document.documentElement.style.fontSize = '16px';
    }
    
    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        // Adjust layouts after orientation change
        setTimeout(() => {
            adjustLayoutsAfterOrientationChange();
        }, 300);
    });
}

/**
 * Adjust layouts after orientation change
 */
function adjustLayoutsAfterOrientationChange() {
    // Recalculate heights for elements that depend on viewport height
    const heroSections = document.querySelectorAll('.hero-section');
    heroSections.forEach(section => {
        section.style.height = 'auto';
        section.style.minHeight = window.innerHeight * 0.7 + 'px';
    });
    
    // Fix iOS Safari issues with vh units
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIOS) {
        document.documentElement.style.setProperty('--vh', window.innerHeight * 0.01 + 'px');
    }
}

/**
 * Detect when user is on a slow connection and optimize accordingly
 */
function detectSlowConnection() {
    if ('connection' in navigator) {
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        
        if (connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g')) {
            // On slow connections, further optimize
            document.body.classList.add('slow-connection');
            
            // Disable non-essential animations
            const animations = document.querySelectorAll('.animated:not(.essential)');
            animations.forEach(el => {
                el.classList.remove('animated');
            });
            
            // Load lower quality images
            const images = document.querySelectorAll('img[data-low-src]');
            images.forEach(img => {
                img.src = img.dataset.lowSrc;
            });
        }
    }
}

// Call this function to detect slow connections
detectSlowConnection();
