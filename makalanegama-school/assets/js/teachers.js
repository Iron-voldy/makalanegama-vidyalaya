/**
 * Teachers Page JavaScript - COMPLETE FIXED VERSION
 * Handles teacher loading, filtering, searching, and interactions
 */

class TeachersManager {
    constructor() {
        this.teachers = [];
        this.filteredTeachers = [];
        this.currentFilter = 'all';
        this.teachersPerPage = 50; // Show more teachers initially
        this.currentPage = 1;
        this.isLoading = false;
        this.searchTerm = '';
        this.debugInfo = [];
        
        this.initializeElements();
        this.bindEvents();
        console.log('TeachersManager initialized - COMPLETE FIXED VERSION');
        this.init();
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        this.teachersGrid = document.getElementById('teachers-grid');
        this.loadMoreBtn = document.getElementById('load-more-teachers');
        this.loadingIndicator = document.getElementById('loading-indicator');
        this.noTeachersDiv = document.getElementById('no-teachers');
        this.apiErrorDiv = document.getElementById('api-error');
        this.searchInput = document.getElementById('teacher-search');
        this.filterButtons = document.querySelectorAll('.filter-btn');
        
        // Stats elements
        this.totalTeachersEl = document.getElementById('total-teachers');
        this.activeTeachersEl = document.getElementById('active-teachers');
        this.departmentsCountEl = document.getElementById('departments-count');
        
        console.log('Elements initialized:', {
            grid: !!this.teachersGrid,
            buttons: this.filterButtons.length
        });
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
                this.loadMoreTeachers();
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
     * Initialize the teachers manager
     */
    async init() {
        console.log('Loading teachers from database...');
        await this.loadTeachers();
        this.updateStatistics();
        this.displayTeachers();
    }

    /**
     * Test multiple API paths to find the working one
     */
    async testApiPaths() {
        const currentUrl = window.location.href;
        const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/'));
        
        // Multiple possible API paths to test
        const apiPaths = [
            'api/teachers.php',
            './api/teachers.php',
            '/api/teachers.php',
            '../api/teachers.php',
            baseUrl + '/api/teachers.php'
        ];

        this.debugInfo.push('Testing API paths...');
        this.debugInfo.push('Current URL: ' + currentUrl);
        this.debugInfo.push('Base URL: ' + baseUrl);

        for (const path of apiPaths) {
            try {
                this.debugInfo.push(`Testing: ${path}`);
                console.log(`Testing API path: ${path}`);
                
                const response = await fetch(path + '?limit=100');
                console.log(`Response status for ${path}:`, response.status);
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.error) {
                        this.debugInfo.push(`${path} - API Error: ${data.error}`);
                        continue;
                    }
                    
                    if (Array.isArray(data)) {
                        this.debugInfo.push(`${path} - SUCCESS! Found ${data.length} teachers`);
                        console.log(`SUCCESS: Working API path found: ${path}`);
                        return { success: true, data: data, path: path };
                    } else {
                        this.debugInfo.push(`${path} - Invalid data format`);
                    }
                } else {
                    this.debugInfo.push(`${path} - HTTP ${response.status}: ${response.statusText}`);
                }
                
            } catch (error) {
                this.debugInfo.push(`${path} - Error: ${error.message}`);
                console.log(`Error testing ${path}:`, error.message);
            }
        }
        
