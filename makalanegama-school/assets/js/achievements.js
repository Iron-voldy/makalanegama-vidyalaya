/**
 * Achievements Page JavaScript
 * Handles loading and displaying achievements from the database
 */

class AchievementsManager {
    constructor() {
        this.achievements = [];
        this.filteredAchievements = [];
        this.currentFilter = 'all';
        this.currentPage = 1;
        this.achievementsPerPage = 9;
        this.isLoading = false;
        this.searchTerm = '';
        
        this.initializeElements();
        this.bindEvents();
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        this.achievementsGrid = document.getElementById('achievements-grid');
        this.featuredSection = document.getElementById('featured-section');
        this.featuredContainer = document.getElementById('featured-achievements');
        this.loadMoreBtn = document.getElementById('load-more-achievements');
        this.loadingIndicator = document.getElementById('loading-indicator');
        this.noResultsDiv = document.getElementById('no-results');
        this.searchInput = document.getElementById('achievement-search');
        this.filterButtons = document.querySelectorAll('.filter-btn');
        
        // Stats elements
        this.totalAchievementsEl = document.getElementById('total-achievements');
        this.featuredCountEl = document.getElementById('featured-count');
        this.categoriesCountEl = document.getElementById('categories-count');
        this.statTotalEl = document.getElementById('stat-total');
        this.statAcademicEl = document.getElementById('stat-academic');
        this.statSportsEl = document.getElementById('stat-sports');
        this.statFeaturedEl = document.getElementById('stat-featured');
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Filter buttons
        this.filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFilterChange(btn.getAttribute('data-filter'));
            });
        });

        // Search functionality
        if (this.searchInput) {
            this.searchInput.addEventListener('input', this.debounce((e) => {
                this.handleSearch(e.target.value);
            }, 300));
        }

        // Load more button
        if (this.loadMoreBtn) {
            this.loadMoreBtn.addEventListener('click', () => {
                this.loadMoreAchievements();
            });
        }

        // Search button
        const searchBtn = document.getElementById('search-btn');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.handleSearch(this.searchInput.value);
            });
        }
    }

    /**
     * Initialize the achievements manager
     */
    async init() {
        await this.loadAchievements();
        this.updateStatistics();
        this.displayAchievements();
        this.displayFeaturedAchievements();
    }

    /**
     * Load achievements from API
     */
    async loadAchievements() {
        try {
            this.showLoading(true);
            
            const response = await fetch('/api/achievements.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Check if data is valid
            if (Array.isArray(data)) {
                this.achievements = data;
                this.filteredAchievements = [...this.achievements];
                console.log('Loaded achievements from database:', this.achievements.length);
            } else {
                throw new Error('Invalid data format received from API');
            }
            
        } catch (error) {
            console.error('Error loading achievements:', error);
            this.achievements = [];
            this.filteredAchievements = [];
            this.showNoResults(true);
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * No fallback data - removed to use only real MySQL data
     */

    /**
     * Handle filter changes
     */
    handleFilterChange(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        
        // Update active filter button
        this.filterButtons.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
        
        this.applyFilters();
        this.displayAchievements();
        this.animateFilterChange();
    }

    /**
     * Handle search functionality
     */
    handleSearch(searchTerm) {
        this.searchTerm = searchTerm.toLowerCase().trim();
        this.currentPage = 1;
        this.applyFilters();
        this.displayAchievements();
    }

    /**
     * Apply current filters and search
     */
    applyFilters() {
        this.filteredAchievements = this.achievements.filter(achievement => {
            const matchesFilter = this.currentFilter === 'all' || achievement.category === this.currentFilter;
            const matchesSearch = !this.searchTerm || 
                achievement.title.toLowerCase().includes(this.searchTerm) ||
                achievement.description.toLowerCase().includes(this.searchTerm) ||
                achievement.category.toLowerCase().includes(this.searchTerm);
            
            return matchesFilter && matchesSearch;
        });
    }

    /**
     * Display achievements in grid
     */
    displayAchievements() {
        if (!this.achievementsGrid) return;

        const startIndex = 0;
        const endIndex = this.currentPage * this.achievementsPerPage;
        const achievementsToShow = this.filteredAchievements.slice(startIndex, endIndex);

        if (achievementsToShow.length === 0) {
            this.showNoResults(true);
            this.achievementsGrid.innerHTML = '';
            this.updateLoadMoreButton();
            return;
        }

        this.showNoResults(false);
        this.achievementsGrid.innerHTML = '';

        achievementsToShow.forEach((achievement, index) => {
            const achievementCard = this.createAchievementCard(achievement, index);
            this.achievementsGrid.appendChild(achievementCard);
        });

        this.updateLoadMoreButton();
        this.animateCards();
    }

    /**
     * Display featured achievements
     */
    displayFeaturedAchievements() {
        if (!this.featuredContainer) return;

        const featuredAchievements = this.achievements.filter(a => a.featured).slice(0, 3);
        
        if (featuredAchievements.length === 0) {
            this.featuredSection.style.display = 'none';
            return;
        }

        this.featuredSection.style.display = 'block';
        this.featuredContainer.innerHTML = '';

        featuredAchievements.forEach((achievement, index) => {
            const featuredCard = this.createFeaturedCard(achievement, index);
            this.featuredContainer.appendChild(featuredCard);
        });
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
        const formattedDate = this.formatDate(achievement.date);

        card.innerHTML = `
            <div class="achievement-card" onclick="window.achievementsManager.showAchievementModal(${achievement.id})">
                <div class="achievement-image">
                    <img src="${imageUrl}" alt="${achievement.title}" loading="lazy">
                    <div class="achievement-category ${achievement.category.toLowerCase()}">${achievement.category}</div>
                    ${achievement.featured ? '<div class="featured-badge"><i class="fas fa-star"></i></div>' : ''}
                </div>
                <div class="achievement-content">
                    <div class="achievement-date">${formattedDate}</div>
                    <h4>${achievement.title}</h4>
                    <p>${this.truncateText(achievement.description, 120)}</p>
                    <div class="achievement-actions">
                        <button class="btn btn-maroon btn-sm" onclick="event.stopPropagation(); window.achievementsManager.showAchievementModal(${achievement.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="btn btn-outline-maroon btn-sm" onclick="event.stopPropagation(); window.achievementsManager.shareAchievement(${achievement.id})">
                            <i class="fas fa-share"></i> Share
                        </button>
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    /**
     * Create featured achievement card
     */
    createFeaturedCard(achievement, index) {
        const card = document.createElement('div');
        card.className = 'col-lg-4 col-md-6';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 200).toString());

        const imageUrl = achievement.image_url || 'assets/images/achievements/default-achievement.jpg';
        const formattedDate = this.formatDate(achievement.date);

        card.innerHTML = `
            <div class="featured-achievement-card" onclick="window.achievementsManager.showAchievementModal(${achievement.id})">
                <div class="featured-image">
                    <img src="${imageUrl}" alt="${achievement.title}" loading="lazy">
                    <div class="featured-overlay">
                        <div class="featured-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>
                <div class="featured-content">
                    <div class="featured-category">${achievement.category}</div>
                    <h3>${achievement.title}</h3>
                    <p>${this.truncateText(achievement.description, 100)}</p>
                    <div class="featured-date">${formattedDate}</div>
                </div>
            </div>
        `;

        return card;
    }

    /**
     * Show achievement modal with details
     */
    showAchievementModal(achievementId) {
        const achievement = this.achievements.find(a => a.id == achievementId);
        if (!achievement) return;

        const modal = document.getElementById('achievementModal');
        const modalLabel = document.getElementById('achievementModalLabel');
        const modalBody = document.getElementById('achievementModalBody');

        modalLabel.textContent = achievement.title;

        const imageUrl = achievement.image_url || 'assets/images/achievements/default-achievement.jpg';
        const formattedDate = this.formatDate(achievement.date);

        modalBody.innerHTML = `
            <div class="achievement-modal-content">
                <div class="row">
                    <div class="col-md-6">
                        <img src="${imageUrl}" alt="${achievement.title}" class="img-fluid rounded mb-3">
                    </div>
                    <div class="col-md-6">
                        <div class="achievement-details">
                            <div class="badge bg-primary mb-2">${achievement.category}</div>
                            ${achievement.featured ? '<div class="badge bg-warning mb-2 ms-2"><i class="fas fa-star"></i> Featured</div>' : ''}
                            <p class="text-muted mb-3"><i class="fas fa-calendar"></i> ${formattedDate}</p>
                            <p class="achievement-description">${achievement.description}</p>
                            <div class="achievement-stats mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> Achievement ID: ${achievement.id}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Store current achievement for sharing
        this.currentAchievement = achievement;

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
            title: `${achievement.title} - Makalanegama School`,
            text: achievement.description,
            url: window.location.href + `#achievement-${achievement.id}`
        };

        if (navigator.share) {
            navigator.share(shareData).catch(console.error);
        } else {
            // Fallback to copying URL
            const url = shareData.url;
            navigator.clipboard.writeText(url).then(() => {
                this.showToast('Achievement link copied to clipboard!');
            }).catch(() => {
                // Final fallback
                prompt('Copy this link to share the achievement:', url);
            });
        }
    }

    /**
     * Load more achievements
     */
    loadMoreAchievements() {
        if (this.isLoading) return;

        this.isLoading = true;
        this.currentPage++;

        const startIndex = (this.currentPage - 1) * this.achievementsPerPage;
        const endIndex = this.currentPage * this.achievementsPerPage;
        const newAchievements = this.filteredAchievements.slice(startIndex, endIndex);

        newAchievements.forEach((achievement, index) => {
            const achievementCard = this.createAchievementCard(achievement, index);
            this.achievementsGrid.appendChild(achievementCard);
        });

        this.updateLoadMoreButton();
        this.animateCards();
        this.isLoading = false;
    }

    /**
     * Update load more button visibility
     */
    updateLoadMoreButton() {
        if (!this.loadMoreBtn) return;

        const totalShown = this.currentPage * this.achievementsPerPage;
        const hasMore = totalShown < this.filteredAchievements.length;

        this.loadMoreBtn.style.display = hasMore ? 'inline-flex' : 'none';
    }

    /**
     * Update statistics
     */
    updateStatistics() {
        const total = this.achievements.length;
        const featured = this.achievements.filter(a => a.featured).length;
        const categories = [...new Set(this.achievements.map(a => a.category))].length;
        const academic = this.achievements.filter(a => a.category === 'Academic').length;
        const sports = this.achievements.filter(a => a.category === 'Sports').length;

        // Update header stats
        this.animateCounter(this.totalAchievementsEl, total);
        this.animateCounter(this.featuredCountEl, featured);
        this.animateCounter(this.categoriesCountEl, categories);

        // Update section stats
        this.animateCounter(this.statTotalEl, total);
        this.animateCounter(this.statAcademicEl, academic);
        this.animateCounter(this.statSportsEl, sports);
        this.animateCounter(this.statFeaturedEl, featured);
    }

    /**
     * Animate counter from 0 to target value
     */
    animateCounter(element, target) {
        if (!element) return;

        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 50);
    }

    /**
     * Show/hide loading indicator
     */
    showLoading(show) {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = show ? 'block' : 'none';
        }
    }

    /**
     * Show/hide no results message
     */
    showNoResults(show) {
        if (this.noResultsDiv) {
            this.noResultsDiv.style.display = show ? 'block' : 'none';
        }
    }

    /**
     * Animate cards on display
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
            gsap.from('.filter-btn', {
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
    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 1rem;
            border-radius: 5px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
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
    truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength).trim() + '...';
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
document.addEventListener('DOMContentLoaded', function() {
    window.achievementsManager = new AchievementsManager();
});

// Handle share achievement button in modal
document.addEventListener('DOMContentLoaded', function() {
    const shareBtn = document.getElementById('shareAchievementBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            if (window.achievementsManager && window.achievementsManager.currentAchievement) {
                window.achievementsManager.shareAchievement(window.achievementsManager.currentAchievement.id);
            }
        });
    }
});