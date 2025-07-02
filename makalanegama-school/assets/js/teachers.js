/**
 * Teachers Page JavaScript
 * Handles teacher filtering, loading, and interactions
 */

class TeachersManager {
    constructor() {
        this.teachers = [];
        this.filteredTeachers = [];
        this.currentFilter = 'all';
        this.teachersPerPage = 6;
        this.currentPage = 1;
        this.isLoading = false;
        
        this.initializeFilters();
        this.initializeLoadMore();
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
                this.filterTeachers(filter);
                
                // Animate filter change
                this.animateFilterChange();
            });
        });
    }

    /**
     * Initialize load more functionality
     */
    initializeLoadMore() {
        const loadMoreBtn = document.getElementById('load-more-teachers');
        
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreTeachers();
            });
        }
    }

    /**
     * Filter teachers by department
     */
    filterTeachers(filter) {
        this.currentFilter = filter;
        this.currentPage = 1;
        
        if (filter === 'all') {
            this.filteredTeachers = [...this.teachers];
        } else {
            this.filteredTeachers = this.teachers.filter(teacher => {
                return this.getDepartmentFilter(teacher.department) === filter;
            });
        }
        
        this.displayTeachers();
        this.updateLoadMoreButton();
    }

    /**
     * Get department filter key
     */
    getDepartmentFilter(department) {
        const departmentMap = {
            'Science & Mathematics': 'science',
            'Languages': 'languages',
            'Social Sciences': 'social',
            'Physical Education': 'other',
            'Arts': 'other',
            'Technology': 'other'
        };
        
        return departmentMap[department] || 'other';
    }

    /**
     * Display teachers with animation
     */
    displayTeachers() {
        const grid = document.getElementById('teachers-grid');
        if (!grid) return;
        
        // Clear existing teachers (except sample ones for now)
        const teachersToShow = this.filteredTeachers.slice(0, this.currentPage * this.teachersPerPage);
        
        // Animate out existing cards
        gsap.to('.teacher-card', {
            opacity: 0,
            y: 20,
            duration: 0.3,
            stagger: 0.05,
            onComplete: () => {
                this.renderTeachers(teachersToShow);
            }
        });
    }

    /**
     * Render teachers in the grid
     */
    renderTeachers(teachers) {
        const grid = document.getElementById('teachers-grid');
        if (!grid) return;
        
        // For now, we'll work with the existing cards and update them
        // In a real implementation, we would generate cards dynamically
        
        const existingCards = grid.querySelectorAll('.teacher-card');
        
        // Show/hide cards based on filter
        existingCards.forEach((card, index) => {
            const department = card.getAttribute('data-department');
            const shouldShow = this.currentFilter === 'all' || 
                              this.getDepartmentFilter(this.getDepartmentName(department)) === this.currentFilter;
            
            if (shouldShow) {
                card.style.display = 'block';
                gsap.from(card, {
                    opacity: 0,
                    y: 30,
                    duration: 0.6,
                    delay: index * 0.1,
                    ease: "power2.out"
                });
            } else {
                card.style.display = 'none';
            }
        });
    }

    /**
     * Get department name from filter key
     */
    getDepartmentName(filterKey) {
        const filterMap = {
            'science': 'Science & Mathematics',
            'languages': 'Languages',
            'social': 'Social Sciences',
            'other': 'Other'
        };
        
        return filterMap[filterKey] || 'Other';
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
     * Load more teachers
     */
    loadMoreTeachers() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.currentPage++;
        
        const loadMoreBtn = document.getElementById('load-more-teachers');
        const originalText = loadMoreBtn.innerHTML;
        
        // Show loading state
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;
        
        // Simulate loading delay
        setTimeout(() => {
            this.displayTeachers();
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
        const loadMoreBtn = document.getElementById('load-more-teachers');
        if (!loadMoreBtn) return;
        
        const totalShown = this.currentPage * this.teachersPerPage;
        const hasMore = totalShown < this.filteredTeachers.length;
        
        if (hasMore) {
            loadMoreBtn.style.display = 'inline-flex';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }

    /**
     * Load teachers from API
     */
    async loadTeachers() {
        try {
            // In development, use fallback data
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                this.teachers = this.getFallbackTeachers();
            } else {
                // Load from Telegram API
                const response = await fetch('/api/teachers.php');
                this.teachers = await response.json();
            }
            
            this.filteredTeachers = [...this.teachers];
            this.displayTeachers();
            this.updateLoadMoreButton();
            
        } catch (error) {
            console.error('Error loading teachers:', error);
            this.teachers = this.getFallbackTeachers();
            this.filteredTeachers = [...this.teachers];
            this.displayTeachers();
        }
    }

    /**
     * Fallback teacher data
     */
    getFallbackTeachers() {
        return [
            {
                id: 1,
                name: "Mr. Sunil Perera",
                qualification: "B.Ed (Mathematics), Dip. in Education",
                subject: "Mathematics",
                department: "Science & Mathematics",
                experience: "15 years",
                bio: "Experienced mathematics teacher specializing in advanced mathematics and statistics.",
                image: "assets/images/teachers/teacher-1.jpg",
                email: "sperera@makalanegamaschool.lk",
                specializations: ["Advanced Mathematics", "Statistics", "Problem Solving"]
            },
            {
                id: 2,
                name: "Mrs. Kamala Wijesinghe",
                qualification: "B.A (Sinhala), PGDE",
                subject: "Sinhala Language & Literature",
                department: "Languages",
                experience: "12 years",
                bio: "Passionate about promoting Sinhala literature and language skills among students.",
                image: "assets/images/teachers/teacher-2.jpg",
                email: "kwijesinghe@makalanegamaschool.lk",
                specializations: ["Sinhala Literature", "Creative Writing", "Cultural Studies"]
            },
            {
                id: 3,
                name: "Mr. Rohan Fernando",
                qualification: "B.Sc (Physics), Dip. in Education",
                subject: "Science",
                department: "Science & Mathematics",
                experience: "10 years",
                bio: "Dedicated science teacher focusing on practical experiments and scientific inquiry.",
                image: "assets/images/teachers/teacher-3.jpg",
                email: "rfernando@makalanegamaschool.lk",
                specializations: ["Physics", "Laboratory Work", "Scientific Method"]
            },
            {
                id: 4,
                name: "Mrs. Priyanka Silva",
                qualification: "B.A (English), TESL Certificate",
                subject: "English Language",
                department: "Languages",
                experience: "8 years",
                bio: "English language specialist with expertise in modern teaching methodologies.",
                image: "assets/images/teachers/teacher-4.jpg",
                email: "psilva@makalanegamaschool.lk",
                specializations: ["TESL", "Communication Skills", "Grammar"]
            },
            {
                id: 5,
                name: "Mr. Asanka Rathnayake",
                qualification: "B.A (History), Dip. in Education",
                subject: "History & Social Studies",
                department: "Social Sciences",
                experience: "14 years",
                bio: "History teacher with special interest in Sri Lankan heritage and culture.",
                image: "assets/images/teachers/teacher-5.jpg",
                email: "arathnayake@makalanegamaschool.lk",
                specializations: ["Sri Lankan History", "Cultural Heritage", "Social Studies"]
            },
            {
                id: 6,
                name: "Mrs. Sandya Mendis",
                qualification: "B.Sc (Geography), PGDE",
                subject: "Geography",
                department: "Social Sciences",
                experience: "9 years",
                bio: "Geography teacher promoting environmental awareness and sustainability.",
                image: "assets/images/teachers/teacher-6.jpg",
                email: "smendis@makalanegamaschool.lk",
                specializations: ["Environmental Geography", "Field Studies", "Sustainability"]
            },
            // Additional teachers for demonstration
            {
                id: 7,
                name: "Mr. Chathura Bandara",
                qualification: "B.A (Art), Dip. in Education",
                subject: "Art & Crafts",
                department: "Arts",
                experience: "7 years",
                bio: "Creative arts teacher inspiring students through various art forms and crafts.",
                image: "assets/images/teachers/teacher-7.jpg",
                email: "cbandara@makalanegamaschool.lk",
                specializations: ["Traditional Art", "Crafts", "Creative Expression"]
            },
            {
                id: 8,
                name: "Mrs. Niluka Jayawardena",
                qualification: "B.Sc (Physical Education), Dip. in Sports Science",
                subject: "Physical Education",
                department: "Physical Education",
                experience: "6 years",
                bio: "Physical education teacher promoting health, fitness, and sportsmanship among students.",
                image: "assets/images/teachers/teacher-8.jpg",
                email: "njayawardena@makalanegamaschool.lk",
                specializations: ["Athletics", "Team Sports", "Health Education"]
            },
            {
                id: 9,
                name: "Mr. Dinesh Kulasekara",
                qualification: "B.Sc (Computer Science), PGDE",
                subject: "Information Technology",
                department: "Technology",
                experience: "5 years",
                bio: "IT teacher helping students develop digital literacy and computer programming skills.",
                image: "assets/images/teachers/teacher-9.jpg",
                email: "dkulasekara@makalanegamaschool.lk",
                specializations: ["Programming", "Digital Literacy", "Web Development"]
            }
        ];
    }

    /**
     * Initialize teacher card interactions
     */
    initializeTeacherInteractions() {
        const teacherCards = document.querySelectorAll('.teacher-card');
        
        teacherCards.forEach(card => {
            // Hover animations
            card.addEventListener('mouseenter', () => {
                gsap.to(card, {
                    y: -10,
                    boxShadow: "0 20px 60px rgba(0, 0, 0, 0.15)",
                    duration: 0.3,
                    ease: "power2.out"
                });
                
                gsap.to(card.querySelector('.teacher-image img'), {
                    scale: 1.1,
                    duration: 0.4,
                    ease: "power2.out"
                });
                
                gsap.to(card.querySelector('.teacher-overlay'), {
                    opacity: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
            
            card.addEventListener('mouseleave', () => {
                gsap.to(card, {
                    y: 0,
                    boxShadow: "0 8px 30px rgba(0, 0, 0, 0.12)",
                    duration: 0.3,
                    ease: "power2.out"
                });
                
                gsap.to(card.querySelector('.teacher-image img'), {
                    scale: 1,
                    duration: 0.4,
                    ease: "power2.out"
                });
                
                gsap.to(card.querySelector('.teacher-overlay'), {
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
            
            // Click to expand functionality
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.teacher-social')) {
                    this.showTeacherModal(card);
                }
            });
        });
    }

    /**
     * Show teacher modal with detailed information
     */
    showTeacherModal(card) {
        const teacherName = card.querySelector('.teacher-name').textContent;
        const teacherImage = card.querySelector('.teacher-image img').src;
        const teacherQualification = card.querySelector('.teacher-qualification').textContent;
        const teacherSubject = card.querySelector('.teacher-subject').textContent;
        const teacherDepartment = card.querySelector('.teacher-department').textContent;
        const teacherBio = card.querySelector('.teacher-bio').textContent;
        const teacherExperience = card.querySelector('.teacher-experience span').textContent;
        
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'teacher-modal';
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <button class="modal-close">&times;</button>
                <div class="modal-body">
                    <div class="teacher-modal-image">
                        <img src="${teacherImage}" alt="${teacherName}">
                    </div>
                    <div class="teacher-modal-info">
                        <div class="teacher-modal-department">${teacherDepartment}</div>
                        <h3 class="teacher-modal-name">${teacherName}</h3>
                        <p class="teacher-modal-qualification">${teacherQualification}</p>
                        <p class="teacher-modal-subject"><strong>Subject:</strong> ${teacherSubject}</p>
                        <p class="teacher-modal-experience"><strong>Experience:</strong> ${teacherExperience}</p>
                        <div class="teacher-modal-bio">
                            <h5>About</h5>
                            <p>${teacherBio}</p>
                        </div>
                        <div class="teacher-modal-contact">
                            <h5>Contact</h5>
                            <div class="contact-buttons">
                                <a href="mailto:${teacherName.toLowerCase().replace(/\s+/g, '').replace(/\./g, '')}@makalanegamaschool.lk" class="btn btn-maroon">
                                    <i class="fas fa-envelope"></i> Send Email
                                </a>
                                <button class="btn btn-outline-maroon" onclick="this.closest('.teacher-modal').remove()">
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
        
        // Close modal functionality
        const closeModal = () => {
            gsap.to(modal, {
                opacity: 0,
                duration: 0.3,
                ease: "power2.out",
                onComplete: () => {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                }
            });
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
}

/**
 * Initialize teacher page functionality
 */
function initializeTeacherFilters() {
    const teachersManager = new TeachersManager();
    
    // Initialize interactions after a short delay to ensure DOM is ready
    setTimeout(() => {
        teachersManager.initializeTeacherInteractions();
    }, 500);
    
    // Load teachers data
    teachersManager.loadTeachers();
    
    // Make teachersManager globally available
    window.teachersManager = teachersManager;
}

/**
 * Update teachers display from external call
 */
window.MakalanegamaSchool = window.MakalanegamaSchool || {};
window.MakalanegamaSchool.updateTeachersDisplay = function(teachers) {
    if (window.teachersManager) {
        window.teachersManager.teachers = teachers;
        window.teachersManager.filteredTeachers = [...teachers];
        window.teachersManager.displayTeachers();
        window.teachersManager.updateLoadMoreButton();
    }
};

/**
 * Search functionality for teachers
 */
function initializeTeacherSearch() {
    const searchInput = document.getElementById('teacher-search');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', debounce((e) => {
        const searchTerm = e.target.value.toLowerCase();
        const teacherCards = document.querySelectorAll('.teacher-card');
        
        teacherCards.forEach(card => {
            const teacherName = card.querySelector('.teacher-name').textContent.toLowerCase();
            const teacherSubject = card.querySelector('.teacher-subject').textContent.toLowerCase();
            const teacherDepartment = card.querySelector('.teacher-department').textContent.toLowerCase();
            
            const matches = teacherName.includes(searchTerm) || 
                          teacherSubject.includes(searchTerm) || 
                          teacherDepartment.includes(searchTerm);
            
            if (matches) {
                card.style.display = 'block';
                gsap.from(card, {
                    opacity: 0,
                    y: 20,
                    duration: 0.3,
                    ease: "power2.out"
                });
            } else {
                gsap.to(card, {
                    opacity: 0,
                    y: -20,
                    duration: 0.2,
                    ease: "power2.out",
                    onComplete: () => {
                        card.style.display = 'none';
                    }
                });
            }
        });
    }, 300));
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

/**
 * Initialize on DOM content loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeTeacherSearch();
});

// CSS for teacher modal (to be added to style.css)
const modalStyles = `
.teacher-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    font-size: 2rem;
    color: #666;
    cursor: pointer;
    z-index: 10;
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: var(--maroon-primary);
}

.modal-body {
    padding: 0;
}

.teacher-modal-image {
    height: 250px;
    overflow: hidden;
    border-radius: 20px 20px 0 0;
}

.teacher-modal-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.teacher-modal-info {
    padding: 2rem;
}

.teacher-modal-department {
    color: var(--maroon-primary);
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}

.teacher-modal-name {
    color: var(--black);
    margin-bottom: 0.5rem;
}

.teacher-modal-qualification,
.teacher-modal-subject,
.teacher-modal-experience {
    color: var(--medium-gray);
    margin-bottom: 0.5rem;
}

.teacher-modal-bio,
.teacher-modal-contact {
    margin-top: 1.5rem;
}

.teacher-modal-bio h5,
.teacher-modal-contact h5 {
    color: var(--black);
    margin-bottom: 1rem;
}

.contact-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.contact-buttons .btn {
    flex: 1;
    min-width: 150px;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
    
    .teacher-modal-info {
        padding: 1.5rem;
    }
    
    .contact-buttons {
        flex-direction: column;
    }
    
    .contact-buttons .btn {
        width: 100%;
    }
}
`;

// Add modal styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = modalStyles;
document.head.appendChild(styleSheet);