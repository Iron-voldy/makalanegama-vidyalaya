/**
 * Makalanegama School Website - Main JavaScript
 * Updated to use admin backend API instead of Telegram
 */

// Global variables
let isLoading = true;
let navbar, backToTopBtn, loadingScreen;

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeElements();
    initializeLoadingScreen();
    initializeNavigation();
    initializeScrollEffects();
    initializeAnimations();
    initializeInteractiveElements();
    initializeGallery();
    initializeCalendar();
    
    // Load dynamic content
    loadDynamicContent();
});

/**
 * Initialize DOM elements
 */
function initializeElements() {
    navbar = document.querySelector('.custom-navbar');
    backToTopBtn = document.getElementById('backToTop');
    loadingScreen = document.getElementById('loading-screen');
}

/**
 * Loading Screen Animation
 */
function initializeLoadingScreen() {
    if (!loadingScreen) return;
    
    // Simulate loading progress
    const progressFill = document.querySelector('.progress-fill');
    const schoolLogo = document.querySelector('.school-logo img');
    
    // Animate logo pulse
    if (typeof gsap !== 'undefined' && schoolLogo) {
        gsap.to(schoolLogo, {
            scale: 1.1,
            duration: 1,
            repeat: -1,
            yoyo: true,
            ease: "power2.inOut"
        });
    }
    
    // Progress bar animation
    if (typeof gsap !== 'undefined' && progressFill) {
        gsap.to(progressFill, {
            width: "100%",
            duration: 3,
            ease: "power2.out",
            onComplete: () => {
                hideLoadingScreen();
            }
        });
    } else {
        // Fallback without GSAP
        setTimeout(() => {
            hideLoadingScreen();
        }, 3000);
    }
}

function hideLoadingScreen() {
    if (!loadingScreen) return;
    
    if (typeof gsap !== 'undefined') {
        gsap.to(loadingScreen, {
            opacity: 0,
            duration: 0.5,
            ease: "power2.out",
            onComplete: () => {
                loadingScreen.style.display = 'none';
                isLoading = false;
                animateHeroSection();
            }
        });
    } else {
        // Fallback without GSAP
        loadingScreen.style.opacity = '0';
        setTimeout(() => {
            loadingScreen.style.display = 'none';
            isLoading = false;
        }, 500);
    }
}

/**
 * Navigation functionality
 */
function initializeNavigation() {
    if (!navbar) return;
    
    // Ensure navbar is in correct state on page load
    const navbarCollapse = document.querySelector('.navbar-collapse');
    if (navbarCollapse) {
        // Remove any existing show classes on page load
        navbarCollapse.classList.remove('show');
        navbarCollapse.style.display = '';
        
        // Reset any dropdown states
        document.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
    
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY > 100;
        navbar.classList.toggle('scrolled', scrolled);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Mobile menu close on link click - Enhanced for dropdowns
    const navbarToggler = document.querySelector('.navbar-toggler');
    
    // Close menu when clicking on any navigation link (including dropdown items)
    const allNavLinks = document.querySelectorAll('.navbar-nav .nav-link, .navbar-nav .dropdown-item');
    
    allNavLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // For dropdown toggles, don't close immediately - let Bootstrap handle it
            if (link.classList.contains('dropdown-toggle')) {
                return;
            }
            
            // For actual navigation links, close the mobile menu
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                setTimeout(() => {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, { toggle: false });
                    bsCollapse.hide();
                }, 100); // Small delay to allow navigation to start
            }
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (navbarCollapse && navbarCollapse.classList.contains('show')) {
            const clickedInsideNav = navbar.contains(e.target);
            if (!clickedInsideNav) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse, { toggle: false });
                bsCollapse.hide();
            }
        }
    });
    
    // Handle dropdown behavior in mobile
    const dropdownToggleButtons = document.querySelectorAll('.navbar-nav .dropdown-toggle');
    dropdownToggleButtons.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            // In mobile view, handle dropdown differently
            if (window.innerWidth < 992) {
                e.preventDefault();
                const dropdownMenu = toggle.nextElementSibling;
                
                if (dropdownMenu) {
                    const isOpen = dropdownMenu.classList.contains('show');
                    
                    // Close all other dropdowns first
                    document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdownMenu) {
                            menu.classList.remove('show');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdownMenu.classList.toggle('show', !isOpen);
                }
            }
        });
    });
    
    // Handle window resize to fix navbar state
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            // Desktop view - reset mobile menu state
            if (navbarCollapse) {
                navbarCollapse.classList.remove('show');
                navbarCollapse.style.display = '';
            }
            
            // Reset all dropdown states
            document.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
}

/**
 * Scroll effects and back to top button
 */
