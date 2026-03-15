/**
 * Database API - Hotel Management System
 * Handles all database operations for hotel booking, restaurant reservations, and user management
 */
class DatabaseAPI {
    constructor() {
        this.baseURL = './api';
        this.currentUser = null;
        this.init();
    }

    /**
     * Initialize the database API
     */
    async init() {
        // Add modal styles to head
        this.addModalStyles();
        
        // Load current user
        await this.loadCurrentUserSimple();
        
        // Setup periodic session refresh
        setInterval(() => this.refreshSession(), 300000); // 5 minutes
    }

    /**
     * Add modal styles to the page
     */
    addModalStyles() {
        if (document.getElementById('modalStyles')) return;
        
        const styles = `
            <style id="modalStyles">
                #logoutModal .bg-white {
                    transform: scale(0.9);
                    opacity: 0;
                    transition: all 0.3s ease;
                }
                #logoutModal .bg-white.scale-100 {
                    transform: scale(1);
                    opacity: 1;
                }
                #logoutModal .bg-white:hover {
                    transform: scale(1.02);
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    /**
     * API Request Helper
     */
    async apiRequest(endpoint, options = {}) {
        let url;
        
        // Route to appropriate API file
        if (endpoint.includes('profile') || endpoint.includes('bookings') || endpoint.includes('reservations') || 
            endpoint.includes('notifications') || endpoint.includes('payment-methods') || endpoint.includes('loyalty-rewards') ||
            endpoint.includes('transactions') || endpoint.includes('reviews') || endpoint.includes('points-history') ||
            endpoint.includes('change-password')) {
            url = `./api/auth/user.php?endpoint=${endpoint}`;
        } else if (endpoint.includes('hotel-rooms') || endpoint.includes('restaurant-tables') || 
                   endpoint.includes('menu-items') || endpoint.includes('create-hotel-booking') ||
                   endpoint.includes('create-restaurant-reservation') || endpoint.includes('create-food-order')) {
            url = `./api/booking/booking.php?endpoint=${endpoint}`;
        } else if (endpoint.includes('auth.php')) {
            url = endpoint; // Full URL for auth calls
        } else {
            url = `./api/${endpoint}`;
        }
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                // Try to get error message from response
                let errorMessage = 'API request failed';
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorMessage;
                } catch (e) {
                    // If JSON parsing fails, use status text
                    errorMessage = response.statusText || errorMessage;
                }
                throw new Error(errorMessage);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    /**
     * Simple load current user data (bypasses complex routing)
     */
    async loadCurrentUserSimple() {
        console.log('🔍 Loading current user (simple method)...');
        
        try {
            console.log('🔍 Making request to simple_user.php...');
            const response = await fetch('./api/auth/simple_user.php');
            console.log('🔍 Response status:', response.status);
            console.log('🔍 Response headers:', response.headers);
            
            const data = await response.json();
            console.log('🔍 Raw API response:', data);
            
            if (data.success) {
                console.log('✅ User data loaded (simple):', data.data);
                console.log('🔍 User data details:', JSON.stringify(data.data, null, 2));
                this.currentUser = data.data;
                
                // Don't automatically update UI - let each page handle it
                // this.updateUIWithUserData();
                
                return data.data;
            } else {
                console.log('❌ Simple user API failed:', data);
                console.log('❌ Error details:', data.error);
            }
            
            return null;
        } catch (error) {
            console.error('❌ Simple user load failed:', error);
            console.error('❌ Error details:', error.message);
            console.error('❌ Error stack:', error.stack);
            return null;
        }
    }
    
    /**
     * Update UI with user data
     */
    updateUIWithUserData() {
        console.log('🔍 updateUIWithUserData called with:', this.currentUser);
        
        if (!this.currentUser) {
            console.log('❌ No current user data to update UI');
            return;
        }
        
        console.log('🔍 Updating UI elements...');
        
        // Update header user info
        const userNameElement = document.getElementById('userName');
        const membershipElement = document.getElementById('membershipTier'); // Fixed ID
        const pointsElement = document.getElementById('userPoints');
        
        console.log('🔍 UI elements found:', {
            userName: !!userNameElement,
            membershipTier: !!membershipElement,
            userPoints: !!pointsElement,
            actualElements: {
                userNameElement: userNameElement,
                membershipElement: membershipElement,
                pointsElement: pointsElement
            }
        });
        
        if (userNameElement) {
            console.log('🔍 Setting userName to:', `${this.currentUser.first_name} ${this.currentUser.last_name}`);
            userNameElement.textContent = `${this.currentUser.first_name} ${this.currentUser.last_name}`;
        } else {
            console.log('❌ userName element not found');
        }
        
        if (membershipElement) {
            console.log('🔍 Setting membership to:', this.currentUser.membership_tier || 'Member');
            membershipElement.textContent = this.currentUser.membership_tier || 'Member';
        } else {
            console.log('❌ membershipTier element not found');
        }
        
        if (pointsElement) {
            console.log('🔍 Setting userPoints to:', this.currentUser.loyalty_points || '0');
            pointsElement.textContent = this.currentUser.loyalty_points || '0';
        } else {
            console.log('❌ userPoints element not found');
        }
        
        console.log('✅ UI update completed');
    }
    
    /**
     * Update notification count
     */
    updateNotificationCount() {
        if (!this.currentUser) return;
        
        const notificationCount = document.getElementById('notificationCount');
        if (notificationCount) {
            notificationCount.textContent = this.currentUser.unread_notifications || '0';
        }
    }
    
    /**
     * Get points to next tier
     */
    getPointsToNextTier(currentTier, currentPoints) {
        const tiers = {
            'member': { next: 'silver', required: 100 },
            'silver': { next: 'gold', required: 500 },
            'gold': { next: 'platinum', required: 1000 },
            'platinum': { next: null, required: null }
        };
        
        const tierInfo = tiers[currentTier] || tiers['member'];
        
        if (!tierInfo.next) {
            return {
                nextTier: null,
                pointsNeeded: 0,
                progress: 100
            };
        }
        
        const pointsNeeded = Math.max(0, tierInfo.required - currentPoints);
        const progress = Math.min(100, (currentPoints / tierInfo.required) * 100);
        
        return {
            nextTier: tierInfo.next,
            pointsNeeded,
            progress
        };
    }

    /**
     * Format date
     */
    formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00' || dateString === '0000-00-00 00:00:00') {
            return 'Not set';
        }
        
        try {
            const date = new Date(dateString);
            // Check if date is invalid
            if (isNaN(date.getTime())) {
                return 'Not set';
            }
            
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (error) {
            console.error('Date formatting error:', error);
            return 'Not set';
        }
    }

    /**
     * Get hotel bookings
     */
    async getHotelBookings(limit = 10) {
        try {
            // For now, return empty array - this would be implemented with actual API
            return [];
        } catch (error) {
            console.error('Failed to load hotel bookings:', error);
            return [];
        }
    }

    /**
     * Get restaurant reservations
     */
    async getRestaurantReservations(limit = 10) {
        try {
            // For now, return empty array - this would be implemented with actual API
            return [];
        } catch (error) {
            console.error('Failed to load restaurant reservations:', error);
            return [];
        }
    }

    /**
     * Get food orders
     */
    async getFoodOrders(limit = 10) {
        try {
            // For now, return empty array - this would be implemented with actual API
            return [];
        } catch (error) {
            console.error('Failed to load food orders:', error);
            return [];
        }
    }

    /**
     * Get user initials
     */
    getUserInitials(firstName, lastName) {
        if (!firstName && !lastName) return '—';
        
        const firstInitial = firstName ? firstName.charAt(0).toUpperCase() : '';
        const lastInitial = lastName ? lastName.charAt(0).toUpperCase() : '';
        
        return (firstInitial + lastInitial) || '—';
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toastContainer');
        if (!container) {
            console.error('Toast container not found');
            return;
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        // Set icon based on type
        let icon = 'fa-regular fa-bell';
        if (type === 'success') {
            icon = 'fa-solid fa-check-circle';
        } else if (type === 'error') {
            icon = 'fa-solid fa-exclamation-circle';
        } else if (type === 'info') {
            icon = 'fa-solid fa-info-circle';
        }
        
        toast.innerHTML = `
            <div class="toast-content">
                <i class="${icon} toast-icon"></i>
                <span class="toast-message">${message}</span>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Auto-remove after duration
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, duration);
    }
    
    /**
     * Load current user data
     */
    async loadCurrentUser() {
        try {
            // First check if user is logged in
            console.log('🔍 Checking session...');
            const sessionResponse = await this.apiRequest('../login-register/api/auth/auth.php/check-session');
            console.log('🔍 Session response:', sessionResponse);
            
            if (!sessionResponse.success) {
                console.log('❌ No active session found');
                this.currentUser = null;
                return null;
            }
            
            console.log('✅ Session active, loading user profile...');
            const response = await this.apiRequest('profile');
            console.log('🔍 Profile response:', response);
            
            if (response.success) {
                console.log('✅ User data loaded:', response.data);
                this.currentUser = response.data;
                this.updateUIWithUserData();
                return response.data;
            } else {
                console.log('❌ Profile API failed:', response);
            }
            
            return null;
        } catch (error) {
            console.error('❌ Failed to load current user:', error);
            this.currentUser = null;
            return null;
        }
    }
    
    /**
     * Update user profile
     */
    async updateProfile(profileData) {
        try {
            const response = await this.apiRequest('update-profile', {
                method: 'PUT',
                body: JSON.stringify(profileData)
            });
            
            if (response.success) {
                this.currentUser = response.data;
                this.updateUIWithUserData();
                this.showToast('Profile updated successfully', 'success');
            } else {
                this.showToast('Failed to update profile', 'error');
            }
        } catch (error) {
            console.error('Failed to update profile:', error);
            this.showToast('Failed to update profile', 'error');
        }
    }
    
    /**
     * Get notification preferences
     */
    async getNotificationPreferences() {
        try {
            // Use the direct notification_preferences.php file
            const response = await fetch('./api/auth/notification_preferences.php');
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (error) {
            console.error('Failed to load notification preferences:', error);
            return null;
        }
    }
    
    /**
     * Update notification preferences
     */
    async updateNotificationPreferences(preferences) {
        try {
            const response = await this.apiRequest('notification-preferences', {
                method: 'PUT',
                body: JSON.stringify(preferences)
            });
            
            if (response.success) {
                this.showToast('Notification preferences updated', 'success');
            } else {
                this.showToast('Failed to update notification preferences', 'error');
            }
        } catch (error) {
            console.error('Failed to update notification preferences:', error);
            this.showToast('Failed to update notification preferences', 'error');
        }
    }
    
    /**
     * Change password
     */
    async changePassword(passwordData) {
        try {
            const response = await this.apiRequest('change-password', {
                method: 'POST',
                body: JSON.stringify(passwordData)
            });
            
            if (response.success) {
                this.showToast('Password changed successfully', 'success');
            } else {
                this.showToast('Failed to change password', 'error');
            }
        } catch (error) {
            console.error('Failed to change password:', error);
            this.showToast('Failed to change password', 'error');
        }
    }
    
    /**
     * Get available hotel rooms
     */
    async getAvailableHotelRooms(checkIn, checkOut) {
        try {
            // For now, return empty array - this would be implemented with actual API
            return [];
        } catch (error) {
            console.error('Failed to load available hotel rooms:', error);
            return [];
        }
    }

    /**
     * Get notifications
     */
    async getNotifications(limit = 10) {
        try {
            // For now, return empty array - this would be implemented with actual API
            return [];
        } catch (error) {
            console.error('Failed to load notifications:', error);
            return [];
        }
    }

    /**
     * Format currency
     */
    formatCurrency(amount, currency = 'PHP') {
        const formatter = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2
        });
        
        return formatter.format(amount);
    }

    /**
     * Get loyalty message based on points and tier
     */
    getLoyaltyMessage(tier, points) {
        const messages = {
            'member': `You have ${points} points. Keep booking to reach Silver tier!`,
            'silver': `Great job! You have ${points} points as a Silver member.`,
            'gold': `Excellent! You have ${points} points as a Gold member.`,
            'platinum': `Outstanding! You have ${points} points as our Platinum member.`
        };
        
        return messages[tier] || messages['member'];
    }

    /**
     * Get payment methods
     */
    async getPaymentMethods() {
        try {
            // Use direct fetch instead of apiRequest to avoid routing issues
            const response = await fetch('./api/payment/payment_methods.php');
            const data = await response.json();
            return data.success ? data.data : [];
        } catch (error) {
            console.error('Failed to load payment methods:', error);
            return [];
        }
    }
    
    /**
     * Add payment method
     */
    async addPaymentMethod(paymentData) {
        try {
            const response = await this.apiRequest('add-payment-method', {
                method: 'POST',
                body: JSON.stringify(paymentData)
            });
            
            if (response.success) {
                this.showToast('Payment method added successfully', 'success');
            } else {
                this.showToast('Failed to add payment method', 'error');
            }
        } catch (error) {
            console.error('Failed to add payment method:', error);
            this.showToast('Failed to add payment method', 'error');
        }
    }
    
    /**
     * Get bookings
     */
    async getBookings() {
        try {
            const response = await this.apiRequest('bookings');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load bookings:', error);
            return [];
        }
    }
    
    /**
     * Create hotel booking
     */
    async createHotelBooking(bookingData) {
        try {
            const response = await this.apiRequest('create-hotel-booking', {
                method: 'POST',
                body: JSON.stringify(bookingData)
            });
            
            if (response.success) {
                this.showToast('Hotel booking confirmed', 'success');
            } else {
                this.showToast('Failed to create hotel booking', 'error');
            }
        } catch (error) {
            console.error('Failed to create hotel booking:', error);
            this.showToast('Failed to create hotel booking', 'error');
        }
    }
    
    /**
     * Get reservations
     */
    async getReservations() {
        try {
            const response = await this.apiRequest('reservations');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load reservations:', error);
            return [];
        }
    }
    
    /**
     * Create restaurant reservation
     */
    async createRestaurantReservation(reservationData) {
        try {
            const response = await this.apiRequest('create-restaurant-reservation', {
                method: 'POST',
                body: JSON.stringify(reservationData)
            });
            
            if (response.success) {
                this.showToast('Restaurant reservation confirmed', 'success');
            } else {
                this.showToast('Failed to create restaurant reservation', 'error');
            }
        } catch (error) {
            console.error('Failed to create restaurant reservation:', error);
            this.showToast('Failed to create restaurant reservation', 'error');
        }
    }
    
    /**
     * Get menu categories
     */
    async getMenuCategories() {
        try {
            const response = await this.apiRequest('menu-categories');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load menu categories:', error);
            return [];
        }
    }
    
    /**
     * Get menu items
     */
    async getMenuItems(categoryId = null) {
        try {
            let url = 'menu-items';
            if (categoryId) {
                url = `menu-items?category_id=${categoryId}`;
            }
            
            const response = await this.apiRequest(url);
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load menu items:', error);
            return [];
        }
    }
    
    /**
     * Create food order
     */
    async createFoodOrder(orderData) {
        try {
            const response = await this.apiRequest('create-food-order', {
                method: 'POST',
                body: JSON.stringify(orderData)
            });
            
            if (response.success) {
                this.showToast('Order placed successfully', 'success');
            } else {
                this.showToast('Failed to place order', 'error');
            }
        } catch (error) {
            console.error('Failed to create food order:', error);
            this.showToast('Failed to place order', 'error');
        }
    }
    
    /**
     * Get waiting list
     */
    async getWaitingList() {
        try {
            const response = await this.apiRequest('waiting-list');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load waiting list:', error);
            return [];
        }
    }
    
    /**
     * Get loyalty rewards
     */
    async getLoyaltyRewards() {
        try {
            const response = await this.apiRequest('loyalty-rewards');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load loyalty rewards:', error);
            return [];
        }
    }
    
    /**
     * Redeem reward
     */
    async redeemReward(rewardId) {
        try {
            const response = await this.apiRequest('redeem-reward', {
                method: 'POST',
                body: JSON.stringify({ reward_id })
            });
            
            if (response.success) {
                this.showToast('Reward redeemed successfully', 'success');
                // Reload user data to update points
                await this.loadCurrentUserSimple();
            } else {
                this.showToast('Failed to redeem reward', 'error');
            }
        } catch (error) {
            console.error('Failed to redeem reward:', error);
            this.showToast('Failed to redeem reward', 'error');
        }
    }
    
    /**
     * Get transactions
     */
    async getTransactions() {
        try {
            const response = await this.apiRequest('transactions');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load transactions:', error);
            return [];
        }
    }
    
    /**
     * Get reviews
     */
    async getReviews() {
        try {
            const response = await this.apiRequest('reviews');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load reviews:', error);
            return [];
        }
    }
    
    /**
     * Add review
     */
    async addReview(reviewData) {
        try {
            const response = await this.apiRequest('add-review', {
                method: 'POST',
                body: JSON.stringify(reviewData)
            });
            
            if (response.success) {
                this.showToast('Review submitted successfully', 'success');
                // Reload user data to update points
                await this.loadCurrentUserSimple();
            } else {
                this.showToast('Failed to submit review', 'error');
            }
        } catch (error) {
            console.error('Failed to add review:', error);
            this.showToast('Failed to add review', 'error');
        }
    }
    
    /**
     * Get points history
     */
    async getPointsHistory() {
        try {
            const response = await this.apiRequest('points-history');
            return response.success ? response.data : [];
        } catch (error) {
            console.error('Failed to load points history:', error);
            return [];
        }
    }
    
    /**
     * Refresh session
     */
    async refreshSession() {
        try {
            const sessionResponse = await this.apiRequest('../login-register/api/auth/auth.php/check-session');
            if (sessionResponse.success) {
                // Session is still valid, no action needed
            } else {
                // Session expired, redirect to login
                window.location.href = '../login-register/login_form.html';
            }
        } catch (error) {
            console.error('Failed to refresh session:', error);
        }
    }
    
    /**
     * Show logout confirmation modal
     */
    showLogoutModal() {
        // Create modal if it doesn't exist
        if (!document.getElementById('logoutModal')) {
            const modalHTML = `
                <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl transform transition-all">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-hotel text-2xl text-amber-600"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800 mb-2">Leaving Lùcas Hotel & Restaurant?</h3>
                            <p class="text-sm text-slate-600 mb-4">Are you sure you want to logout? You'll be redirected to the login page.</p>
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700">
                                <i class="fa-regular fa-clock mr-1"></i> Your session will be ended immediately
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="window.dbAPI.closeLogoutModal()" class="flex-1 border border-slate-200 text-slate-700 px-4 py-3 rounded-xl font-medium hover:bg-slate-50 transition">
                                <i class="fa-regular fa-xmark mr-2"></i>Cancel
                            </button>
                            <button onclick="window.dbAPI.confirmLogout()" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-3 rounded-xl font-medium transition">
                                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>Logout
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        // Show modal
        const modal = document.getElementById('logoutModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Add animation
        setTimeout(() => {
            modal.querySelector('.bg-white').classList.add('scale-100');
        }, 10);
    }
    
    /**
     * Close logout modal
     */
    closeLogoutModal() {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.querySelector('.bg-white').classList.remove('scale-100');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    }
    
    /**
     * Confirm logout and proceed
     */
    async confirmLogout() {
        this.closeLogoutModal();
        
        try {
            const response = await this.apiRequest('../login-register/api/auth/auth.php/logout', {
                method: 'POST'
            });
            
            if (response.success) {
                // Clear current user data
                this.currentUser = null;
                
                // Show success message
                this.showToast('Successfully logged out of Lùcas Hotel & Restaurant', 'success');
                
                // Small delay before redirect for user to see the message
                setTimeout(() => {
                    // Redirect to login page
                    window.location.href = response.data.redirect_to;
                }, 1500);
            } else {
                this.showToast('Logout failed', 'error');
            }
        } catch (error) {
            console.error('Logout error:', error);
            this.showToast('Network error during logout', 'error');
        }
    }

    /**
     * Logout user with custom modal
     */
    async logout() {
        this.showLogoutModal();
    }
    
    /**
     * Check if user is logged in
     */
    async checkSession() {
        try {
            const response = await this.apiRequest('../login-register/api/auth/auth.php/check-session');
            return response.success ? response.data : null;
        } catch (error) {
            console.error('Session check error:', error);
            return null;
        }
    }
}

// Initialize database API
window.dbAPI = new DatabaseAPI();
