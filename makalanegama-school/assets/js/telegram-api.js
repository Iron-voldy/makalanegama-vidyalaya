/**
 * Telegram API Integration for Makalanegama School Website
 * Handles dynamic content loading from Telegram bot
 */

class TelegramAPI {
    constructor() {
        this.baseUrl = '/api';
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    }

    /**
     * Load latest achievements from Telegram
     */
    async loadAchievements(limit = 6) {
        try {
            const cacheKey = `achievements_${limit}`;
            
            // Check cache first
            if (this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < this.cacheTimeout) {
                    return cached.data;
                }
            }

            const response = await fetch(`${this.baseUrl}/achievements.php?limit=${limit}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Cache the result
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });

            return data;
        } catch (error) {
            console.error('Error loading achievements:', error);
            return this.getFallbackAchievements();
        }
    }

    /**
     * Load upcoming events from Telegram
     */
    async loadEvents(limit = 5) {
        try {
            const cacheKey = `events_${limit}`;
            
            if (this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < this.cacheTimeout) {
                    return cached.data;
                }
            }

            const response = await fetch(`${this.baseUrl}/events.php?limit=${limit}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });

            return data;
        } catch (error) {
            console.error('Error loading events:', error);
            return this.getFallbackEvents();
        }
    }

    /**
     * Load latest news from Telegram
     */
    async loadNews(limit = 4) {
        try {
            const cacheKey = `news_${limit}`;
            
            if (this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < this.cacheTimeout) {
                    return cached.data;
                }
            }

            const response = await fetch(`${this.baseUrl}/news.php?limit=${limit}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });

            return data;
        } catch (error) {
            console.error('Error loading news:', error);
            return this.getFallbackNews();
        }
    }

    /**
     * Load teacher information from Telegram
     */
    async loadTeachers(limit = 12) {
        try {
            const cacheKey = `teachers_${limit}`;
            
            if (this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < this.cacheTimeout) {
                    return cached.data;
                }
            }

            const response = await fetch(`${this.baseUrl}/teachers.php?limit=${limit}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });

            return data;
        } catch (error) {
            console.error('Error loading teachers:', error);
            return this.getFallbackTeachers();
        }
    }

    /**
     * Load gallery images from Telegram
     */
    async loadGallery(category = 'all', limit = 20) {
        try {
            const cacheKey = `gallery_${category}_${limit}`;
            
            if (this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < this.cacheTimeout) {
                    return cached.data;
                }
            }

            const response = await fetch(`${this.baseUrl}/gallery.php?category=${category}&limit=${limit}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            this.cache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });

            return data;
        } catch (error) {
            console.error('Error loading gallery:', error);
            return this.getFallbackGallery();
        }
    }

    /**
     * Send notification to Telegram (for contact forms, etc.)
     */
    async sendNotification(type, data) {
        try {
            const response = await fetch(`${this.baseUrl}/notify.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: type,
                    data: data,
                    timestamp: new Date().toISOString()
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error sending notification:', error);
            throw error;
        }
    }

    /**
     * Fallback data for when Telegram API is unavailable
     */
    getFallbackAchievements() {
        return [
            {
                id: 1,
                title: "Provincial Mathematics Excellence",
                description: "Our Grade 10 students achieved outstanding results in the provincial mathematics competition, securing first place among 50 participating schools.",
                image: "assets/images/achievements/cricket-win.jpg",
                category: "Sports",
                date: "2024-01-20",
                featured: false
            },
            {
                id: 3,
                title: "Environmental Conservation Award",
                description: "Recognition for our school's outstanding contribution to environmental conservation through our gardening and sustainability projects.",
                image: "assets/images/achievements/environment-award.jpg",
                category: "Environment",
                date: "2024-01-10",
                featured: false
            },
            {
                id: 4,
                title: "Computer Lab Inauguration",
                description: "Successfully launched our new computer laboratory with support from Wire Academy & Technology for Village, enhancing digital literacy education.",
                image: "assets/images/achievements/computer-lab.jpg",
                category: "Facilities",
                date: "2023-12-15",
                featured: false
            },
            {
                id: 5,
                title: "Traditional Dance Performance",
                description: "Our students showcased exceptional talent in traditional Sri Lankan dance at the provincial cultural festival.",
                image: "assets/images/achievements/dance-performance.jpg",
                category: "Cultural",
                date: "2023-11-25",
                featured: false
            },
            {
                id: 6,
                title: "Science Project Exhibition",
                description: "Students demonstrated innovative science projects focusing on renewable energy and environmental sustainability.",
                image: "assets/images/achievements/science-fair.jpg",
                category: "Academic",
                date: "2023-11-10",
                featured: false
            }
        ];
    }

    getFallbackEvents() {
        return [
            {
                id: 1,
                title: "Annual Sports Day",
                description: "Join us for our annual sports day featuring various athletic competitions and cultural performances.",
                date: "2024-02-25",
                time: "8:00 AM - 4:00 PM",
                location: "School Grounds",
                category: "Sports",
                image: "assets/images/events/sports-day.jpg"
            },
            {
                id: 2,
                title: "Parent-Teacher Meeting",
                description: "Meet with teachers to discuss student progress and academic performance for the first term.",
                date: "2024-03-15",
                time: "2:00 PM - 5:00 PM",
                location: "School Hall",
                category: "Academic",
                image: "assets/images/events/parent-meeting.jpg"
            },
            {
                id: 3,
                title: "Science Fair 2024",
                description: "Students will showcase their innovative science projects and experiments focusing on technology and environment.",
                date: "2024-04-10",
                time: "9:00 AM - 3:00 PM",
                location: "Computer Lab",
                category: "Academic",
                image: "assets/images/events/science-fair.jpg"
            },
            {
                id: 4,
                title: "Cultural Festival",
                description: "Celebrate Sri Lankan heritage with traditional music, dance, and drama performances by our talented students.",
                date: "2024-04-20",
                time: "6:00 PM - 9:00 PM",
                location: "School Auditorium",
                category: "Cultural",
                image: "assets/images/events/cultural-festival.jpg"
            },
            {
                id: 5,
                title: "Grade 5 Scholarship Preparation",
                description: "Special preparation classes for Grade 5 scholarship examination with experienced teachers.",
                date: "2024-05-01",
                time: "7:00 AM - 12:00 PM",
                location: "Classrooms 1-5",
                category: "Academic",
                image: "assets/images/events/scholarship-prep.jpg"
            }
        ];
    }

    getFallbackNews() {
        return [
            {
                id: 1,
                title: "New Computer Lab Officially Opens",
                content: "We are proud to announce the official opening of our state-of-the-art computer laboratory, made possible through the generous support of Wire Academy & Technology for Village. The lab features 25 modern computers with high-speed internet connectivity, enabling our students to develop essential digital literacy skills.",
                excerpt: "State-of-the-art computer laboratory opens with support from Wire Academy & Technology for Village...",
                image: "assets/images/news/computer-lab-opening.jpg",
                category: "Facilities",
                date: "2024-02-10",
                author: "Principal",
                featured: true
            },
            {
                id: 2,
                title: "2024 Admissions Now Open",
                content: "Applications for Grade 1 admissions for the 2024 academic year are now being accepted. Parents are encouraged to visit the school office between 8:00 AM and 2:00 PM on weekdays to collect application forms and learn about admission requirements.",
                excerpt: "Grade 1 applications for 2024 academic year now being accepted...",
                image: "assets/images/news/admissions-2024.jpg",
                category: "Admissions",
                date: "2024-02-05",
                author: "Admissions Office",
                featured: false
            },
            {
                id: 3,
                title: "Teacher Professional Development Workshop",
                content: "Our dedicated teaching staff participated in a comprehensive professional development workshop focusing on modern teaching methodologies and digital integration in education. The workshop was conducted by education experts from the National Institute of Education.",
                excerpt: "Teachers enhance skills through professional development workshop...",
                image: "assets/images/news/teacher-training.jpg",
                category: "Education",
                date: "2024-01-28",
                author: "Academic Coordinator",
                featured: false
            },
            {
                id: 4,
                title: "Environmental Conservation Initiative Launched",
                content: "Makalanegama School has launched a comprehensive environmental conservation initiative including tree planting, waste management, and renewable energy projects. Students are actively participating in creating a sustainable school environment.",
                excerpt: "School launches comprehensive environmental conservation program...",
                image: "assets/images/news/environmental-initiative.jpg",
                category: "Environment",
                date: "2024-01-20",
                author: "Environment Club",
                featured: false
            }
        ];
    }

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
                image: "assets/images/teachers/teacher-1.jpg"
            },
            {
                id: 2,
                name: "Mrs. Kamala Wijesinghe",
                qualification: "B.A (Sinhala), PGDE",
                subject: "Sinhala Language & Literature",
                department: "Languages",
                experience: "12 years",
                bio: "Passionate about promoting Sinhala literature and language skills among students.",
                image: "assets/images/teachers/teacher-2.jpg"
            },
            {
                id: 3,
                name: "Mr. Rohan Fernando",
                qualification: "B.Sc (Physics), Dip. in Education",
                subject: "Science",
                department: "Science & Mathematics",
                experience: "10 years",
                bio: "Dedicated science teacher focusing on practical experiments and scientific inquiry.",
                image: "assets/images/teachers/teacher-3.jpg"
            },
            {
                id: 4,
                name: "Mrs. Priyanka Silva",
                qualification: "B.A (English), TESL Certificate",
                subject: "English Language",
                department: "Languages",
                experience: "8 years",
                bio: "English language specialist with expertise in modern teaching methodologies.",
                image: "assets/images/teachers/teacher-4.jpg"
            },
            {
                id: 5,
                name: "Mr. Asanka Rathnayake",
                qualification: "B.A (History), Dip. in Education",
                subject: "History & Social Studies",
                department: "Social Sciences",
                experience: "14 years",
                bio: "History teacher with special interest in Sri Lankan heritage and culture.",
                image: "assets/images/teachers/teacher-5.jpg"
            },
            {
                id: 6,
                name: "Mrs. Sandya Mendis",
                qualification: "B.Sc (Geography), PGDE",
                subject: "Geography",
                department: "Social Sciences",
                experience: "9 years",
                bio: "Geography teacher promoting environmental awareness and sustainability.",
                image: "assets/images/teachers/teacher-6.jpg"
            }
        ];
    }

    getFallbackGallery() {
        return [
            {
                id: 1,
                title: "Interactive Learning Session",
                description: "Students engaged in interactive learning with modern teaching methods",
                image: "assets/images/gallery/classroom-activity.jpg",
                category: "Academic",
                date: "2024-02-01"
            },
            {
                id: 2,
                title: "Science Laboratory Experiment",
                description: "Hands-on science experiments in our well-equipped laboratory",
                image: "assets/images/gallery/science-experiment.jpg",
                category: "Academic",
                date: "2024-01-25"
            },
            {
                id: 3,
                title: "Annual Sports Day",
                description: "Athletic competitions and team spirit on display",
                image: "assets/images/gallery/sports-day.jpg",
                category: "Sports",
                date: "2024-01-20"
            },
            {
                id: 4,
                title: "Cultural Dance Performance",
                description: "Traditional Sri Lankan dance performances by our students",
                image: "assets/images/gallery/cultural-dance.jpg",
                category: "Cultural",
                date: "2024-01-15"
            },
            {
                id: 5,
                title: "Environmental Project",
                description: "Students working on gardening and sustainability initiatives",
                image: "assets/images/gallery/gardening-project.jpg",
                category: "Environment",
                date: "2024-01-10"
            },
            {
                id: 6,
                title: "Computer Class Session",
                description: "Students learning digital literacy in our new computer lab",
                image: "assets/images/gallery/computer-class.jpg",
                category: "Technology",
                date: "2024-01-05"
            }
        ];
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Get cache size
     */
    getCacheSize() {
        return this.cache.size;
    }
}

