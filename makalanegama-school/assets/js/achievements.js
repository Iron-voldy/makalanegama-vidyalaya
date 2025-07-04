/**
 * Achievements Page JavaScript
 * Handles loading achievements from database, filtering, searching, and display
 */

class AchievementsManager {
    constructor() {
        this.achievements = [];
        this.filteredAchievements = [];
        this.currentFilter = 'all';
        this.currentPage = 1;
        this.achievementsPerPage = 6;
        this.isLoading = false;
        this.searchTerm = '';
        this.currentAchievement = null;
        
        // API endpoints
        this.apiUrl = 'api/achievements.php';
    }

    /**
     * Initialize the achievements manager
     */
    init() {
        this.initializeEventListeners();
        this.loadAchievements();
    }

    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        // Filter buttons
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFilterClick(btn);
            });
        });

        // Search functionality
        const searchInput = document.getElementById('achievement-search');
        const searchBtn = document.getElementById('search-btn');
        
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.handleSearch(e.target.value);
            }, 300));
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.handleSearch(searchInput.value);
            });
        }

        // Load more button
        const loadMoreBtn = document.getElementById('load-more-achievements');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreAchievements();
            });
        }

        // Share button in modal
        const shareBtn = document.getElementById('shareAchievementBtn');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => {
                this.shareCurrentAchievement();
            });
        }
    }

    /**
     * Load achievements from database
     */
    async loadAchievements() {
        try {
            this.showLoading(true);
            
            const response = await fetch(this.apiUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (Array.isArray(data)) {
                this.achievements = data;
                this.filteredAchievements = [...this.achievements];
                this.displayAchievements();
                this.updateStatistics();
                this.showFeaturedAchievements();
            } else {
                console.error('Invalid data format received:', data);
                this.showErrorMessage('Invalid data format received from server');
            }
            
        } catch (error) {
            console.error('Error loading achievements:', error);
            this.showErrorMessage('Failed to load achievements. Please try again later.');
            // Load fallback data for development
            this.loadFallbackData();
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * Load fallback data for development/demo
     */
    loadFallbackData() {
        this.achievements = [
            {
                id: 1,
                title: "Provincial Mathematics Excellence",
                description: "Our Grade 10 students achieved outstanding results in the provincial mathematics competition, securing first place among 50 participating schools from across the North Western Province. This remarkable achievement demonstrates the high quality of mathematics education at our school and the dedication of both students and teachers.",
                category: "Academic",
                image_url: "assets/images/achievements/math-competition.jpg",
                featured: true,
                date: "2024-02-15"
            },
            {
                id: 2,
                title: "Inter-School Cricket Championship",
                description: "Our cricket team emerged victorious in the zonal inter-school cricket championship after a thrilling final match, demonstrating exceptional teamwork, sportsmanship, and dedication to the sport.",
                category: "Sports",
                image_url: "assets/images/achievements/cricket-championship.jpg",
                featured: true,
                date: "2024-01-20"
            },
            {
                id: 3,
                title: "Environmental Conservation Award",
                description: "Recognition for our school's outstanding contribution to environmental conservation through innovative gardening and sustainability projects that have positively impacted the local community.",
                category: "Environmental",
                image_url: "assets/images/achievements/environmental-award.jpg",
                featured: false,
                date: "2024-01-10"
            },
            {
                id: 4,
                title: "Science Fair Innovation",
                description: "Students demonstrated innovative science projects focusing on renewable energy and environmental sustainability, with three projects advancing to the national level competition.",
                category: "Academic",
                image_url: "assets/images/achievements/science-fair.jpg",
                featured: false,
                date: "2023-11-10"
            },
            {
                id: 5,
                title: "Traditional Dance Excellence",
                description: "Our students showcased exceptional talent in traditional Sri Lankan dance at the provincial cultural festival, earning standing ovations and recognition for cultural preservation.",
                category: "Cultural",
                image_url: "assets/images/achievements/cultural-performance.jpg",
                featured: false,
                date: "2023-11-25"
            },
            {
                id: 6,
                title: "Computer Lab Launch",
                description: "Successfully inaugurated our state-of-the-art computer laboratory with support from Wire Academy & Technology for Village, enhancing digital education capabilities.",
                category: "Technology",
                image_url: "assets/images/achievements/computer-lab.jpg",
                featured: false,
                date: "2023-12-15"
            },
            {
                id: 7,
                title: "Track and Field Excellence",
                description: "Outstanding performance in the zonal athletics championship with multiple medal wins in various track and field events, showcasing our students' athletic prowess.",
                category: "Sports",
                image_url: "assets/images/achievements/athletics.jpg",
                featured: false,
                date: "2023-10-18"
            },
            {
                id: 8,
                title: "Inter-School Debate Victory",
                description: "Our debate team secured first place in the inter-school debate competition, demonstrating exceptional critical thinking and public speaking skills.",
                category: "Academic",
                image_url: "assets/images/achievements/debate-competition.jpg",
                featured: false,
                date: "2023-09-22"
            }
        ];
        
        this.filteredAchievements = [...this.achievements];
        this.displayAchievements();
        this.updateStatistics();
        this.showFeaturedAchievements();
    }

    /**
     * Show/hide loading indicator
     */
    showLoading(show) {
        const loadingIndicator = document.getElementById('loading-indicator');
        const achievementsGrid = document.getElementById('achievements-grid');
        
        if (loadingIndicator) {
            loadingIndicator.style.display = show ? 'block' : 'none';
        }
        
        if (achievementsGrid) {
            achievementsGrid.style.opacity = show ? '0.5' : '1';
        }
        
        this.isLoading = show;
    }

    /**
     * Show error message
     */
    showErrorMessage(message) {
        const achievementsGrid = document.getElementById('achievements-grid');
        if (achievementsGrid) {
            achievementsGrid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger text-center" role="alert">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <h4>Error Loading Achievements</h4>
                        <p>${message}</p>
                        <button class="btn btn-maroon" onclick="location.reload()">
                            <i class="fas fa-refresh"></i> Try Again
                        </button>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Handle filter button click
     */
    handleFilterClick(button) {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Add active class to clicked button
        button.classList.add('active');
        
        // Get filter value and apply
        const filter = button.getAttribute('data-filter');
        this.applyFilter(filter);
        
        // Animate filter change
        this.animateFilterChange();
    }

    /**
     * Apply filter to achievements
     */
    applyFilter(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        
        if (filter === 'all') {
            this.filteredAchievements = [...this.achievements];
        } else {
            this.filteredAchievements = this.achievements.filter(achievement => {
                return achievement.category === filter;
            });
        }
        
        // Apply search if active
        if (this.searchTerm) {
            this.applySearch();
        }
        
        this.displayAchievements();
        this.updateLoadMoreButton();
    }

    /**
     * Handle search
     */
    handleSearch(searchTerm) {
        this.searchTerm = searchTerm.toLowerCase().trim();
        this.currentPage = 1;
        
        // Reset to all achievements first, then apply current filter
        this.applyFilter(this.currentFilter);
    }

    /**
     * Apply search to filtered achievements
     */
    applySearch() {
        if (!this.searchTerm) return;
        
        this.filteredAchievements = this.filteredAchievements.filter(achievement => {
            return achievement.title.toLowerCase().includes(this.searchTerm) ||
                   achievement.description.toLowerCase().includes(this.searchTerm) ||
                   achievement.category.toLowerCase().includes(this.searchTerm);
        });
    }

    /**
     * Display achievements
     */
    displayAchievements() {
        const grid = document.getElementById('achievements-grid');
        const noResults = document.getElementById('no-results');
        
        if (!grid) return;
        
        // Calculate items to show
        const startIndex = 0;
        const endIndex = this.currentPage * this.achievementsPerPage;
        const achievementsToShow = this.filteredAchievements.slice(startIndex, endIndex);
        
        if (achievementsToShow.length === 0) {
            grid.innerHTML = '';
            if (noResults) noResults.style.display = 'block';
            return;
        }
        
        if (noResults) noResults.style.display = 'none';
        
        // Clear grid and add achievements
        grid.innerHTML = '';
        
        achievementsToShow.forEach((achievement, index) => {
            const achievementCard = this.createAchievementCard(achievement, index);
            grid.appendChild(achievementCard);
        });
        
        // Update load more button
        this.updateLoadMoreButton();
        
        // Animate cards
        this.animateCards();
    }

    /**
     * Create achievement card element
     */
    createAchievementCard(achievement, index) {
        const card = document.createElement('div');
        card.className = 'col-lg-4 col-md-6';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        const imageUrl = achievement.image_url || 'assets/images/achievements/default-achievement.jpg';
        const categoryClass = achievement.category.toLowerCase();
        
        card.innerHTML = `
            <div class="achievement-card" data-achievement-id="${achievement.id}">
                <div class="achievement-image">
                    <img src="${imageUrl}" alt="${achievement.title}" loading="lazy">
                    <div class="achievement-category ${categoryClass}">${achievement.category}</div>
                    <div class="achievement-overlay">
                        <div class="overlay-actions">
                            <button class="action-btn view-btn" data-bs-toggle="tooltip" title="View Details" onclick="achievementsManager.showAchievementModal(${achievement.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn share-btn" data-bs-toggle="tooltip" title="Share" onclick="achievementsManager.shareAchievement(${achievement.id})">
                                <i class="fas fa-share"></i>
                            </button>
                            ${achievement.featured ? '<button class="action-btn featured-btn" data-bs-toggle="tooltip" title="Featured"><i class="fas fa-star"></i></button>' : ''}
                        </div>
                    </div>
                </div>
                
                <div class="achievement-content">
                    <div class="achievement-meta">
                        <span class="achievement-date">${this.formatDate(achievement.date)}</span>
                        <span class="achievement-type">${achievement.category}</span>
                    </div>
                    <h4>${achievement.title}</h4>
                    <p>${this.truncateText(achievement.description, 150)}</p>
                    <div class="achievement-actions">
                        <button class="btn btn-maroon btn-sm" onclick="achievementsManager.showAchievementModal(${achievement.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }

    /**
     * Show featured achievements
     */
    showFeaturedAchievements() {
        const featuredSection = document.getElementById('featured-section');
        const featuredContainer = document.getElementById('featured-achievements');
        
        if (!featuredContainer) return;
        
        const featuredAchievements = this.achievements.filter(achievement => achievement.featured);
        
        if (featuredAchievements.length === 0) {
            if (featuredSection) featuredSection.style.display = 'none';
            return;
        }
        
        if (featuredSection) featuredSection.style.display = 'block';
        
        featuredContainer.innerHTML = '';
        
        featuredAchievements.slice(0, 2).forEach((achievement, index) => {
            const card = this.createFeaturedCard(achievement, index);
            featuredContainer.appendChild(card);
        });
    }

    /**
     * Create featured achievement card
     */
    createFeaturedCard(achievement, index) {
        const card = document.createElement('div');
        card.className = 'col-lg-6';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', ((index + 1) * 100).toString());
        
        const imageUrl = achievement.image_url || 'assets/images/achievements/default-achievement.jpg';
        const categoryClass = achievement.category.toLowerCase();
        
        card.innerHTML = `
            <div class="featured-achievement-card">
                <div class="achievement-badge">
                    <i class="fas fa-star"></i>
                    Featured
                </div>
                <div class="achievement-image">
                    <img src="${imageUrl}" alt="${achievement.title}">
                    <div class="achievement-category ${categoryClass}">${achievement.category}</div>
                </div>
                <div class="achievement-content">
                    <div class="achievement-meta">
                        <span class="achievement-date">
                            <i class="fas fa-calendar"></i>
                            ${this.formatDate(achievement.date)}
                        </span>
                        <span class="achievement-level">
                            <i class="fas fa-medal"></i>
                            Featured Achievement
                        </span>
                    </div>
                    <h3>${achievement.title}</h3>
                    <p>${this.truncateText(achievement.description, 200)}</p>
                    <div class="achievement-highlights">
                        <span class="highlight-tag">${achievement.category}</span>
                        <span class="highlight-tag">Featured</span>
                    </div>
                    <div class="achievement-actions">
                        <button class="btn btn-maroon" onclick="achievementsManager.showAchievementModal(${achievement.id})">
                            <i class="fas fa-eye"></i>
                            View Details
                        </button>
                        <button class="btn btn-outline-maroon" onclick="achievementsManager.shareAchievement(${achievement.id})">
                            <i class="fas fa-share"></i>
                            Share
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }

    /**
     * Load more achievements
     */
    loadMoreAchievements() {
        if (this.isLoading) return;
        
        this.currentPage++;
        const loadMoreBtn = document.getElementById('load-more-achievements');
        const originalText = loadMoreBtn.innerHTML;
        
        // Show loading state
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;
        
        // Simulate loading delay
        setTimeout(() => {
            this.displayAchievements();
            
            // Reset button
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
        }, 1000);
    }

    /**
     * Update load more button visibility
     */
    updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('load-more-achievements');
        if (!loadMoreBtn) return;
        
        const totalShown = this.currentPage * this.achievementsPerPage;
        const hasMore = totalShown < this.filteredAchievements.length;
        
        loadMoreBtn.style.display = hasMore ? 'inline-flex' : 'none';
    }

    /**
     * Show achievement modal
     */
    showAchievementModal(achievementId) {
        const achievement = this.achievements.find(a => a.id == achievementId);
        if (!achievement) return;
        
        const modal = document.getElementById('achievementModal');
        const modalTitle = document.getElementById('achievementModalLabel');
        const modalBody = document.getElementById('achievementModalBody');
        
        if (!modal || !modalTitle || !modalBody) return;
        
        modalTitle.textContent = achievement.title;
        
        const imageUrl = achievement.image_url || 'assets/images/achievements/default-achievement.jpg';
        
        modalBody.innerHTML = `
            <div class="modal-achievement-content">
                <div class="modal-achievement-image mb-3">
                    <img src="${imageUrl}" alt="${achievement.title}" class="img-fluid rounded">
                </div>
                <div class="modal-achievement-meta mb-3">
                    <span class="badge bg-primary me-2">${achievement.category}</span>
                    <span class="text-muted me-3">
                        <i class="fas fa-calendar"></i> ${this.formatDate(achievement.date)}
                    </span>
                    ${achievement.featured ? '<span class="badge bg-warning"><i class="fas fa-star"></i> Featured</span>' : ''}
                </div>
                <div class="modal-achievement-description">
                    <p>${achievement.description}</p>
                </div>
            </div>
        `;
        
        // Store current achievement for sharing
        this.currentAchievement = achievement;
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    /**
     * Share achievement
     */
    shareAchievement(achievementId) {
        const achievement = this.achievements.find(a => a.id == achievementId);
        if (!achievement) return;
        
        const shareData = {
            title: `Makalanegama School Achievement: ${achievement.title}`,
            text: achievement.description.substring(0, 100) + '...',
            url: window.location.href
        };
        
        if (navigator.share) {
            navigator.share(shareData);
        } else {
            // Fallback - copy to clipboard
            const shareText = `${shareData.title}\n\n${shareData.text}\n\n${shareData.url}`;
            navigator.clipboard.writeText(shareText).then(() => {
                this.showToast('Achievement details copied to clipboard!', 'success');
            }).catch(() => {
                // Fallback for older browsers
                this.showToast('Please copy the link manually: ' + shareData.url, 'info');
            });
        }
    }

    /**
     * Share current achievement from modal
     */
    shareCurrentAchievement() {
        if (this.currentAchievement) {
            this.shareAchievement(this.currentAchievement.id);
        }
    }

    /**
     * Update statistics
     */
    updateStatistics() {
        const stats = this.calculateStats();
        
        // Update header stats
        this.updateStatElement('total-achievements', stats.total);
        this.updateStatElement('featured-count', stats.featured);
        this.updateStatElement('categories-count', stats.categories);
        
        // Update detailed stats
        this.updateStatElement('stat-total', stats.total);
        this.updateStatElement('stat-academic', stats.academic);
        this.updateStatElement('stat-sports', stats.sports);
        this.updateStatElement('stat-featured', stats.featured);
        
        // Animate counters
        this.animateCounters();
    }

    /**
     * Calculate statistics
     */
    calculateStats() {
        const stats = {
            total: this.achievements.length,
            featured: this.achievements.filter(a => a.featured).length,
            academic: this.achievements.filter(a => a.category === 'Academic').length,
            sports: this.achievements.filter(a => a.category === 'Sports').length,
            categories: [...new Set(this.achievements.map(a => a.category))].length
        };
        
        return stats;
    }

    /**
     * Update stat element
     */
    updateStatElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.setAttribute('data-count', value);
            element.textContent = value;
        }
    }

    /**
     * Animate counters
     */
    animateCounters() {
        const counters = document.querySelectorAll('[data-count]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-count'));
            if (isNaN(target)) return;
            
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current);
            }, 16);
        });
    }

    /**
     * Animate cards
     */
    animateCards() {
        if (typeof gsap !== 'undefined') {
            gsap.from('.achievement-card', {
                y: 30,
                opacity: 0,
                duration: 0.6,
                stagger: 0.1,
                ease: "power2.out"
            });
        }
    }

    /**
     * Animate filter change
     */
    animateFilterChange() {
        if (typeof gsap !== 'undefined') {
            const filterButtons = document.querySelectorAll('.filter-btn');
            gsap.from(filterButtons, {
                scale: 0.95,
                duration: 0.2,
                stagger: 0.05,
                ease: "power2.out"
            });
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        if (typeof gsap !== 'undefined') {
            gsap.from(toast, {
                x: 300,
                opacity: 0,
                duration: 0.3,
                ease: "power2.out"
            });
        }
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (typeof gsap !== 'undefined') {
                gsap.to(toast, {
                    x: 300,
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.out",
                    onComplete: () => {
                        if (document.body.contains(toast)) {
                            document.body.removeChild(toast);
                        }
                    }
                });
            } else {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }
        }, 3000);
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return 'Recent';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }

    /**
     * Truncate text to specified length
     */
    truncateText(text, length) {
        if (!text) return '';
        if (text.length <= length) return text;
        return text.substring(0, length).trim() + '...';
    }

    /**
     * Debounce function for search
     */
    debounce(func, wait) {
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
}

