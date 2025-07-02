/**
 * Makalanegama School Website - Main JavaScript
 * Modern interactions, animations, and dynamic content loading
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
    gsap.to(schoolLogo, {
        scale: 1.1,
        duration: 1,
        repeat: -1,
        yoyo: true,
        ease: "power2.inOut"
    });
    
    // Progress bar animation
    gsap.to(progressFill, {
        width: "100%",
        duration: 3,
        ease: "power2.out",
        onComplete: () => {
            hideLoadingScreen();
        }
    });
}

function hideLoadingScreen() {
    if (!loadingScreen) return;
    
    gsap.to(loadingScreen, {
        opacity: 0,
        duration: 0.5,
        ease: "power2.out",
        onComplete: () => {
            loadingScreen.style.display = 'none';
            isLoading = false;
            // Trigger hero animations
            animateHeroSection();
        }
    });
}

/**
 * Navigation functionality
 */
function initializeNavigation() {
    if (!navbar) return;
    
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY > 100;
        navbar.classList.toggle('scrolled', scrolled);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Mobile menu close on link click
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
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
 * GSAP Animations
 */
function initializeAnimations() {
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
            gsap.to(btn, {
                scale: 1.05,
                duration: 0.2,
                ease: "power2.out"
            });
        });
        
        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, {
                scale: 1,
                duration: 0.2,
                ease: "power2.out"
            });
        });
    });
    
    // Event items animation
    const eventItems = document.querySelectorAll('.event-item');
    eventItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            gsap.to(item, {
                x: 10,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        item.addEventListener('mouseleave', () => {
            gsap.to(item, {
                x: 0,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    });
    
    // News items animation
    const newsItems = document.querySelectorAll('.news-item');
    newsItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            gsap.to(item, {
                x: 10,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        item.addEventListener('mouseleave', () => {
            gsap.to(item, {
                x: 0,
                duration: 0.3,
                ease: "power2.out"
            });
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
            // Create lightbox effect
            createLightbox(item.querySelector('.gallery-image').src);
        });
        
        // Hover animation
        item.addEventListener('mouseenter', () => {
            gsap.to(item.querySelector('.gallery-image'), {
                scale: 1.1,
                duration: 0.5,
                ease: "power2.out"
            });
            
            gsap.to(item.querySelector('.gallery-overlay'), {
                opacity: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        item.addEventListener('mouseleave', () => {
            gsap.to(item.querySelector('.gallery-image'), {
                scale: 1,
                duration: 0.5,
                ease: "power2.out"
            });
            
            gsap.to(item.querySelector('.gallery-overlay'), {
                opacity: 0,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    });
}

/**
 * Create lightbox for gallery images
 */
function createLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <img src="${imageSrc}" alt="Gallery Image">
            <button class="lightbox-close">&times;</button>
        </div>
    `;
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    
    // Animate lightbox in
    gsap.from(lightbox, {
        opacity: 0,
        duration: 0.3,
        ease: "power2.out"
    });
    
    gsap.from(lightbox.querySelector('.lightbox-content'), {
        scale: 0.8,
        duration: 0.3,
        ease: "back.out(1.7)"
    });
    
    // Close lightbox
    const closeLightbox = () => {
        gsap.to(lightbox, {
            opacity: 0,
            duration: 0.3,
            ease: "power2.out",
            onComplete: () => {
                document.body.removeChild(lightbox);
                document.body.style.overflow = '';
            }
        });
    };
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });
    
    lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
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
        calendarHeader.textContent = `${monthNames[month]} ${year}`;
        
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
 * Load dynamic content from Telegram
 */
function loadDynamicContent() {
    // Check if we're in development mode
    const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    
    if (isDevelopment) {
        // Load sample data for development
        loadSampleData();
    } else {
        // Load real data from Telegram API
        loadTelegramContent();
    }
}

/**
 * Load sample data for development
 */
function loadSampleData() {
    // Sample achievements
    const sampleAchievements = [
        {
            title: "Provincial Mathematics Excellence",
            description: "Our Grade 10 students achieved outstanding results in the provincial mathematics competition, securing first place among 50 participating schools.",
            image: "assets/images/achievements/math-competition.jpg",
            category: "Academic",
            date: "2024-02-15"
        },
        {
            title: "Inter-School Cricket Championship",
            description: "Our cricket team won the zonal championship after a thrilling final match against St. Joseph's College.",
            image: "assets/images/achievements/cricket-win.jpg",
            category: "Sports",
            date: "2024-01-20"
        },
        {
            title: "Environmental Conservation Award",
            description: "Recognition for our school's outstanding contribution to environmental conservation through our gardening and sustainability projects.",
            image: "assets/images/achievements/environment-award.jpg",
            category: "Environment",
            date: "2024-01-10"
        }
    ];
    
    // Sample events
    const sampleEvents = [
        {
            title: "Annual Sports Day",
            date: "2024-02-25",
            time: "8:00 AM - 4:00 PM",
            location: "School Grounds",
            description: "Join us for our annual sports day featuring various athletic competitions and cultural performances."
        },
        {
            title: "Parent-Teacher Meeting",
            date: "2024-03-15",
            time: "2:00 PM - 5:00 PM",
            location: "School Hall",
            description: "Meet with teachers to discuss student progress and academic performance."
        },
        {
            title: "Science Fair 2024",
            date: "2024-04-10",
            time: "9:00 AM - 3:00 PM",
            location: "Computer Lab",
            description: "Students will showcase their innovative science projects and experiments."
        }
    ];
    
    // Sample news
    const sampleNews = [
        {
            title: "New Computer Lab Officially Opens",
            content: "We are proud to announce the official opening of our state-of-the-art computer laboratory, made possible through the generous support of Wire Academy & Technology for Village.",
            image: "assets/images/news/computer-lab.jpg",
            category: "Facilities",
            date: "2024-02-10"
        },
        {
            title: "2024 Admissions Now Open",
            content: "Applications for Grade 1 admissions for the 2024 academic year are now being accepted. Please visit the school office for application forms and requirements.",
            image: "assets/images/news/admissions.jpg",
            category: "Admissions",
            date: "2024-02-05"
        }
    ];
    
    // Update DOM with sample data
    updateAchievementsDisplay(sampleAchievements);
    updateEventsDisplay(sampleEvents);
    updateNewsDisplay(sampleNews);
}

/**
 * Load content from Telegram API
 */
async function loadTelegramContent() {
    try {
        // Load achievements
        const achievements = await fetchTelegramContent('achievements');
        updateAchievementsDisplay(achievements);
        
        // Load events
        const events = await fetchTelegramContent('events');
        updateEventsDisplay(events);
        
        // Load news
        const news = await fetchTelegramContent('news');
        updateNewsDisplay(news);
        
    } catch (error) {
        console.error('Error loading Telegram content:', error);
        // Fallback to sample data
        loadSampleData();
    }
}

/**
 * Fetch content from Telegram API
 */
async function fetchTelegramContent(type) {
    const response = await fetch(`/api/${type}.php`);
    if (!response.ok) {
        throw new Error(`Failed to fetch ${type}`);
    }
    return await response.json();
}

/**
 * Update achievements display
 */
function updateAchievementsDisplay(achievements) {
    const container = document.getElementById('latest-achievements');
    if (!container || !achievements.length) return;
    
    // Clear existing content except the first 3 sample items for now
    const existingItems = container.querySelectorAll('.col-lg-4');
    
    // Update existing items with new data
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
    
    if (img && achievement.image) img.src = achievement.image;
    if (category) category.textContent = achievement.category;
    if (date) date.textContent = formatDate(achievement.date);
    if (title) title.textContent = achievement.title;
    if (description) description.textContent = achievement.description;
}

/**
 * Update events display
 */
function updateEventsDisplay(events) {
    const container = document.getElementById('upcoming-events');
    if (!container || !events.length) return;
    
    // Update existing event items
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
    
    const eventDate = new Date(event.date);
    
    if (dateNumber) dateNumber.textContent = eventDate.getDate();
    if (dateMonth) dateMonth.textContent = eventDate.toLocaleDateString('en', { month: 'short' });
    if (title) title.textContent = event.title;
    if (time) time.innerHTML = `<i class="fas fa-clock"></i> ${event.time}`;
    if (location) location.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${event.location}`;
}

/**
 * Update news display
 */
function updateNewsDisplay(news) {
    const container = document.getElementById('latest-news');
    if (!container || !news.length) return;
    
    // Update featured news (first item)
    const featuredCard = container.querySelector('.news-card.featured');
    if (featuredCard && news[0]) {
        updateNewsCard(featuredCard, news[0]);
    }
    
    // Update sidebar news items
    const sidebarItems = container.querySelectorAll('.news-item');
    news.slice(1, 4).forEach((newsItem, index) => {
        if (sidebarItems[index]) {
            updateNewsItem(sidebarItems[index], newsItem);
        }
    });
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
    
    if (img && news.image) img.src = news.image;
    if (category) category.textContent = news.category;
    if (date) date.innerHTML = `<i class="fas fa-calendar"></i> ${formatDate(news.date)}`;
    if (title) title.textContent = news.title;
    if (content) content.textContent = news.content;
}

/**
 * Update individual news item
 */
function updateNewsItem(item, news) {
    const img = item.querySelector('.news-item-image img');
    const date = item.querySelector('.news-date');
    const title = item.querySelector('h5');
    const content = item.querySelector('p');
    
    if (img && news.image) img.src = news.image;
    if (date) date.textContent = formatDate(news.date);
    if (title) title.textContent = news.title;
    if (content) content.textContent = news.content;
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
    loadTelegramContent,
    updateAchievementsDisplay,
    updateEventsDisplay,
    updateNewsDisplay,
    formatDate
};