// Global functions for easy access
window.loadLatestAchievements = async function() {
    const telegramAPI = new TelegramAPI();
    try {
        const achievements = await telegramAPI.loadAchievements(3);
        window.MakalanegamaSchool.updateAchievementsDisplay(achievements);
    } catch (error) {
        console.error('Failed to load achievements:', error);
    }
};

window.loadUpcomingEvents = async function() {
    const telegramAPI = new TelegramAPI();
    try {
        const events = await telegramAPI.loadEvents(3);
        window.MakalanegamaSchool.updateEventsDisplay(events);
    } catch (error) {
        console.error('Failed to load events:', error);
    }
};

window.loadLatestNews = async function() {
    const telegramAPI = new TelegramAPI();
    try {
        const news = await telegramAPI.loadNews(4);
        window.MakalanegamaSchool.updateNewsDisplay(news);
    } catch (error) {
        console.error('Failed to load news:', error);
    }
};

window.loadTeacherProfiles = async function() {
    const telegramAPI = new TelegramAPI();
    try {
        const teachers = await telegramAPI.loadTeachers();
        if (window.MakalanegamaSchool.updateTeachersDisplay) {
            window.MakalanegamaSchool.updateTeachersDisplay(teachers);
        }
    } catch (error) {
        console.error('Failed to load teacher profiles:', error);
    }
};