function initializeScrollEffects() {
    // Back to top button
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY > 500;
            backToTopBtn.classList.toggle('visible', scrolled);
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Parallax effect for hero background
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            const parallax = scrolled * 0.5;
            heroSection.style.transform = `translateY(${parallax}px)`;
        });
    }
}

/**
 * GSAP Animations (if available)
 */
function initializeAnimations() {
    if (typeof gsap === 'undefined') return;
    
    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger);
    
    // Animate sections on scroll
    const sections = document.querySelectorAll('section:not(.hero-section)');
    sections.forEach(section => {
        gsap.from(section.children, {
            y: 50,
            opacity: 0,
            duration: 1,
            stagger: 0.2,
            ease: "power2.out",
            scrollTrigger: {
                trigger: section,
                start: "top 80%",
                end: "bottom 20%",
                toggleActions: "play none none reverse"
            }
        });
    });
    
    // Animate cards on hover
    const cards = document.querySelectorAll('.quick-link-card, .achievement-card, .news-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                y: -10,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                y: 0,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    });
    
    // Animate stats counter
    const statsNumbers = document.querySelectorAll('.stat-number');
    statsNumbers.forEach(stat => {
        const finalValue = stat.textContent.replace('+', '');
        if (!isNaN(finalValue)) {
            gsap.from(stat, {
                textContent: 0,
                duration: 2,
                ease: "power2.out",
                snap: { textContent: 1 },
                scrollTrigger: {
                    trigger: stat,
                    start: "top 80%"
                },
                onUpdate: function() {
                    stat.textContent = Math.ceil(this.targets()[0].textContent) + '+';
                }
            });
        }
    });
}

/**
 * Hero section animations
 */
function animateHeroSection() {
    if (typeof gsap === 'undefined') return;
    
    const heroContent = document.querySelector('.hero-content');
    const heroVisual = document.querySelector('.hero-visual');
    
    if (heroContent) {
        // Animate hero content elements
        const tl = gsap.timeline();
        
        tl.from('.hero-badge', {
            y: 30,
            opacity: 0,
            duration: 0.8,
            ease: "power2.out"
        })
        .from('.hero-title', {
            y: 50,
            opacity: 0,
            duration: 1,
            ease: "power2.out"
        }, "-=0.5")
        .from('.hero-subtitle-sinhala', {
            y: 30,
            opacity: 0,
            duration: 0.8,
            ease: "power2.out"
        }, "-=0.7")
        .from('.hero-description', {
            y: 30,
            opacity: 0,
            duration: 0.8,
            ease: "power2.out"
        }, "-=0.6")
        .from('.hero-stats .stat-item', {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.2,
            ease: "power2.out"
        }, "-=0.5")
        .from('.hero-buttons .btn', {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.2,
            ease: "power2.out"
        }, "-=0.4");
    }
    
    if (heroVisual) {
        // Animate hero visual
        gsap.from('.school-building-img', {
            x: 100,
            opacity: 0,
            duration: 1.2,
            ease: "power2.out",
            delay: 0.5
        });
        
        // Animate floating cards
        gsap.from('.floating-card', {
            scale: 0,
            opacity: 0,
            duration: 0.8,
            stagger: 0.3,
            ease: "back.out(1.7)",
            delay: 1
        });
    }
}

/**
 * Interactive elements
 */
function initializeInteractiveElements() {
    // Animated buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            if (typeof gsap !== 'undefined') {
                gsap.to(btn, {
                    scale: 1.05,
                    duration: 0.2,
                    ease: "power2.out"
                });
            }
        });
        
        btn.addEventListener('mouseleave', () => {
            if (typeof gsap !== 'undefined') {
                gsap.to(btn, {
                    scale: 1,
                    duration: 0.2,
                    ease: "power2.out"
                });
            }
        });
    });
}

/**
 * Gallery functionality
 */
function initializeGallery() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        item.addEventListener('click', () => {
            const img = item.querySelector('.gallery-image');
            if (img) {
                createLightbox(img.src);
            }
        });
    });
}

/**
 * Create lightbox for gallery images
 */
function createLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        cursor: pointer;
    `;
    
    lightbox.innerHTML = `
        <div class="lightbox-content" style="position: relative; max-width: 90%; max-height: 90%;">
            <img src="${imageSrc}" alt="Gallery Image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            <button class="lightbox-close" style="position: absolute; top: -40px; right: 0; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;">&times;</button>
        </div>
    `;
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    
    // Close lightbox
    const closeLightbox = () => {
        document.body.removeChild(lightbox);
        document.body.style.overflow = '';
    };
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });
    
    lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closeLightbox();
            document.removeEventListener('keydown', escapeHandler);
        }
    });
}

/**
 * Calendar functionality
 */
function initializeCalendar() {
    const calendarGrid = document.querySelector('.calendar-grid');
    const calendarHeader = document.querySelector('.calendar-header h4');
    const prevBtn = document.querySelector('.btn-nav.prev');
    const nextBtn = document.querySelector('.btn-nav.next');
    
    if (!calendarGrid) return;
    
    let currentDate = new Date();
    
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Update header
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        if (calendarHeader) {
            calendarHeader.textContent = `${monthNames[month]} ${year}`;
        }
        
        // Clear existing days (keep headers)
        const existingDays = calendarGrid.querySelectorAll('.calendar-day:not(.header)');
        existingDays.forEach(day => day.remove());
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Add empty cells for days before month starts
        for (let i = 0; i < firstDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day';
            calendarGrid.appendChild(emptyDay);
        }
        
        // Add days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            
            // Mark event days (example: 25th)
            if (day === 25) {
                dayElement.classList.add('event-day');
            }
            
            calendarGrid.appendChild(dayElement);
        }
    }
    
    // Navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }
    
    // Initial render
    renderCalendar();
}

/**
 * Load dynamic content from API
 */
function loadDynamicContent() {
    loadLatestAchievements();
    loadUpcomingEvents();
    loadLatestNews();
}

/**
 * Load latest achievements from API
 */
async function loadLatestAchievements() {
    try {
        const response = await fetch('/makalanegama-school/makalanegama-school/api/achievements.php?limit=3&featured=1');
        if (!response.ok) throw new Error('Failed to fetch achievements');
        
        const achievements = await response.json();
        updateAchievementsDisplay(achievements);
    } catch (error) {
        console.error('Error loading achievements:', error);
        // Keep existing sample data if API fails
    }
}

/**
 * Load upcoming events from API
 */
async function loadUpcomingEvents() {
    try {
        const response = await fetch('/makalanegama-school/makalanegama-school/api/events.php?limit=3&upcoming=1');
        if (!response.ok) throw new Error('Failed to fetch events');
        
        const events = await response.json();
        updateEventsDisplay(events);
    } catch (error) {
        console.error('Error loading events:', error);
        // Keep existing sample data if API fails
    }
}

/**
 * Load latest news from API
 */
async function loadLatestNews() {
    try {
        const response = await fetch('/makalanegama-school/makalanegama-school/api/news.php?limit=4&featured=1');
        if (!response.ok) throw new Error('Failed to fetch news');
        
        const result = await response.json();
        const news = result.success ? result.data : [];
        updateNewsDisplay(news);
    } catch (error) {
        console.error('Error loading news:', error);
        // Show no news message if API fails
        updateNewsDisplay([]);
    }
}

/**
 * Update achievements display
 */
function updateAchievementsDisplay(achievements) {
    const container = document.getElementById('latest-achievements');
    if (!container || !achievements.length) return;
    
    const existingItems = container.querySelectorAll('.col-lg-4');
    
    achievements.slice(0, 3).forEach((achievement, index) => {
        if (existingItems[index]) {
            const card = existingItems[index].querySelector('.achievement-card');
            if (card) {
                updateAchievementCard(card, achievement);
            }
        }
    });
}

/**
 * Update individual achievement card
 */
function updateAchievementCard(card, achievement) {
    const img = card.querySelector('.achievement-image img');
    const category = card.querySelector('.achievement-category');
    const date = card.querySelector('.achievement-date');
    const title = card.querySelector('h4');
    const description = card.querySelector('p');
    
    if (img && achievement.image_url) {
        img.src = achievement.image_url;
        img.alt = achievement.title;
    }
    if (category) category.textContent = achievement.category;
    if (date) date.textContent = formatDate(achievement.date);
    if (title) title.textContent = achievement.title;
    if (description) description.textContent = truncateText(achievement.description, 100);
}

/**
 * Update events display
 */
function updateEventsDisplay(events) {
    const container = document.getElementById('upcoming-events');
    if (!container || !events.length) return;
    
    const existingItems = container.querySelectorAll('.event-item');
    
    events.slice(0, 3).forEach((event, index) => {
        if (existingItems[index]) {
            updateEventItem(existingItems[index], event);
        }
    });
}

/**
 * Update individual event item
 */
function updateEventItem(item, event) {
    const dateNumber = item.querySelector('.date-number');
    const dateMonth = item.querySelector('.date-month');
    const title = item.querySelector('h5');
    const time = item.querySelector('.event-time');
    const location = item.querySelector('.event-location');
    
    const eventDate = new Date(event.event_date);
    
    if (dateNumber) dateNumber.textContent = eventDate.getDate();
    if (dateMonth) dateMonth.textContent = eventDate.toLocaleDateString('en', { month: 'short' });
    if (title) title.textContent = event.title;
    if (time && event.event_time) {
        time.innerHTML = `<i class="fas fa-clock"></i> ${formatTime(event.event_time)}`;
    }
    if (location) {
        location.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${event.location}`;
    }
}