// Initialize achievements manager when DOM is loaded
let achievementsManager;

document.addEventListener('DOMContentLoaded', function() {
    achievementsManager = new AchievementsManager();
    achievementsManager.init();
});

// Export for global access
window.AchievementsManager = AchievementsManager;

// Additional helper functions for homepage integration
window.loadLatestAchievements = async function() {
    const container = document.getElementById('latest-achievements');
    const loadingIndicator = document.getElementById('achievements-loading');
    
    if (!container) return;
    
    try {
        // Show loading
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        const response = await fetch('api/achievements.php?limit=3&featured=1');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const achievements = await response.json();
        
        if (Array.isArray(achievements) && achievements.length > 0) {
            updateAchievementsDisplay(achievements);
        } else {
            console.log('No achievements data available');
        }
        
    } catch (error) {
        console.error('Error loading achievements:', error);
        // Keep existing content on error
    } finally {
        // Hide loading
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }
};

// Helper function for homepage
window.updateAchievementsDisplay = function(achievements) {
    const container = document.getElementById('latest-achievements');
    if (!container) return;
    
    // Clear existing content
    container.innerHTML = '';
    
    achievements.slice(0, 3).forEach((achievement, index) => {
        const achievementCard = createHomeAchievementCard(achievement, index);
        container.appendChild(achievementCard);
    });
    
    // Animate new cards
    if (typeof gsap !== 'undefined') {
        gsap.from('#latest-achievements .achievement-card', {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.2,
            ease: "power2.out"
        });
    }
};

// Helper function to create homepage achievement cards
function createHomeAchievementCard(achievement, index) {
    const card = document.createElement('div');
    card.className = 'col-lg-4 col-md-6';
    card.setAttribute('data-aos', 'fade-up');
    card.setAttribute('data-aos-delay', ((index + 1) * 100).toString());
    
    const imageUrl = achievement.image_url || 'assets/images/achievements/default-achievement.jpg';
    const categoryClass = achievement.category.toLowerCase();
    
    card.innerHTML = `
        <div class="achievement-card">
            <div class="achievement-image">
                <img src="${imageUrl}" alt="${achievement.title}" loading="lazy">
                <div class="achievement-category ${categoryClass}">${achievement.category}</div>
            </div>
            <div class="achievement-content">
                <div class="achievement-date">${formatDateForHome(achievement.date)}</div>
                <h4>${achievement.title}</h4>
                <p>${truncateTextForHome(achievement.description, 120)}</p>
                <a href="achievements.html" class="achievement-link">Read More <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    `;
    
    return card;
}

// Helper functions for homepage
function formatDateForHome(dateString) {
    if (!dateString) return 'Recent';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function truncateTextForHome(text, length) {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + '...';
}