window.loadGalleryImages = async function(category = 'all') {
    const telegramAPI = new TelegramAPI();
    try {
        const images = await telegramAPI.loadGallery(category);
        if (window.MakalanegamaSchool.updateGalleryDisplay) {
            window.MakalanegamaSchool.updateGalleryDisplay(images);
        }
    } catch (error) {
        console.error('Failed to load gallery images:', error);
    }
};

window.sendContactNotification = async function(formData) {
    const telegramAPI = new TelegramAPI();
    try {
        await telegramAPI.sendNotification('contact', formData);
        return { success: true, message: 'Message sent successfully!' };
    } catch (error) {
        console.error('Failed to send contact notification:', error);
        return { success: false, message: 'Failed to send message. Please try again.' };
    }
};

// Auto-refresh content every 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        loadLatestAchievements();
        loadUpcomingEvents();
        loadLatestNews();
    }
}, 5 * 60 * 1000);

// Refresh content when page becomes visible
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        loadLatestAchievements();
        loadUpcomingEvents();
        loadLatestNews();
    }
});

// Export the TelegramAPI class
window.TelegramAPI = TelegramAPI;/math-competition.jpg",
                category: "Academic",
                date: "2024-02-15",
                featured: true
            },
            {
                id: 2,
                title: "Inter-School Cricket Championship",
                description: "Our cricket team won the zonal championship after a thrilling final match against St. Joseph's College.",
                image: "assets/images/achievements