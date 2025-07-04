/**
 * News Page JavaScript
 * Handles news filtering, searching, modal functionality, and newsletter subscription
 */

class NewsManager {
    constructor() {
        this.newsData = [];
        this.filteredNews = [];
        this.currentFilter = 'all';
        this.currentPage = 1;
        this.newsPerPage = 8;
        this.isLoading = false;
        
        this.initializeFilters();
        this.initializeSearch();
        this.initializeLoadMore();
        this.initializeModal();
        this.initializeNewsletter();
    }

    /**
     * Initialize filter functionality
     */
    initializeFilters() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        
        filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all buttons
                filterButtons.forEach(button => button.classList.remove('active'));
                
                // Add active class to clicked button
                btn.classList.add('active');
                
                // Get filter value
                const filter = btn.getAttribute('data-filter');
                this.filterNews(filter);
                
                // Animate filter change
                this.animateFilterChange();
            });
        });
    }

    /**
     * Filter news by category
     */
    filterNews(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        
        if (filter === 'all') {
            this.filteredNews = [...this.newsData];
        } else {
            this.filteredNews = this.newsData.filter(news => {
                return news.category.toLowerCase() === filter.toLowerCase();
            });
        }
        
        this.displayNews();
        this.updateLoadMoreButton();
    }

    /**
     * Initialize search functionality
     */
    initializeSearch() {
        const searchInput = document.getElementById('news-search');
        const searchBtn = document.getElementById('search-btn');
        
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                this.searchNews(e.target.value);
            }, 300));
        }
        
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.searchNews(searchInput.value);
            });
        }
    }

    /**
     * Search news articles
     */
    searchNews(query) {
        const searchTerm = query.toLowerCase().trim();
        
        if (!searchTerm) {
            this.filteredNews = [...this.newsData];
        } else {
            this.filteredNews = this.newsData.filter(news => {
                return news.title.toLowerCase().includes(searchTerm) ||
                       news.content.toLowerCase().includes(searchTerm) ||
                       news.category.toLowerCase().includes(searchTerm) ||
                       news.author.toLowerCase().includes(searchTerm);
            });
        }
        
        this.currentPage = 1;
        this.displayNews();
        this.updateLoadMoreButton();
    }

    /**
     * Display news articles
     */
    displayNews() {
        const newsGrid = document.getElementById('news-grid');
        if (!newsGrid) return;
        
        // Get news to show
        const startIndex = (this.currentPage - 1) * this.newsPerPage;
        const endIndex = startIndex + this.newsPerPage;
        const newsToShow = this.filteredNews.slice(0, endIndex);
        
        // Clear existing news
        newsGrid.innerHTML = '';
        
        // Add news articles
        newsToShow.forEach((news, index) => {
            const newsCard = this.createNewsCard(news, index);
            newsGrid.appendChild(newsCard);
        });
        
        // Animate cards
        gsap.from('.news-card', {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: "power2.out"
        });
    }

    /**
     * Create news card element
     */
    createNewsCard(news, index) {
        const card = document.createElement('div');
        card.className = 'col-lg-6 col-md-6';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        // Handle image URL properly (matching events.js pattern)
        let imageUrl = '/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg';
        if (news.image_url && news.image_url.trim() !== '') {
            if (news.image_url.startsWith('http')) {
                imageUrl = news.image_url;
            } else {
                imageUrl = `/makalanegama-school/makalanegama-school/${news.image_url}`;
            }
        } else if (news.image) {
            imageUrl = news.image;
        }
        
        // Handle date field (API returns 'created_at' or 'date')
        const displayDate = news.date || news.created_at;
        
        card.innerHTML = `
            <article class="news-card">
                <div class="news-image">
                    <img src="${imageUrl}" alt="${this.escapeHtml(news.title)}" onerror="this.src='/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg'">
                    <div class="news-category ${news.category.toLowerCase()}">${this.escapeHtml(news.category)}</div>
                </div>
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-date">${this.formatDate(displayDate)}</span>
                        <span class="news-author">${this.escapeHtml(news.author)}</span>
                    </div>
                    <h4>${this.escapeHtml(news.title)}</h4>
                    <p>${this.escapeHtml(news.excerpt || news.content.substring(0, 150) + '...')}</p>
                    <a href="#" class="news-link" data-bs-toggle="modal" data-bs-target="#newsModal" data-news-id="${news.id}">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
        `;
        
        return card;
    }

    /**
     * Initialize load more functionality
     */
    initializeLoadMore() {
        const loadMoreBtn = document.getElementById('load-more-news');
        
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreNews();
            });
        }
    }

    /**
     * Load more news articles
     */
    loadMoreNews() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.currentPage++;
        
        const loadMoreBtn = document.getElementById('load-more-news');
        const originalText = loadMoreBtn.innerHTML;
        
        // Show loading state
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;
        
        // Simulate loading delay
        setTimeout(() => {
            this.displayNews();
            this.updateLoadMoreButton();
            
            // Reset button
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
            this.isLoading = false;
        }, 1000);
    }

    /**
     * Update load more button visibility
     */
    updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('load-more-news');
        if (!loadMoreBtn) return;
        
        const totalShown = this.currentPage * this.newsPerPage;
        const hasMore = totalShown < this.filteredNews.length;
        
        if (hasMore) {
            loadMoreBtn.style.display = 'inline-flex';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }

    /**
     * Initialize news modal
     */
    initializeModal() {
        const modal = document.getElementById('newsModal');
        if (!modal) return;
        
        modal.addEventListener('show.bs.modal', (e) => {
            const button = e.relatedTarget;
            const newsId = button.getAttribute('data-news-id');
            this.loadNewsContent(newsId);
        });
    }

    /**
     * Load news content into modal
     */
    loadNewsContent(newsId) {
        const news = this.getNewsById(newsId) || this.getSampleNews(newsId);
        if (!news) return;
        
        const modalBody = document.getElementById('newsModalBody');
        const modalTitle = document.getElementById('newsModalLabel');
        
        modalTitle.textContent = news.title;
        
        // Handle image URL properly
        let imageUrl = '/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg';
        if (news.image_url && news.image_url.trim() !== '') {
            if (news.image_url.startsWith('http')) {
                imageUrl = news.image_url;
            } else {
                imageUrl = `/makalanegama-school/makalanegama-school/${news.image_url}`;
            }
        } else if (news.image) {
            imageUrl = news.image;
        }
        
        // Handle date field (API returns 'created_at' or 'date')
        const displayDate = news.date || news.created_at;
        
        modalBody.innerHTML = `
            <div class="modal-news-image">
                <img src="${imageUrl}" alt="${this.escapeHtml(news.title)}" class="img-fluid rounded mb-3" onerror="this.style.display='none'">
            </div>
            <div class="modal-news-meta mb-3">
                <span class="badge bg-secondary me-2">${this.escapeHtml(news.category)}</span>
                <span class="text-muted me-3"><i class="fas fa-calendar"></i> ${this.formatDate(displayDate)}</span>
                <span class="text-muted me-3"><i class="fas fa-user"></i> ${this.escapeHtml(news.author)}</span>
                <span class="text-muted"><i class="fas fa-clock"></i> ${this.calculateReadTime(news.content)} min read</span>
            </div>
            <div class="modal-news-content">
                ${this.formatNewsContent(news.content)}
            </div>
            ${news.tags ? `
                <div class="modal-news-tags mt-4">
                    <strong>Tags:</strong>
                    ${news.tags.map(tag => `<span class="badge bg-secondary me-1">${this.escapeHtml(tag)}</span>`).join('')}
                </div>
            ` : ''}
        `;
        
        // Update share buttons
        this.updateShareButtons(news);
    }

    /**
     * Get news by ID
     */
    getNewsById(id) {
        return this.newsData.find(news => news.id == id);
    }

    /**
     * Get sample news for demo
     */
    getSampleNews(id) {
        const sampleNews = {
            '1': {
                id: 1,
                title: 'New Computer Lab Officially Opens with Modern Technology',
                content: `We are proud to announce the official opening of our state-of-the-art computer laboratory, made possible through the generous support of Wire Academy & Technology for Village. 
                
                The new facility features 25 modern computers equipped with the latest software and high-speed internet connectivity, providing our students with access to cutting-edge technology for learning and skill development.

                Principal Mrs. Nirmala Perera stated, "This computer lab represents a significant step forward in our commitment to providing quality education that prepares our students for the digital age. We are grateful to Wire Academy & Technology for Village for their invaluable support."

                The lab will be used for computer literacy classes, programming courses, and various educational projects. Students from grades 6 to 11 will have regular access to the facility as part of their curriculum.

                We believe this investment in technology will greatly enhance our students' learning experience and better prepare them for future academic and career opportunities in an increasingly digital world.`,
                category: 'Facilities',
                author: 'Principal',
                date: '2024-02-10',
                image: 'assets/images/news/computer-lab-featured.jpg',
                tags: ['Technology', 'Education', 'Digital Literacy'],
                readTime: '3 min read'
            },
            '2': {
                id: 2,
                title: '2024 Grade 1 Admissions Now Open',
                content: `Applications for Grade 1 admissions for the 2024 academic year are now being accepted at Makalanegama School. Parents and guardians are encouraged to visit the school office to collect application forms and learn about the admission requirements.

                The admission process follows the guidelines set by the Ministry of Education and the North Western Provincial Education Department. Priority is given to students residing within the school zone.

                Required documents for application include:
                - Birth certificate of the child
                - Proof of residence
                - Immunization records
                - Recent passport-size photographs

                Application deadline is March 31, 2024. For more information, parents can contact the school office during weekdays from 8:00 AM to 2:00 PM.

                We welcome new students to join our school community and experience quality education in a nurturing environment.`,
                category: 'Admissions',
                author: 'Admissions Office',
                date: '2024-02-05',
                image: 'assets/images/news/admissions-2024.jpg',
                tags: ['Admissions', 'Grade 1', 'Registration'],
                readTime: '2 min read'
            },
            '3': {
                id: 3,
                title: 'Professional Development Workshop for Teachers',
                content: `Our dedicated teaching staff recently participated in a comprehensive professional development workshop focusing on modern teaching methodologies and digital integration in education.

                The two-day workshop was conducted by education experts from the National Institute of Education and covered various topics including:
                - Interactive teaching techniques
                - Digital classroom management
                - Student assessment strategies
                - Inclusive education practices

                All 25 teachers attended the workshop and received certificates of completion. The training was part of our ongoing commitment to providing the highest quality education to our students.

                Vice Principal Mr. Sunil Bandara commented, "These workshops are essential for keeping our teaching methods current and effective. Our teachers are now better equipped to engage students and enhance the learning experience."

                The school plans to conduct similar training sessions quarterly to ensure continuous professional development for all staff members.`,
                category: 'Academic',
                author: 'Academic Coordinator',
                date: '2024-01-28',
                image: 'assets/images/news/teacher-training.jpg',
                tags: ['Professional Development', 'Teachers', 'Training'],
                readTime: '3 min read'
            }
        };
        
        return sampleNews[id] || null;
    }

    /**
     * Format news content for display
     */
    formatNewsContent(content) {
        return content.split('\n\n').map(paragraph => {
            if (paragraph.trim()) {
                return `<p>${paragraph.trim()}</p>`;
            }
        }).join('');
    }

    /**
     * Update share buttons
     */
    updateShareButtons(news) {
        const shareButtons = {
            facebook: document.getElementById('share-facebook'),
            twitter: document.getElementById('share-twitter'),
            whatsapp: document.getElementById('share-whatsapp')
        };
        
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(news.title);
        const text = encodeURIComponent(news.excerpt || news.content.substring(0, 100) + '...');
        
        if (shareButtons.facebook) {
            shareButtons.facebook.href = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        }
        
        if (shareButtons.twitter) {
            shareButtons.twitter.href = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
        }
        
        if (shareButtons.whatsapp) {
            shareButtons.whatsapp.href = `https://wa.me/?text=${title}%20${url}`;
        }
    }

    /**
     * Initialize newsletter subscription
     */
    initializeNewsletter() {
        const newsletterForm = document.getElementById('newsletter-form');
        
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleNewsletterSubmission(e.target);
            });
        }
    }

    /**
     * Handle newsletter form submission
     */
    handleNewsletterSubmission(form) {
        const formData = new FormData(form);
        const name = formData.get('name') || form.querySelector('input[type="text"]').value;
        const email = formData.get('email') || form.querySelector('input[type="email"]').value;
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Reset form
            form.reset();
            
            // Show success message
            this.showNewsletterSuccess();
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    }

    /**
     * Show newsletter success message
     */
    showNewsletterSuccess() {
        const toast = document.createElement('div');
        toast.className = 'toast-notification success';
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-check-circle"></i>
                <span>Successfully subscribed to newsletter!</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        gsap.from(toast, {
            x: 300,
            opacity: 0,
            duration: 0.3,
            ease: "power2.out"
        });
        
        // Remove after 3 seconds
        setTimeout(() => {
            gsap.to(toast, {
                x: 300,
                opacity: 0,
                duration: 0.3,
                ease: "power2.out",
                onComplete: () => {
                    document.body.removeChild(toast);
                }
            });
        }, 3000);
    }

    /**
     * Animate filter change
     */
    animateFilterChange() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        
        gsap.from(filterButtons, {
            scale: 0.95,
            duration: 0.2,
            stagger: 0.05,
            ease: "power2.out"
        });
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
     * Calculate reading time for article
     */
    calculateReadTime(content) {
        const wordsPerMinute = 200;
        const words = content.trim().split(/\s+/).length;
        const readTime = Math.ceil(words / wordsPerMinute);
        return readTime;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>\"']/g, (m) => map[m]);
    }

    /**
     * Render featured news section with sidebar
     */
    renderFeaturedNews() {
        const featuredContainer = document.getElementById('featured-news');
        if (!featuredContainer) return;
        
        const featuredNews = this.allNews.filter(news => news.featured || news.is_featured);
        const recentNews = this.allNews.slice(0, 3); // Get latest 3 news for sidebar
        
        // Clear existing content
        featuredContainer.innerHTML = '';
        
        if (featuredNews.length > 0) {
            // Create main featured article
            const featuredElement = this.createFeaturedNewsElement(featuredNews[0]);
            featuredContainer.appendChild(featuredElement);
        } else if (this.allNews.length > 0) {
            // If no featured news, use the first news item as featured
            const featuredElement = this.createFeaturedNewsElement(this.allNews[0]);
            featuredContainer.appendChild(featuredElement);
        }
        
        // Create sidebar with recent updates
        const sidebarElement = this.createSidebarElement(recentNews);
        featuredContainer.appendChild(sidebarElement);
    }

    /**
     * Create featured news element for main display
     */
    createFeaturedNewsElement(news) {
        const newsDate = new Date(news.date || news.created_at);
        
        let imageUrl = '/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg';
        if (news.image_url && news.image_url.trim() !== '') {
            if (news.image_url.startsWith('http')) {
                imageUrl = news.image_url;
            } else {
                imageUrl = `/makalanegama-school/makalanegama-school/${news.image_url}`;
            }
        }
        
        const featuredElement = document.createElement('div');
        featuredElement.className = 'col-lg-8';
        featuredElement.setAttribute('data-aos', 'fade-up');
        featuredElement.setAttribute('data-aos-delay', '100');
        
        featuredElement.innerHTML = `
            <article class="featured-news-card">
                <div class="news-image">
                    <img src="${imageUrl}" alt="${this.escapeHtml(news.title)}" onerror="this.src='/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg'">
                    <div class="news-badge featured">Featured</div>
                    <div class="news-category">${this.escapeHtml(news.category)}</div>
                </div>
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-date">
                            <i class="fas fa-calendar"></i>
                            ${newsDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                        </span>
                        <span class="news-author">
                            <i class="fas fa-user"></i>
                            ${this.escapeHtml(news.author)}
                        </span>
                        <span class="news-read-time">
                            <i class="fas fa-clock"></i>
                            ${this.calculateReadTime(news.content)} min read
                        </span>
                    </div>
                    <h2>${this.escapeHtml(news.title)}</h2>
                    <p>${this.escapeHtml(news.excerpt || this.truncateText(news.content, 200))}</p>
                    <div class="news-tags">
                        <span class="tag">${this.escapeHtml(news.category)}</span>
                        <span class="tag">School News</span>
                    </div>
                    <a href="#" class="btn btn-maroon" onclick="newsManager.showNewsModal(${news.id})">
                        Read Full Article <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
        `;
        
        return featuredElement;
    }

    /**
     * Create sidebar element with recent updates
     */
    createSidebarElement(recentNews) {
        const sidebarElement = document.createElement('div');
        sidebarElement.className = 'col-lg-4';
        
        let sidebarItems = '';
        recentNews.slice(0, 3).forEach((news, index) => {
            const newsDate = new Date(news.date || news.created_at);
            
            let imageUrl = '/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg';
            if (news.image_url && news.image_url.trim() !== '') {
                if (news.image_url.startsWith('http')) {
                    imageUrl = news.image_url;
                } else {
                    imageUrl = `/makalanegama-school/makalanegama-school/${news.image_url}`;
                }
            }
            
            sidebarItems += `
                <div class="sidebar-news-item" data-aos="fade-up" data-aos-delay="${(index + 3) * 100}">
                    <div class="news-item-image">
                        <img src="${imageUrl}" alt="${this.escapeHtml(news.title)}" onerror="this.src='/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg'">
                    </div>
                    <div class="news-item-content">
                        <div class="news-category">${this.escapeHtml(news.category)}</div>
                        <h6>${this.escapeHtml(news.title)}</h6>
                        <div class="news-date">${newsDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                        <p>${this.escapeHtml(this.truncateText(news.excerpt || news.content, 80))}...</p>
                        <a href="#" class="read-more-link" onclick="newsManager.showNewsModal(${news.id})">Read More</a>
                    </div>
                </div>
            `;
        });
        
        sidebarElement.innerHTML = `
            <div class="sidebar-news">
                <h4 class="sidebar-title" data-aos="fade-up" data-aos-delay="200">Recent Updates</h4>
                ${sidebarItems}
            </div>
        `;
        
        return sidebarElement;
    }

    /**
     * Truncate text to specified length
     */
    truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength);
    }

    /**
     * Show news modal with content
     */
    showNewsModal(newsId) {
        const news = this.getNewsById(newsId) || this.getSampleNews(newsId);
        if (!news) return;
        
        const modal = document.getElementById('newsModal');
        const modalTitle = document.getElementById('newsModalLabel');
        const modalBody = document.getElementById('newsModalBody');
        
        if (!modal || !modalTitle || !modalBody) return;
        
        modalTitle.textContent = news.title;
        
        // Handle image URL properly
        let imageUrl = '/makalanegama-school/makalanegama-school/assets/images/news/default-news.jpg';
        if (news.image_url && news.image_url.trim() !== '') {
            if (news.image_url.startsWith('http')) {
                imageUrl = news.image_url;
            } else {
                imageUrl = `/makalanegama-school/makalanegama-school/${news.image_url}`;
            }
        } else if (news.image) {
            imageUrl = news.image;
        }
        
        // Handle date field (API returns 'created_at' or 'date')
        const displayDate = news.date || news.created_at;
        
        modalBody.innerHTML = `
            <div class="modal-news-content">
                <div class="news-meta mb-3">
                    <span class="badge bg-secondary me-2">${this.escapeHtml(news.category)}</span>
                    <span class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        ${new Date(displayDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                    </span>
                    <span class="text-muted ms-3">
                        <i class="fas fa-user me-1"></i>
                        ${this.escapeHtml(news.author)}
                    </span>
                    <span class="text-muted ms-3">
                        <i class="fas fa-clock me-1"></i>
                        ${this.calculateReadTime(news.content)} min read
                    </span>
                </div>
                
                ${news.image_url || news.image ? `
                <div class="news-image mb-4">
                    <img src="${imageUrl}" alt="${this.escapeHtml(news.title)}" class="img-fluid rounded" onerror="this.style.display='none'">
                </div>
                ` : ''}
                
                <div class="news-body">
                    ${this.formatNewsContent(news.content)}
                </div>
            </div>
        `;
        
        // Setup share buttons
        this.updateShareButtons(news);
        
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }

    /**
     * Load news data
     */
    async loadNews() {
        try {
            // Use absolute path from the root directory (matching events.js pattern)
            const response = await fetch('/makalanegama-school/makalanegama-school/api/news.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (Array.isArray(data) && data.length > 0) {
                this.newsData = data;
                console.log('News loaded successfully:', data.length, 'articles');
            } else {
                console.warn('No news data received, using fallback');
                throw new Error('No news data available');
            }
        } catch (error) {
            console.error('Error loading news:', error);
            // Use fallback data
            this.newsData = this.getFallbackNewsData();
        }
        
        this.filteredNews = [...this.newsData];
        this.displayNews();
        this.updateLoadMoreButton();
    }

    /**
     * Get fallback news data
     */
    getFallbackNewsData() {
        return [
            {
                id: 5,
                title: 'Cricket Team Wins Zonal Championship',
                content: 'Our school cricket team secured first place in the zonal inter-school tournament after a thrilling final match against St. Joseph\'s College.',
                category: 'Sports',
                author: 'Sports Coordinator',
                date: '2024-01-15',
                image: 'assets/images/news/sports-achievement.jpg',
                tags: ['Cricket', 'Sports', 'Championship']
            },
            {
                id: 6,
                title: 'Annual Cultural Festival Celebrates Heritage',
                content: 'Students showcased their talents in traditional music, dance, and drama performances, celebrating Sri Lankan cultural heritage.',
                category: 'Cultural',
                author: 'Arts Department',
                date: '2024-01-10',
                image: 'assets/images/news/cultural-festival.jpg',
                tags: ['Culture', 'Festival', 'Heritage']
            },
            {
                id: 7,
                title: 'Science Fair Showcases Student Innovation',
                content: 'Students demonstrated innovative science projects focusing on renewable energy and environmental sustainability at our annual science fair.',
                category: 'Academic',
                author: 'Science Department',
                date: '2023-12-20',
                image: 'assets/images/news/science-fair.jpg',
                tags: ['Science', 'Innovation', 'Projects']
            },
            {
                id: 8,
                title: 'Successful Parent-Teacher Meeting Held',
                content: 'The quarterly parent-teacher meeting was successfully conducted with high attendance and positive feedback from parents.',
                category: 'General',
                author: 'Administration',
                date: '2023-12-15',
                image: 'assets/images/news/parent-meeting.jpg',
                tags: ['Parents', 'Meeting', 'Communication']
            }
        ];
    }
}

/**
 * Initialize news page functionality
 */
function initializeNewsFilters() {
    if (!window.newsManager) {
        const newsManager = new NewsManager();
        newsManager.loadNews();
        
        // Make newsManager globally available
        window.newsManager = newsManager;
    }
}

function initializeNewsSearch() {
    // Already handled in NewsManager constructor
}

function initializeNewsModal() {
    // Already handled in NewsManager constructor
}

function initializeNewsletterForm() {
    // Already handled in NewsManager constructor
}

/**
 * Debounce function for search
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

// CSS for toast notifications
const toastStyles = `
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--success);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-medium);
    box-shadow: var(--shadow-heavy);
    z-index: 9999;
    max-width: 300px;
}

.toast-notification.success {
    background: var(--success);
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toast-content i {
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .toast-notification {
        right: 10px;
        left: 10px;
        max-width: none;
    }
}
`;

// Add toast styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = toastStyles;
document.head.appendChild(styleSheet);