/**
 * Update news display
 */
function updateNewsDisplay(news) {
    const container = document.getElementById('latest-news');
    const noNewsMessage = document.getElementById('no-news-message');
    const newsContent = document.getElementById('news-content');
    
    if (!container) return;
    
    // Check if we have news data
    if (!news || !news.length) {
        // Show "No news yet" message
        if (noNewsMessage) {
            noNewsMessage.style.display = 'block';
        }
        if (newsContent) {
            newsContent.style.display = 'none';
        }
        return;
    }
    
    // Hide "No news yet" message and show news content
    if (noNewsMessage) {
        noNewsMessage.style.display = 'none';
    }
    if (newsContent) {
        newsContent.style.display = 'block';
        // Create dynamic news content
        createNewsContent(newsContent, news);
    }
}

/**
 * Create dynamic news content
 */
function createNewsContent(container, news) {
    const featuredNews = news[0];
    const sidebarNews = news.slice(1, 4);
    
    container.innerHTML = `
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <article class="news-card featured">
                <div class="news-image">
                    <img src="${featuredNews.image_url || 'assets/images/news/default-news.jpg'}" alt="${featuredNews.title}">
                    <div class="news-category">${featuredNews.category || 'General'}</div>
                </div>
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-date"><i class="fas fa-calendar"></i> ${formatDate(featuredNews.created_at)}</span>
                        <span class="news-author"><i class="fas fa-user"></i> ${featuredNews.author || 'Administration'}</span>
                    </div>
                    <h3>${featuredNews.title}</h3>
                    <p>${featuredNews.excerpt || truncateText(featuredNews.content, 150)}</p>
                    <a href="news.html" class="news-link">Read Full Article <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
        </div>
        
        <div class="col-lg-6">
            <div class="news-sidebar">
                ${sidebarNews.map((newsItem, index) => `
                    <div class="news-item" data-aos="fade-up" data-aos-delay="${200 + (index * 100)}">
                        <div class="news-item-image">
                            <img src="${newsItem.image_url || 'assets/images/news/default-news.jpg'}" alt="${newsItem.title}">
                        </div>
                        <div class="news-item-content">
                            <div class="news-date">${formatDate(newsItem.created_at)}</div>
                            <h5>${newsItem.title}</h5>
                            <p>${newsItem.excerpt || truncateText(newsItem.content, 100)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

/**
 * Update individual news card
 */
function updateNewsCard(card, news) {
    const img = card.querySelector('.news-image img');
    const category = card.querySelector('.news-category');
    const date = card.querySelector('.news-date');
    const title = card.querySelector('h3');
    const content = card.querySelector('p');
    
    if (img && news.image_url) {
        img.src = news.image_url;
        img.alt = news.title;
    }
    if (category) category.textContent = news.category;
    if (date) date.innerHTML = `<i class="fas fa-calendar"></i> ${formatDate(news.date)}`;
    if (title) title.textContent = news.title;
    if (content) content.textContent = news.excerpt || truncateText(news.content, 150);
}

/**
 * Update individual news item
 */
function updateNewsItem(item, news) {
    const img = item.querySelector('.news-item-image img');
    const date = item.querySelector('.news-date');
    const title = item.querySelector('h5');
    const content = item.querySelector('p');
    
    if (img && news.image_url) {
        img.src = news.image_url;
        img.alt = news.title;
    }
    if (date) date.textContent = formatDate(news.date);
    if (title) title.textContent = news.title;
    if (content) content.textContent = news.excerpt || truncateText(news.content, 80);
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

/**
 * Format time for display
 */
function formatTime(timeString) {
    const time = new Date(`2000-01-01T${timeString}`);
    return time.toLocaleTimeString('en', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
}

/**
 * Truncate text to specified length
 */
function truncateText(text, length) {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + '...';
}

/**
 * Handle contact form submission
 */
async function handleContactForm(formData) {
    try {
        const response = await fetch('/api/contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            return { success: true, message: result.message };
        } else {
            return { success: false, message: result.error || 'Failed to send message' };
        }
    } catch (error) {
        console.error('Contact form error:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

/**
 * Utility functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for use in other files
window.MakalanegamaSchool = {
    loadLatestAchievements,
    loadUpcomingEvents,
    loadLatestNews,
    updateAchievementsDisplay,
    updateEventsDisplay,
    updateNewsDisplay,
    handleContactForm,
    formatDate,
    formatTime,
    truncateText
};