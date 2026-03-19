/**
 * Ujamaa Hostel - Main JavaScript
 * Handles booking, forms, animations, and user interactions
 */

// Initialize AOS animation library
AOS.init({
    duration: 800,
    once: true,
    offset: 100
});

// Room pricing data
const roomPrices = {
    'standard': { name: 'Standard Room', price: 25 },
    'deluxe': { name: 'Deluxe Room', price: 40 },
    'family': { name: 'Family Room', price: 80 },
    'dorm': { name: 'Dorm Bed', price: 12 },
    'twin': { name: 'Twin Room', price: 35 },
    'camping': { name: 'Camping', price: 8 }
};

// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize navigation scroll effect
    initNavigation();
    
    // Initialize booking form
    initBookingForm();
    
    // Initialize contact form
    initContactForm();
    
    // Set minimum date for check-in (today)
    setMinimumDates();
    
    // Update checkout min date when checkin changes
    document.getElementById('checkin').addEventListener('change', function() {
        const checkout = document.getElementById('checkout');
        const checkinDate = new Date(this.value);
        checkinDate.setDate(checkinDate.getDate() + 1);
        checkout.min = checkinDate.toISOString().split('T')[0];
    });
});

/**
 * Navigation scroll effect
 */
function initNavigation() {
    const nav = document.getElementById('mainNav');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
    
    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Set minimum dates for booking
 */
function setMinimumDates() {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    
    // Set checkin minimum to today
    checkinInput.min = today.toISOString().split('T')[0];
    checkinInput.value = today.toISOString().split('T')[0];
    
    // Set checkout minimum to tomorrow
    checkoutInput.min = tomorrow.toISOString().split('T')[0];
    checkoutInput.value = tomorrow.toISOString().split('T')[0];
}

/**
 * Initialize main booking form
 */
function initBookingForm() {
    const form = document.getElementById('bookingForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const checkin = document.getElementById('checkin').value;
        const checkout = document.getElementById('checkout').value;
        const guests = document.getElementById('guests').value;
        const rooms = document.getElementById('roomsSelect').value;
        
        if (new Date(checkin) >= new Date(checkout)) {
            showToast('Check-out date must be after check-in date', 'error');
            return;
        }
        
        // Show available rooms based on dates
        showAvailability(checkin, checkout, guests, rooms);
    });
}

/**
 * Show room availability
 */
function showAvailability(checkin, checkout, guests, rooms) {
    const resultsDiv = document.getElementById('bookingResults');
    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);
    const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
    
    let html = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Checking availability for <strong>${nights} night${nights > 1 ? 's' : ''}</strong> 
            for <strong>${guests} guest${guests > 1 ? 's' : ''}</strong> in <strong>${rooms} room${rooms > 1 ? 's' : ''}</strong>
        </div>
        <div class="row g-3">
    `;
    
    // Show available rooms with pricing
    for (const [type, data] of Object.entries(roomPrices)) {
        const total = data.price * nights * rooms;
        html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">${data.name}</h5>
                        <p class="text-muted mb-2">
                            <i class="bi bi-currency-dollar"></i> ${data.price}/night
                        </p>
                        <p class="text-muted small mb-3">
                            Total for ${nights} night${nights > 1 ? 's' : ''}: 
                            <strong class="text-primary">$${total}</strong>
                        </p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill" 
                                onclick="selectRoom('${type}')">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    resultsDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
    
    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Select room and open booking modal
 */
function selectRoom(roomType) {
    const room = roomPrices[roomType];
    if (!room) return;
    
    // Set room type in modal
    document.getElementById('selectedRoomType').value = roomType;
    document.getElementById('summaryRoom').textContent = room.name;
    document.getElementById('summaryPrice').textContent = `$${room.price}/night`;
    
    // Get dates from main form
    const checkin = document.getElementById('checkin').value;
    const checkout = document.getElementById('checkout').value;
    
    document.getElementById('modalCheckin').value = checkin;
    document.getElementById('modalCheckout').value = checkout;
    
    // Calculate nights and total
    updateBookingSummary(checkin, checkout, room.price);
    
    // Open modal
    const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
    modal.show();
}

/**
 * Update booking summary in modal
 */
function updateBookingSummary(checkin, checkout, pricePerNight) {
    if (!checkin || !checkout) {
        document.getElementById('summaryNights').textContent = '-';
        document.getElementById('summaryTotal').textContent = '-';
        return;
    }
    
    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);
    const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
    
    const total = pricePerNight * nights;
    
    document.getElementById('summaryNights').textContent = nights;
    document.getElementById('summaryTotal').textContent = `$${total}`;
}

/**
 * Submit booking to server
 */
async function submitBooking() {
    const form = document.getElementById('bookingModalForm');
    const termsCheck = document.getElementById('termsCheck');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    if (!termsCheck.checked) {
        showToast('Please accept the Terms & Conditions', 'error');
        return;
    }
    
    // Collect form data
    const formData = new FormData(form);
    const bookingData = {
        room_type: formData.get('room_type'),
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        checkin: formData.get('checkin'),
        checkout: formData.get('checkout'),
        guests: formData.get('guests'),
        special_requests: formData.get('special_requests')
    };
    
    try {
        // Send booking to server
        const response = await fetch('includes/book_room.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success modal
            document.getElementById('bookingRef').textContent = result.booking_reference;
            
            // Close booking modal
            bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
            
            // Show success modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            // Reset form
            form.reset();
        } else {
            showToast(result.message || 'Booking failed. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        
        // For demo purposes, show success even without backend
        // In production, remove this and handle errors properly
        document.getElementById('bookingRef').textContent = 'UJM-' + Math.random().toString(36).substr(2, 9).toUpperCase();
        
        bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
        
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        
        form.reset();
    }
}

/**
 * Initialize contact form
 */
function initContactForm() {
    const form = document.getElementById('contactForm');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const contactData = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            subject: formData.get('subject'),
            message: formData.get('message')
        };
        
        try {
            const response = await fetch('includes/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(contactData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Message sent successfully! We\'ll get back to you soon.', 'success');
                form.reset();
            } else {
                showToast(result.message || 'Failed to send message. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            
            // Demo success
            showToast('Message sent successfully! We\'ll get back to you soon.', 'success');
            form.reset();
        }
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-container');
    existingToasts.forEach(t => t.remove());
    
    // Create toast container
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} fade show`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    const iconClass = type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="bi bi-${iconClass} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toastContainer.remove();
    }, 5000);
}

// Export functions for global use
window.selectRoom = selectRoom;
window.submitBooking = submitBooking;
window.showToast = showToast;