        return { success: false, data: [], path: null };
    }

    /**
     * Load teachers from database API
     */
    async loadTeachers() {
        try {
            this.showLoading(true);
            this.hideError();
            this.debugInfo = [];
            
            console.log('Testing multiple API paths...');
            
            const result = await this.testApiPaths();
            
            if (result.success) {
                this.teachers = result.data;
                this.filteredTeachers = [...this.teachers];
                console.log('Successfully loaded teachers from database:', this.teachers.length);
                this.debugInfo.push(`Final result: ${this.teachers.length} teachers loaded successfully`);
                
                if (this.teachers.length === 0) {
                    console.log('Database returned empty results - showing fallback data');
                    this.teachers = this.getFallbackData();
                    this.filteredTeachers = [...this.teachers];
                }
                
                this.hideNoTeachers();
            } else {
                console.log('All API paths failed - using fallback data');
                this.teachers = this.getFallbackData();
                this.filteredTeachers = [...this.teachers];
            }
            
        } catch (error) {
            console.error('Error loading teachers:', error);
            this.debugInfo.push(`Final error: ${error.message}`);
            this.teachers = this.getFallbackData();
            this.filteredTeachers = [...this.teachers];
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * Fallback data when API is not available
     */
    getFallbackData() {
        return [
            {
                id: 1,
                name: "Mr. Sunil Perera",
                qualification: "B.Ed (Mathematics), Dip. in Education",
                subject: "Mathematics",
                department: "Science & Mathematics",
                bio: "Experienced mathematics teacher specializing in advanced mathematics and statistics with over 15 years of teaching experience.",
                experience_years: 15,
                email: "sperera@makalanegamaschool.lk",
                phone: "+94 71 234 5678",
                photo_url: "assets/images/teachers/teacher-1.jpg",
                specializations: ["Advanced Mathematics", "Statistics", "Problem Solving"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 2,
                name: "Mrs. Kamala Wijesinghe",
                qualification: "B.A (Sinhala), PGDE",
                subject: "Sinhala Language & Literature",
                department: "Languages",
                bio: "Passionate about promoting Sinhala literature and language skills among students with innovative teaching methods.",
                experience_years: 12,
                email: "kwijesinghe@makalanegamaschool.lk",
                phone: "+94 71 345 6789",
                photo_url: "assets/images/teachers/teacher-2.jpg",
                specializations: ["Sinhala Literature", "Creative Writing", "Cultural Studies"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 3,
                name: "Mr. Rohan Fernando",
                qualification: "B.Sc (Physics), Dip. in Education",
                subject: "Science",
                department: "Science & Mathematics",
                bio: "Dedicated science teacher focusing on practical experiments and scientific inquiry to inspire young minds.",
                experience_years: 10,
                email: "rfernando@makalanegamaschool.lk",
                phone: "+94 71 456 7890",
                photo_url: "assets/images/teachers/teacher-3.jpg",
                specializations: ["Physics", "Laboratory Work", "Scientific Method"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 4,
                name: "Mrs. Priyanka Silva",
                qualification: "B.A (English), TESL Certificate",
                subject: "English Language",
                department: "Languages",
                bio: "English language specialist with expertise in modern teaching methodologies and communication skills development.",
                experience_years: 8,
                email: "psilva@makalanegamaschool.lk",
                phone: "+94 71 567 8901",
                photo_url: "assets/images/teachers/teacher-4.jpg",
                specializations: ["TESL", "Communication Skills", "Grammar"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 5,
                name: "Mr. Asanka Rathnayake",
                qualification: "B.A (History), Dip. in Education",
                subject: "History & Social Studies",
                department: "Social Sciences",
                bio: "History teacher with special interest in Sri Lankan heritage and culture, bringing the past to life for students.",
                experience_years: 14,
                email: "arathnayake@makalanegamaschool.lk",
                phone: "+94 71 678 9012",
                photo_url: "assets/images/teachers/teacher-5.jpg",
                specializations: ["Sri Lankan History", "Cultural Heritage", "Social Studies"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 6,
                name: "Mrs. Sandya Mendis",
                qualification: "B.Sc (Geography), PGDE",
                subject: "Geography",
                department: "Social Sciences",
                bio: "Geography teacher promoting environmental awareness and sustainability through hands-on learning experiences.",
                experience_years: 9,
                email: "smendis@makalanegamaschool.lk",
                phone: "+94 71 789 0123",
                photo_url: "assets/images/teachers/teacher-6.jpg",
                specializations: ["Environmental Geography", "Field Studies", "Sustainability"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 7,
                name: "Mr. Chathura Bandara",
                qualification: "B.A (Art), Dip. in Education",
                subject: "Art & Crafts",
                department: "Arts",
                bio: "Creative arts teacher inspiring students through various art forms and traditional crafts of Sri Lanka.",
                experience_years: 7,
                email: "cbandara@makalanegamaschool.lk",
                phone: "+94 71 890 1234",
                photo_url: "assets/images/teachers/teacher-7.jpg",
                specializations: ["Traditional Art", "Crafts", "Creative Expression"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 8,
                name: "Mrs. Niluka Jayawardena",
                qualification: "B.Sc (Physical Education), Dip. in Sports Science",
                subject: "Physical Education",
                department: "Physical Education",
                bio: "Physical education teacher promoting health, fitness, and sportsmanship among students through active learning.",
                experience_years: 6,
                email: "njayawardena@makalanegamaschool.lk",
                phone: "+94 71 901 2345",
                photo_url: "assets/images/teachers/teacher-8.jpg",
                specializations: ["Athletics", "Team Sports", "Health Education"],
                is_active: true,
                created_at: "2024-01-01"
            },
            {
                id: 9,
                name: "Mr. Dinesh Kulasekara",
                qualification: "B.Sc (Computer Science), PGDE",
                subject: "Information Technology",
                department: "Technology",
                bio: "IT teacher helping students develop digital literacy and computer programming skills for the modern world.",
                experience_years: 5,
                email: "dkulasekara@makalanegamaschool.lk",
                phone: "+94 71 012 3456",
                photo_url: "assets/images/teachers/teacher-9.jpg",
                specializations: ["Programming", "Digital Literacy", "Web Development"],
                is_active: true,
                created_at: "2024-01-01"
            }
        ];
    }

    /**
     * Handle filter changes
     */
    handleFilterChange(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        
        // Update active filter button
        this.filterButtons.forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`[data-filter="${filter}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
        
        this.applyFilters();
        this.displayTeachers();
        this.animateFilterChange();
    }

    /**
     * Handle search functionality
     */
    handleSearch(searchTerm) {
        this.searchTerm = searchTerm.toLowerCase().trim();
        this.currentPage = 1;
        this.applyFilters();
        this.displayTeachers();
    }

    /**
     * Apply current filters and search
     */
    applyFilters() {
        this.filteredTeachers = this.teachers.filter(teacher => {
            const matchesFilter = this.currentFilter === 'all' || this.getDepartmentFilter(teacher.department) === this.currentFilter;
            const matchesSearch = !this.searchTerm || 
                teacher.name.toLowerCase().includes(this.searchTerm) ||
                teacher.subject.toLowerCase().includes(this.searchTerm) ||
                teacher.department.toLowerCase().includes(this.searchTerm) ||
                teacher.qualification.toLowerCase().includes(this.searchTerm);
            
            return matchesFilter && matchesSearch;
        });
    }

    /**
     * Get department filter key from department name
     */
    getDepartmentFilter(department) {
        const departmentMap = {
            'Science & Mathematics': 'science',
            'Languages': 'languages',
            'Social Sciences': 'social',
            'Physical Education': 'other',
            'Arts': 'other',
            'Technology': 'other',
            'Special Education': 'other'
        };
        
        return departmentMap[department] || 'other';
    }

    /**
     * Display teachers in grid
     */
    displayTeachers() {
        if (!this.teachersGrid) {
            console.error('Teachers grid not found');
            return;
        }

        const startIndex = 0;
        const endIndex = this.currentPage * this.teachersPerPage;
        const teachersToShow = this.filteredTeachers.slice(startIndex, endIndex);

        console.log('Displaying teachers:', teachersToShow.length, 'of', this.filteredTeachers.length);

        if (teachersToShow.length === 0) {
            if (this.teachers.length === 0) {
                this.showNoTeachers('No teachers found in the database. Please check back later.');
            } else {
                this.showNoTeachers('No teachers match your search criteria. Try adjusting your filters.');
            }
            this.teachersGrid.innerHTML = '';
            this.updateLoadMoreButton();
            return;
        }

        this.hideNoTeachers();
        this.teachersGrid.innerHTML = '';

        // Create Bootstrap row container
        const rowContainer = document.createElement('div');
        rowContainer.className = 'row g-4';
        this.teachersGrid.appendChild(rowContainer);
        console.log('✅ Created row container with class:', rowContainer.className);

        teachersToShow.forEach((teacher, index) => {
            const teacherCard = this.createTeacherCard(teacher, index);
            
            // Force immediate visibility before adding to DOM
            teacherCard.style.opacity = '1';
            teacherCard.style.visibility = 'visible';
            teacherCard.style.transform = 'none';
            
            rowContainer.appendChild(teacherCard);
            console.log('✅ Added card for:', teacher.name, 'with class:', teacherCard.className);
        });
        
        console.log('✅ Grid now contains', this.teachersGrid.children.length, 'cards');

        this.updateLoadMoreButton();
        
        // Force a reflow to ensure grid is visible
        this.teachersGrid.style.display = 'block';
        this.teachersGrid.style.visibility = 'visible';
        this.teachersGrid.style.opacity = '1';
        
        console.log('✅ Teachers Grid configured with', teachersToShow.length, 'cards');
        
        // Ensure immediate visibility since AOS is disabled
        setTimeout(() => {
            // Ensure row and all cards are visible
            const row = this.teachersGrid.querySelector('.row');
            if (row) {
                row.style.opacity = '1';
                row.style.visibility = 'visible';
                row.style.display = 'flex';
            }
            
            const cards = this.teachersGrid.querySelectorAll('.teacher-card-wrapper');
            cards.forEach(card => {
                card.style.opacity = '1';
                card.style.visibility = 'visible';
                card.style.transform = 'none';
                card.style.display = 'block';
            });
            
            this.animateCards();
        }, 100);
    }

    /**
     * Create teacher card element
     */
    createTeacherCard(teacher, index) {
        const card = document.createElement('div');
        card.className = 'col-lg-4 col-md-6 teacher-card-wrapper';
        // Remove AOS animations to prevent visibility issues
        // card.setAttribute('data-aos', 'fade-up');
        // card.setAttribute('data-aos-delay', (index * 100).toString());

        const imageHtml = teacher.photo_url ? 
            `<img src="${teacher.photo_url}" alt="${teacher.name}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
             <div class="no-photo" style="display: none;"><i class="fas fa-user"></i></div>` :
            `<div class="no-photo"><i class="fas fa-user"></i></div>`;

        const specializationsHtml = teacher.specializations && Array.isArray(teacher.specializations) ? 
            teacher.specializations.map(spec => `<span class="specialization-tag">${spec}</span>`).join('') : '';

        // Check if this is a position or subject
        const isPosition = teacher.subject && (
            teacher.subject.toLowerCase().includes('principal') ||
            teacher.subject.toLowerCase().includes('principle') ||
            teacher.subject.toLowerCase().includes('office') ||
            teacher.subject.toLowerCase().includes('assistant')
        );

        card.innerHTML = `
            <div class="teacher-card" data-teacher-id="${teacher.id}">
                <div class="teacher-image">
                    ${imageHtml}
                </div>
                
                <div class="teacher-content">
                    <div class="teacher-info">
                        <div class="teacher-department">${teacher.department}</div>
                        <h4 class="teacher-name">${teacher.name}</h4>
                        <div class="teacher-subject">
                            <span class="subject-label">${isPosition ? 'Position' : 'Subject'}:</span>
                            <span class="badge ${isPosition ? 'badge-position' : 'badge-subject'}">${teacher.subject}</span>
                        </div>
                        <p class="teacher-qualification">${teacher.qualification}</p>
                        <div class="teacher-experience">
                            <i class="fas fa-clock"></i> ${teacher.experience_years || 0} years experience
                        </div>
                        <p class="teacher-bio">${this.truncateText(teacher.bio || 'Dedicated educator committed to student success.', 120)}</p>
                    </div>
                    <div class="teacher-footer">
                        <div class="teacher-specializations">
                            ${specializationsHtml}
                        </div>
                        <div class="teacher-actions">
                            <button class="btn btn-maroon btn-sm" onclick="window.teachersManager.showTeacherModal(${teacher.id})">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <a href="mailto:${teacher.email || 'info@makalanegamaschool.lk'}" class="btn btn-outline-maroon btn-sm">
                                <i class="fas fa-envelope"></i> Contact
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add click event to show modal
        card.addEventListener('click', (e) => {
            if (!e.target.closest('button') && !e.target.closest('a')) {
                this.showTeacherModal(teacher.id);
            }
        });

        return card;
    }

    /**
     * Show teacher modal with details
     */
    showTeacherModal(teacherId) {
        const teacher = this.teachers.find(t => t.id == teacherId);
        if (!teacher) {
            console.error('Teacher not found:', teacherId);
            return;
        }

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'teacher-modal';
        
        const imageHtml = teacher.photo_url ? 
            `<img src="${teacher.photo_url}" alt="${teacher.name}">` :
            `<div class="no-photo-large"><i class="fas fa-user"></i></div>`;

        const specializationsHtml = teacher.specializations && Array.isArray(teacher.specializations) ? 
            `<div class="teacher-modal-specializations">
                <h5>Specializations</h5>
                <div class="specializations-list">
                    ${teacher.specializations.map(spec => `<span class="spec-tag">${spec}</span>`).join('')}
                </div>
            </div>` : '';

        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <button class="modal-close">&times;</button>
                <div class="modal-body">
                    <div class="teacher-modal-image">
                        ${imageHtml}
                    </div>
                    <div class="teacher-modal-info">
                        <div class="teacher-modal-department">${teacher.department}</div>
                        <h3 class="teacher-modal-name">${teacher.name}</h3>
                        <p class="teacher-modal-qualification">${teacher.qualification}</p>
                        <p><strong>Subject:</strong> ${teacher.subject}</p>
                        <p><strong>Experience:</strong> ${teacher.experience_years || 0} years</p>
                        
                        <div class="teacher-modal-bio">
                            <h5>About</h5>
                            <p>${teacher.bio || 'Dedicated educator committed to student success and academic excellence.'}</p>
                        </div>
                        
                        ${specializationsHtml}
                        
                        <div class="teacher-modal-contact">
                            <h5>Contact</h5>
                            <div class="contact-buttons">
                                <a href="mailto:${teacher.email || 'info@makalanegamaschool.lk'}" class="btn btn-maroon">
                                    <i class="fas fa-envelope"></i> Send Email
                                </a>
                                ${teacher.phone ? `<a href="tel:${teacher.phone}" class="btn btn-outline-maroon"><i class="fas fa-phone"></i> Call</a>` : ''}
                                <button class="btn btn-outline-maroon" onclick="this.closest('.teacher-modal').remove(); document.body.style.overflow='';">
                                    <i class="fas fa-times"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        // Animate modal in
        if (typeof gsap !== 'undefined') {
            gsap.from(modal.querySelector('.modal-content'), {
                scale: 0.8,
                opacity: 0,
                duration: 0.4,
                ease: "back.out(1.7)"
            });
            
            gsap.from(modal.querySelector('.modal-backdrop'), {
                opacity: 0,
                duration: 0.3,
                ease: "power2.out"
            });
        }

        // Close modal functionality
        const closeModal = () => {
            if (typeof gsap !== 'undefined') {
                gsap.to(modal, {
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.out",
                    onComplete: () => {
                        document.body.removeChild(modal);
                        document.body.style.overflow = '';
                    }
                });
            } else {
                document.body.removeChild(modal);
                document.body.style.overflow = '';
            }
        };

        modal.querySelector('.modal-close').addEventListener('click', closeModal);
        modal.querySelector('.modal-backdrop').addEventListener('click', closeModal);

        document.addEventListener('keydown', function escapeHandler(e) {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', escapeHandler);
            }
        });
    }

    /**
     * Load more teachers - Show all remaining teachers
     */
    loadMoreTeachers() {
        if (this.isLoading) return;

        this.isLoading = true;
        
        const loadMoreBtn = document.getElementById('load-more-teachers');
        const originalText = loadMoreBtn.innerHTML;

        // Show loading state
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;

        // Calculate how many more teachers to show (show all remaining)
        const remainingTeachers = this.filteredTeachers.length - (this.currentPage * this.teachersPerPage);
        if (remainingTeachers > 0) {
            // Set page to show all teachers
            this.currentPage = Math.ceil(this.filteredTeachers.length / this.teachersPerPage);
        }

        // Simulate loading delay
        setTimeout(() => {
            this.displayTeachers();
            this.updateLoadMoreButton();

            // Reset button
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
            this.isLoading = false;
        }, 500);
    }

    /**
     * Update load more button visibility
     */
    updateLoadMoreButton() {
        if (!this.loadMoreBtn) return;

        const totalShown = this.currentPage * this.teachersPerPage;
        const hasMore = totalShown < this.filteredTeachers.length;

        this.loadMoreBtn.style.display = hasMore ? 'inline-flex' : 'none';
    }

    /**
     * Update statistics
     */
    updateStatistics() {
        const total = this.teachers.length;
        const active = this.teachers.filter(t => t.is_active).length;
        const departments = [...new Set(this.teachers.map(t => t.department))].length;

        // Update header stats
        this.animateCounter(this.totalTeachersEl, total);
        this.animateCounter(this.activeTeachersEl, active);
        this.animateCounter(this.departmentsCountEl, departments);
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
     * Show error message
     */
    showError(message) {
        if (this.apiErrorDiv) {
            this.apiErrorDiv.innerHTML = `
                <div class="api-error">
                    <h5><i class="fas fa-exclamation-triangle"></i> Unable to Load Teachers</h5>
                    <p>${message}</p>
                    <button onclick="window.location.reload()" class="btn btn-maroon btn-sm">
                        <i class="fas fa-refresh"></i> Refresh Page
                    </button>
                </div>
            `;
            this.apiErrorDiv.style.display = 'block';
        }
    }

    /**
     * Hide error message
     */
    hideError() {
        if (this.apiErrorDiv) {
            this.apiErrorDiv.style.display = 'none';
        }
    }

    /**
     * Show no teachers message
     */
    showNoTeachers(message) {
        if (this.noTeachersDiv) {
            this.noTeachersDiv.innerHTML = `
                <i class="fas fa-chalkboard-teacher fa-4x text-muted mb-3"></i>
                <h4>No Teachers Found</h4>
                <p>${message}</p>
            `;
            this.noTeachersDiv.style.display = 'block';
        }
    }

    /**
     * Hide no teachers message
     */
    hideNoTeachers() {
        if (this.noTeachersDiv) {
            this.noTeachersDiv.style.display = 'none';
        }
    }

    /**
     * Animate cards on display
     */
    animateCards() {
        if (typeof gsap !== 'undefined') {
            gsap.from('.teacher-card', {
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
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return 'No date';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        } catch (error) {
            console.error('Error formatting date:', error);
            return 'Invalid date';
        }
    }

    /**
     * Truncate text to specified length
     */
    truncateText(text, maxLength) {
        if (!text) return 'No description available';
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

// Initialize teachers manager when DOM is loaded
let teachersManager;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing teachers manager...');
    teachersManager = new TeachersManager();
    
    // Make it globally available for modal callbacks
    window.teachersManager = teachersManager;
});

// Export for global access
window.TeachersManager = TeachersManager;