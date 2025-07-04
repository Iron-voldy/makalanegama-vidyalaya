/**
 * Events Page JavaScript - Dynamic Event Loading and Management
 * Handles fetching events from API and displaying them dynamically
 */

class EventsManager {
    constructor() {
        // Use absolute path from the root directory
        this.apiUrl = '/makalanegama-school/makalanegama-school/api/events.php';
        this.currentFilter = 'all';
        this.eventsPerPage = 10;
        this.currentPage = 1;
        this.allEvents = [];
        this.filteredEvents = [];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadEvents();
    }
    
    setupEventListeners() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFilterChange(btn.getAttribute('data-filter'));
                this.updateActiveFilter(btn);
            });
        });
        
        // Category sidebar links
        document.querySelectorAll('.category-list a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const category = link.getAttribute('data-category');
                this.handleFilterChange(category);
                
                // Update main filter buttons
                const filterBtn = document.querySelector(`[data-filter="${category}"]`);
                if (filterBtn) {
                    this.updateActiveFilter(filterBtn);
                }
                
                // Scroll to events
                document.getElementById('events-timeline').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
        
        // Load more button
        const loadMoreBtn = document.getElementById('load-more-events');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreEvents();
            });
        }
    }
    
    async loadEvents() {
        try {
            this.showLoading();
            
            const response = await fetch(this.apiUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (Array.isArray(data)) {
                this.allEvents = data;
                this.filteredEvents = [...this.allEvents];
                this.renderEvents();
                this.updateCategoryCounts();
                this.updateCalendar();
            } else {
                this.showError('Invalid data received from server');
            }
            
        } catch (error) {
            console.error('Error loading events:', error);
            // Show sample events as fallback
            this.loadSampleEvents();
        } finally {
            this.hideLoading();
        }
    }
    
    loadSampleEvents() {
        this.allEvents = [
            {
                id: 1,
                title: 'Annual Sports Day 2024',
                description: 'Join us for our annual sports day featuring various athletic competitions, cultural performances, and team building activities for all grade levels.',
                event_date: '2024-03-25',
                event_time: '08:00:00',
                location: 'School Grounds',
                image_url: null,
                category: 'Sports',
                featured: true,
                created_at: '2024-02-01 10:00:00'
            },
            {
                id: 2,
                title: 'Parent-Teacher Meeting',
                description: 'Meet with your child\'s teachers to discuss academic progress, performance evaluation, and development plans for the upcoming term.',
                event_date: '2024-03-15',
                event_time: '14:00:00',
                location: 'School Hall',
                image_url: null,
                category: 'Parent Meeting',
                featured: false,
                created_at: '2024-02-01 11:00:00'
            },
            {
                id: 3,
                title: 'Science Fair 2024',
                description: 'Students will showcase their innovative science projects and experiments focusing on technology, environment, and scientific discoveries.',
                event_date: '2024-04-10',
                event_time: '09:00:00',
                location: 'Computer Lab',
                image_url: null,
                category: 'Academic',
                featured: false,
                created_at: '2024-02-01 12:00:00'
            }
        ];
        
        this.filteredEvents = [...this.allEvents];
        this.renderEvents();
        this.updateCategoryCounts();
        this.updateCalendar();
    }
    
    handleFilterChange(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        
        if (filter === 'all') {
            this.filteredEvents = [...this.allEvents];
        } else {
            this.filteredEvents = this.allEvents.filter(event => {
                // Map filter names to database categories
                const categoryMap = {
                    'academic': 'Academic',
                    'sports': 'Sports', 
                    'cultural': 'Cultural',
                    'parent': 'Parent Meeting',
                    'workshop': 'Workshop',
                    'examination': 'Examination',
                    'holiday': 'Holiday',
                    'competition': 'Competition'
                };
                
                return event.category === categoryMap[filter] || event.category === filter;
            });
        }
        
        this.renderEvents();
    }
    
    updateActiveFilter(activeBtn) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        activeBtn.classList.add('active');
    }
    
    renderEvents() {
        const container = document.getElementById('events-timeline');
        if (!container) {
            console.error('Events timeline container not found!');
            return;
        }
        
        // Clear existing events
        container.innerHTML = '';
        
        if (this.filteredEvents.length === 0) {
            this.showNoEvents(container);
            return;
        }
        
        // Show events for current page
        const startIndex = 0;
        const endIndex = this.currentPage * this.eventsPerPage;
        const eventsToShow = this.filteredEvents.slice(startIndex, endIndex);
        
        eventsToShow.forEach((event, index) => {
            const eventElement = this.createEventElement(event, index);
            container.appendChild(eventElement);
            
            // Debug: Add a simple visible test element
            const testDiv = document.createElement('div');
            testDiv.style.cssText = 'background: red; color: white; padding: 10px; margin: 10px 0; font-weight: bold;';
            testDiv.textContent = `DEBUG: Event ${index + 1} - ${event.title}`;
            container.appendChild(testDiv);
        });
        
        // Update load more button
        this.updateLoadMoreButton();
        
        // Animate events
        this.animateEvents();
    }
    
    createEventElement(event, index) {
        const eventDate = new Date(event.event_date);
        const day = eventDate.getDate();
        const month = eventDate.toLocaleDateString('en-US', { month: 'short' });
        
        // Determine category class and color
        const categoryClass = this.getCategoryClass(event.category);
        
        const timelineItem = document.createElement('div');
        timelineItem.className = 'event-item';
        timelineItem.style.cssText = 'display: block; width: 100%; margin-bottom: 2rem;';
        timelineItem.setAttribute('data-category', categoryClass);
        
        // Fix image URL handling
        let imageUrl = '/makalanegama-school/makalanegama-school/assets/images/events/default-event.jpg';
        if (event.image_url && event.image_url.trim() !== '') {
            if (event.image_url.startsWith('http')) {
                imageUrl = event.image_url;
            } else {
                imageUrl = `/makalanegama-school/makalanegama-school/${event.image_url}`;
            }
        }
            
        let eventTime = 'All Day';
        if (event.event_time) {
            try {
                eventTime = new Date(`2000-01-01T${event.event_time}`).toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
            } catch (error) {
                eventTime = event.event_time; // fallback to raw time
            }
        }
        
        // Simplified event card layout
        timelineItem.innerHTML = `
            <div class="event-card" style="display: block; margin-bottom: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; position: relative;">
                <div class="event-header" style="background: #800020; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div class="event-date-info">
                        <div style="font-size: 1.5rem; font-weight: bold;">${day}</div>
                        <div style="font-size: 0.9rem; text-transform: uppercase;">${month}</div>
                    </div>
                    <div class="event-category-badge" style="background: rgba(255,255,255,0.2); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;">
                        ${event.category}
                    </div>
                    ${event.featured ? '<div style="background: #fbbf24; color: #000; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><i class="fas fa-star"></i> Featured</div>' : ''}
                </div>
                
                ${event.image_url ? `
                <div class="event-image" style="height: 200px; overflow: hidden;">
                    <img src="${imageUrl}" alt="${this.escapeHtml(event.title)}" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.style.display='none';">
                </div>
                ` : ''}
                
                <div class="event-content" style="padding: 1.5rem;">
                    <h3 style="margin: 0 0 1rem 0; color: #1f2937; font-size: 1.25rem;">${this.escapeHtml(event.title)}</h3>
                    
                    <div class="event-meta" style="margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-clock" style="color: #800020; width: 16px;"></i>
                            <span>${eventTime}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-map-marker-alt" style="color: #800020; width: 16px;"></i>
                            <span>${this.escapeHtml(event.location || 'School')}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-calendar" style="color: #800020; width: 16px;"></i>
                            <span>${eventDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>
                        </div>
                    </div>
                    
                    <p style="color: #4b5563; line-height: 1.6; margin-bottom: 1.5rem;">
                        ${this.escapeHtml(this.truncateText(event.description, 150))}
                    </p>
                    
                    <div class="event-actions" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button class="btn btn-sm" onclick="eventsManager.addToCalendar(${event.id})" 
                                style="background: #800020; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; cursor: pointer;">
                            <i class="fas fa-calendar-plus"></i> Add to Calendar
                        </button>
                        <button class="btn btn-sm" onclick="eventsManager.shareEvent(${event.id})"
                                style="background: transparent; color: #800020; border: 1px solid #800020; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; cursor: pointer;">
                            <i class="fas fa-share"></i> Share
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return timelineItem;
    }
    
    getCategoryClass(category) {
        const categoryMap = {
            'Academic': 'academic',
            'Sports': 'sports',
            'Cultural': 'cultural',
            'Parent Meeting': 'parent',
            'Workshop': 'workshop',
            'Examination': 'examination',
            'Holiday': 'holiday',
            'Competition': 'competition'
        };
        
        return categoryMap[category] || 'academic';
    }
    
    updateCategoryCounts() {
        const categoryCounts = {};
        
        this.allEvents.forEach(event => {
            const categoryClass = this.getCategoryClass(event.category);
            categoryCounts[categoryClass] = (categoryCounts[categoryClass] || 0) + 1;
        });
        
        // Update sidebar category counts
        document.querySelectorAll('.category-list a').forEach(link => {
            const category = link.getAttribute('data-category');
            const countElement = link.querySelector('.count');
            if (countElement && categoryCounts[category]) {
                countElement.textContent = categoryCounts[category];
            }
        });
    }
    
    updateCalendar() {
        // Mark event days in mini calendar
        const eventDates = this.allEvents.map(event => {
            const date = new Date(event.event_date);
            return date.getDate();
        });
        
        // This would integrate with the existing calendar functionality
        // For now, we'll store the event dates for the calendar to use
        window.eventDates = eventDates;
    }
    
    loadMoreEvents() {
        const loadMoreBtn = document.getElementById('load-more-events');
        const originalText = loadMoreBtn.innerHTML;
        
        // Show loading state
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;
        
        // Simulate loading delay
        setTimeout(() => {
            this.currentPage++;
            this.renderEvents();
            
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
        }, 1000);
    }
    
    updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('load-more-events');
        if (!loadMoreBtn) return;
        
        const totalShown = this.currentPage * this.eventsPerPage;
        const hasMore = totalShown < this.filteredEvents.length;
        
        if (hasMore) {
            loadMoreBtn.style.display = 'inline-flex';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
    
    addToCalendar(eventId) {
        const event = this.allEvents.find(e => e.id === eventId);
        if (!event) return;
        
        const startDate = new Date(event.event_date);
        if (event.event_time) {
            const [hours, minutes] = event.event_time.split(':');
            startDate.setHours(parseInt(hours), parseInt(minutes));
        }
        
        const endDate = new Date(startDate);
        endDate.setHours(endDate.getHours() + 2); // Default 2-hour duration
        
        // Format dates for Google Calendar
        const formatDate = (date) => {
            return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        };
        
        const googleCalendarUrl = [
            'https://calendar.google.com/calendar/render?action=TEMPLATE',
            `&text=${encodeURIComponent(event.title)}`,
            `&dates=${formatDate(startDate)}/${formatDate(endDate)}`,
            `&details=${encodeURIComponent(event.description)}`,
            `&location=${encodeURIComponent(event.location || 'Makalanegama School')}`
        ].join('');
        
        window.open(googleCalendarUrl, '_blank');
    }
    
    shareEvent(eventId) {
        const event = this.allEvents.find(e => e.id === eventId);
        if (!event) return;
        
        const shareData = {
            title: `${event.title} - Makalanegama School`,
            text: event.description,
            url: `${window.location.origin}/events.html#event-${eventId}`
        };
        
        if (navigator.share) {
            navigator.share(shareData);
        } else {
            // Fallback to copying URL
            navigator.clipboard.writeText(shareData.url).then(() => {
                this.showToast('Event link copied to clipboard!');
            });
        }
    }
    
    showLoading() {
        const container = document.getElementById('events-timeline');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-maroon" role="status">
                        <span class="visually-hidden">Loading events...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading events...</p>
                </div>
            `;
        }
    }
    
    hideLoading() {
        // Loading will be hidden when events are rendered
    }
    
    showError(message) {
        const container = document.getElementById('events-timeline');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h5>Unable to Load Events</h5>
                        <p>${message}</p>
                        <button class="btn btn-maroon" onclick="eventsManager.loadEvents()">
                            <i class="fas fa-refresh"></i> Try Again
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    showNoEvents(container) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5>No Events Found</h5>
                <p class="text-muted">No events match your current filter. Try selecting a different category.</p>
                <button class="btn btn-outline-maroon" onclick="eventsManager.handleFilterChange('all'); eventsManager.updateActiveFilter(document.querySelector('[data-filter=all]'))">
                    <i class="fas fa-calendar"></i> Show All Events
                </button>
            </div>
        `;
    }
    
    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.innerHTML = `<i class="fas fa-check"></i> ${message}`;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    animateEvents() {
        // Reinitialize AOS for new elements
        if (typeof AOS !== 'undefined') {
            AOS.refreshHard();
        }
        
        // GSAP animations for timeline items
        if (typeof gsap !== 'undefined') {
            gsap.from('.timeline-item', {
                opacity: 0,
                y: 50,
                duration: 0.6,
                stagger: 0.1,
                ease: "power2.out"
            });
        }
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }
    
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }
}

// Function to load upcoming events (called from the main script)
function loadUpcomingEvents() {
    // This function is called from the main page script
    // The EventsManager will be initialized and handle the loading
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the events page
    const eventsTimeline = document.getElementById('events-timeline');
    if (eventsTimeline) {
        window.eventsManager = new EventsManager();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EventsManager;
}