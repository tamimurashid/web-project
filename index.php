<?php
/**
 * Ujamaa Hostel - Premium Modern Landing Page
 * Arusha, Tanzania - Gateway to Kilimanjaro & Safari
 */

require_once 'includes/db.php';
require_once 'includes/lang.php';

// Get data from database
$rooms = [];
$testimonials = [];
$pdo = getDBConnection();

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM rooms WHERE is_available = TRUE ORDER BY price_per_night ASC");
        $rooms = $stmt->fetchAll();
        
        $stmt = $pdo->query("SELECT * FROM testimonials WHERE is_active = TRUE ORDER BY RAND() LIMIT 6");
        $testimonials = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujamaa Hostel | <?= __('hero_title_2') ?></title>
    <meta name="description" content="<?= __('hero_desc') ?>">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-bs-spy="scroll" data-bs-target="#mainNav">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top glass-nav" id="mainNav">
        <div class="container border-bottom border-white border-opacity-10 pb-2">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <span class="brand-text fw-bold">UJAMAA <span class="text-primary">HOSTEL</span></span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="bi bi-list fs-1"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-lg-3 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#home"><?= __('nav_home') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#about"><?= __('nav_about') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#rooms"><?= __('nav_rooms') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#community"><?= __('nav_community') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact"><?= __('nav_contact') ?></a></li>
                    <li class="nav-item ms-lg-3">
                        <div class="lang-switcher dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-translate me-2"></i><?= strtoupper(getCurrentLang()) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                <li><a class="dropdown-item <?= getCurrentLang() == 'en' ? 'active' : '' ?>" href="?lang=en">English</a></li>
                                <li><a class="dropdown-item <?= getCurrentLang() == 'sw' ? 'active' : '' ?>" href="?lang=sw">Kiswahili</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="#booking" class="btn btn-primary px-4 rounded-pill ms-lg-2"><?= __('nav_book') ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header id="home" class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content text-center" data-aos="zoom-out" data-aos-duration="1500">
            <div class="badge rounded-pill px-3 py-2 mb-4 animate-fade-in-up bg-white text-dark shadow-sm">
                <i class="bi bi-geo-alt-fill me-2 text-primary"></i><?= __('hero_badge') ?>
            </div>
            <h1 class="display-1 mb-4 fw-bold"><?= __('hero_title_1') ?> <span class="text-secondary"><?= __('hero_title_2') ?></span></h1>
            <p class="lead mb-5 mx-auto opacity-90" style="max-width: 700px;">
                <?= __('hero_desc') ?>
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="#booking" class="btn btn-primary btn-lg px-5 py-3 shadow-lg">
                    <i class="bi bi-calendar-check me-2"></i><?= __('hero_cta_book') ?>
                </a>
                <a href="#rooms" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill border-2">
                    <?= __('hero_cta_rooms') ?>
                </a>
            </div>
        </div>

        <div class="quick-stats d-none d-lg-block">
            <div class="container">
                <div class="row text-center text-white">
                    <div class="col-md-3 border-end border-white border-opacity-10">
                        <h3 class="text-secondary mb-0 fw-bold">4.8★</h3>
                        <p class="small text-uppercase mb-0 opacity-70"><?= __('stat_rating') ?></p>
                    </div>
                    <div class="col-md-3 border-end border-white border-opacity-10">
                        <h3 class="text-secondary mb-0 fw-bold">FREE</h3>
                        <p class="small text-uppercase mb-0 opacity-70"><?= __('stat_laundry') ?></p>
                    </div>
                    <div class="col-md-3 border-end border-white border-opacity-10">
                        <h3 class="text-secondary mb-0 fw-bold">500+</h3>
                        <p class="small text-uppercase mb-0 opacity-70"><?= __('stat_guests') ?></p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-secondary mb-0 fw-bold">5m</h3>
                        <p class="small text-uppercase mb-0 opacity-70"><?= __('stat_malls') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Booking Widget -->
    <section id="booking" class="booking-widget-section">
        <div class="container">
            <div class="booking-widget glass-card p-4 p-lg-5 rounded-4" data-aos="fade-up">
                <form id="quickBookingForm" class="row g-4 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase opacity-70 mb-2"><?= __('booking_checkin') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event text-primary"></i></span>
                            <input type="text" id="checkin" class="form-control border-start-0" name="checkin" placeholder="Select date">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase opacity-70 mb-2"><?= __('booking_checkout') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-check text-primary"></i></span>
                            <input type="text" id="checkout" class="form-control border-start-0" name="checkout" placeholder="Select date">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase opacity-70 mb-2"><?= __('booking_room_type') ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-house-door text-primary"></i></span>
                            <select id="roomType" class="form-select border-start-0" name="room_type">
                                <option value=""><?= __('booking_room_placeholder') ?></option>
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?= htmlspecialchars($room['room_type']) ?>"><?= htmlspecialchars($room['room_name']) ?> - $<?= number_format($room['price_per_night']) ?>/night</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow-sm">
                            <?= __('booking_cta') ?>
                        </button>
                    </div>
                </form>
                <div id="quickBookingResult" class="mt-3"></div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 my-5">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <span class="text-primary fw-bold text-uppercase mb-3 d-block letter-spacing-2"><?= __('about_subtitle') ?></span>
                    <h2 class="display-5 fw-bold mb-4"><?= __('about_title_1') ?> <span class="text-primary"><?= __('about_title_2') ?></span></h2>
                    <p class="text-muted fs-5 mb-5 opacity-90">
                        <?= __('about_desc') ?>
                    </p>
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3 feature-card">
                                <div class="icon-box p-3 bg-primary bg-opacity-10 text-primary rounded-4"><i class="bi bi-shield-check fs-3"></i></div>
                                <span class="fw-bold"><?= __('about_feat_security') ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3 feature-card">
                                <div class="icon-box p-3 bg-primary bg-opacity-10 text-primary rounded-4"><i class="bi bi-bicycle fs-3"></i></div>
                                <span class="fw-bold"><?= __('about_feat_bikes') ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3 feature-card">
                                <div class="icon-box p-3 bg-primary bg-opacity-10 text-primary rounded-4"><i class="bi bi-cup-hot fs-3"></i></div>
                                <span class="fw-bold"><?= __('about_feat_breakfast') ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3 feature-card">
                                <div class="icon-box p-3 bg-primary bg-opacity-10 text-primary rounded-4"><i class="bi bi-heart fs-3"></i></div>
                                <span class="fw-bold"><?= __('about_feat_mamas') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="position-relative">
                        <div class="about-image-wrapper p-3 bg-white shadow-xl rounded-5 rotate-2">
                            <img src="images/communal.png" class="img-fluid rounded-5" alt="Communal Area">
                        </div>
                        <div class="position-absolute dropdown-badge bottom-0 start-0 m-4 p-4 glass-card rounded-4 d-none d-md-block shadow-lg" style="max-width: 280px; transform: rotate(-3deg);">
                            <div class="text-secondary mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                            <p class="mb-0 fst-italic fw-medium small">"The mamas are incredible and the food is the best in Arusha!"</p>
                            <div class="mt-2 small fw-bold text-primary">- Sarah, UK</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="rooms" class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="text-primary fw-bold text-uppercase small letter-spacing-2 mb-2 d-block"><?= __('nav_rooms') ?></span>
                <h2 class="display-5 fw-bold"><?= __('rooms_title') ?></h2>
                <p class="text-muted mx-auto" style="max-width: 600px;"><?= __('rooms_desc') ?></p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $index => $room): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="room-card glass-card h-100 overflow-hidden border-0 shadow-sm">
                            <div class="room-image position-relative" style="height: 250px;">
                                <span class="room-badge position-absolute top-0 end-0 m-3 shadow-sm">$<?= number_format($room['price_per_night']) ?> / <?= __('room_price_night') ?></span>
                                <img src="images/<?= strpos($room['room_type'], 'dorm') !== false ? 'dorm.png' : 'private.png' ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($room['room_name']) ?>">
                            </div>
                            <div class="room-content p-4">
                                <h3 class="fw-bold mb-3 h4"><?= htmlspecialchars($room['room_name']) ?></h3>
                                <p class="text-muted mb-4 small opacity-75"><?= htmlspecialchars($room['description']) ?></p>
                                <ul class="list-unstyled mb-4 small">
                                    <li class="mb-2"><i class="bi bi-stars text-primary me-2"></i> <?= __('room_feat_laundry') ?></li>
                                    <li class="mb-2"><i class="bi bi-cup-straw text-primary me-2"></i> <?= __('room_feat_dinner') ?></li>
                                    <li class="mb-2"><i class="bi bi-people text-primary me-2"></i> <?= __('room_feat_guests', $room['capacity']) ?></li>
                                </ul>
                                <button onclick="selectRoom('<?= htmlspecialchars($room['room_type']) ?>')" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-bold"><?= __('room_cta') ?></button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Community Section -->
    <section id="community" class="py-5">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 order-lg-2" data-aos="fade-left">
                    <span class="text-primary fw-bold text-uppercase small letter-spacing-2 mb-2 d-block"><?= __('nav_community') ?></span>
                    <h2 class="display-5 fw-bold mb-4"><?= __('community_title_1') ?> <span class="text-primary"><?= __('community_title_2') ?></span></h2>
                    <p class="text-muted mb-5 fs-5">
                        <?= __('community_desc') ?>
                    </p>
                    
                    <div id="testimonialCarousel" class="carousel slide testimonial-carousel" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($testimonials as $index => $t): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <div class="glass-card p-4 rounded-4 border-primary border-opacity-10 bg-white">
                                    <div class="d-flex gap-1 mb-3 text-secondary">
                                        <?php for($i=0; $i<$t['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                    </div>
                                    <p class="fst-italic opacity-80 mb-4 h5 fw-normal">"<?= htmlspecialchars($t['comment']) ?>"</p>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 50px; height: 50px;">
                                            <?= strtoupper(substr($t['guest_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($t['guest_name']) ?></div>
                                            <div class="text-muted tiny text-uppercase letter-spacing-1" style="font-size: 0.7rem;">Verified Guest</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-indicators position-relative mt-4 mb-0">
                            <?php foreach ($testimonials as $index => $t): ?>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 order-lg-1" data-aos="fade-right">
                    <div class="row g-3">
                        <div class="col-12"><img src="images/hero.png" class="img-fluid rounded-5 shadow-2xl" alt="Social Garden"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section id="location" class="py-5 bg-dark text-white overflow-hidden">
        <div class="container py-lg-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-4" data-aos="fade-right">
                    <span class="text-primary fw-bold text-uppercase small letter-spacing-2 mb-2 d-block"><?= __('contact_loc') ?></span>
                    <h2 class="display-5 fw-bold mb-4 text-white"><?= __('map_title') ?></h2>
                    <p class="opacity-70 mb-5"><?= __('map_desc') ?></p>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                            <p class="mb-0 opacity-90">Njiro Block A, Plot 24, Arusha, Tanzania</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-telephone-fill text-primary fs-5"></i>
                            <p class="mb-0 opacity-90">+255 753 960 570</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8" data-aos="fade-left">
                    <div class="map-wrapper border-0">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15940.345385750035!2d36.6974123!3d-3.3768817!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x183741584c3ea917%3A0x6d9f9570954a1a5!2sUjamaa%20Hostel!5e0!3m2!1sen!2stz!4v1710777000000!5m2!1sen!2stz" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Feedback -->
    <section id="contact" class="py-5 bg-light">
        <div class="container py-lg-5">
            <div class="row g-5">
                <div class="col-lg-5" data-aos="fade-right">
                    <h2 class="display-5 fw-bold mb-4"><?= __('contact_title') ?></h2>
                    <p class="text-muted mb-5"><?= __('contact_desc') ?></p>
                    
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-center gap-4 group-hover-primary">
                            <div class="icon-box p-3 bg-white shadow-sm rounded-circle text-primary transition-all"><i class="bi bi-geo-alt fs-4"></i></div>
                            <div>
                                <div class="fw-bold"><?= __('contact_loc') ?></div>
                                <div class="text-muted small">Njiro Block A, Plot 24, Arusha, Tanzania</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4 group-hover-primary">
                            <div class="icon-box p-3 bg-white shadow-sm rounded-circle text-primary transition-all"><i class="bi bi-telephone fs-4"></i></div>
                            <div>
                                <div class="fw-bold"><?= __('contact_phone') ?></div>
                                <div class="text-muted small">+255 753 960 570</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4 group-hover-primary">
                            <div class="icon-box p-3 bg-white shadow-sm rounded-circle text-primary transition-all"><i class="bi bi-envelope fs-4"></i></div>
                            <div>
                                <div class="fw-bold"><?= __('contact_email') ?></div>
                                <div class="text-muted small">booking.ujamaa.hostel@gmail.com</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="glass-card p-4 p-lg-5 rounded-4 bg-white shadow-lg">
                        <form id="contactForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small opacity-75"><?= __('contact_first_name') ?></label>
                                    <input type="text" class="form-control bg-light border-0" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small opacity-75"><?= __('contact_last_name') ?></label>
                                    <input type="text" class="form-control bg-light border-0" name="last_name" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small opacity-75"><?= __('contact_email') ?></label>
                                    <input type="email" class="form-control bg-light border-0" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small opacity-75"><?= __('contact_subject') ?></label>
                                    <input type="text" class="form-control bg-light border-0" name="subject" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small opacity-75"><?= __('contact_message') ?></label>
                                    <textarea class="form-control bg-light border-0" name="message" rows="4" required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill mt-3 shadow-primary">
                                        <?= __('contact_cta') ?> <i class="bi bi-send ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="contactResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <div>
                        <h4 class="modal-title fw-bold mb-0">Complete Your Stay</h4>
                        <p class="small opacity-75 mb-0">Just a few more details to confirm your visit</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 p-lg-5">
                    <form id="fullBookingForm">
                        <input type="hidden" name="room_type" id="bookingRoomType">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small opacity-75"><?= __('contact_first_name') ?></label>
                                <input type="text" class="form-control bg-light border-0" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small opacity-75"><?= __('contact_last_name') ?></label>
                                <input type="text" class="form-control bg-light border-0" name="last_name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small opacity-75"><?= __('contact_email') ?></label>
                                <input type="email" class="form-control bg-light border-0" name="email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small opacity-75"><?= __('contact_phone') ?></label>
                                <input type="tel" class="form-control bg-light border-0" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small opacity-75"><?= __('booking_checkin') ?></label>
                                <input type="text" class="form-control bg-light border-0" id="bookingCheckin" name="checkin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small opacity-75"><?= __('booking_checkout') ?></label>
                                <input type="text" class="form-control bg-light border-0" id="bookingCheckout" name="checkout" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small opacity-75">Number of Guests</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-people text-primary"></i></span>
                                    <select class="form-select bg-light border-0" name="guests" id="bookingGuests">
                                        <option value="1">1 Guest</option>
                                        <option value="2">2 Guests</option>
                                        <option value="3">3 Guests</option>
                                        <option value="4">4 Guests</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="bookingSummary" class="mt-4 p-3 bg-primary bg-opacity-10 rounded-4 d-none"></div>
                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-primary">
                                Confirm & Book Now
                            </button>
                        </div>
                    </form>
                    <div id="fullBookingResult" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-5 text-white bg-dark">
        <div class="container py-lg-4">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h3 class="fw-bold mb-4 text-white">UJAMAA <span class="text-primary">HOSTEL</span></h3>
                    <p class="opacity-70 mb-4">Your safe, social, and authentic gateway to the wonders of Tanzania. Join our community of travelers and volunteers.</p>
                    <div class="d-flex gap-3 social-links">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-4">Quick Links</h5>
                    <ul class="list-unstyled flex-column d-flex gap-2 opacity-70 small">
                        <li><a href="#home" class="text-white text-decoration-none hover-primary"><?= __('nav_home') ?></a></li>
                        <li><a href="#about" class="text-white text-decoration-none hover-primary"><?= __('nav_about') ?></a></li>
                        <li><a href="#rooms" class="text-white text-decoration-none hover-primary"><?= __('nav_rooms') ?></a></li>
                        <li><a href="#contact" class="text-white text-decoration-none hover-primary"><?= __('nav_contact') ?></a></li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h5 class="fw-bold mb-4">Social Hub</h5>
                    <p class="opacity-70 mb-4 small">Located within walking distance to Njiro Cinema Complex and major malls. Safe, quiet, and friendly.</p>
                    <div class="text-center opacity-30 small mt-5 pt-5 border-top border-white border-opacity-10">
                        © 2026 Ujamaa Hostel Arusha. All Rights Reserved.
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        AOS.init({ duration: 1000, once: true });
        
        // Navbar change on scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled', 'navbar-light');
                nav.classList.remove('navbar-dark');
            } else {
                nav.classList.remove('scrolled', 'navbar-light');
                nav.classList.add('navbar-dark');
            }
        });

        // Initialize Flatpickr
        const fpOptions = { minDate: "today", dateFormat: "Y-m-d" };
        flatpickr("#checkin", fpOptions);
        flatpickr("#checkout", fpOptions);
        flatpickr("#bookingCheckin", fpOptions);
        flatpickr("#bookingCheckout", fpOptions);

        // Quick Booking
        document.getElementById('quickBookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const roomType = document.getElementById('roomType').value;
            
            if (!checkin || !checkout || !roomType) {
                alert('Please fill in all fields');
                return;
            }

            document.getElementById('bookingRoomType').value = roomType;
            document.getElementById('bookingCheckin').value = checkin;
            document.getElementById('bookingCheckout').value = checkout;
            
            calculateTotal();
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        });

        function calculateTotal() {
            const checkin = document.getElementById('bookingCheckin').value;
            const checkout = document.getElementById('bookingCheckout').value;
            const roomType = document.getElementById('bookingRoomType').value;
            
            if (checkin && checkout && roomType) {
                const prices = {
                    'dorm': 20,
                    'standard': 45,
                    'deluxe': 55,
                    'twin': 45,
                    'family': 100,
                    'camping': 15
                };
                const price = prices[roomType] || 20;
                const date1 = new Date(checkin);
                const date2 = new Date(checkout);
                const nights = Math.max(1, Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24)));
                const total = price * nights;
                
                const summary = document.getElementById('bookingSummary');
                summary.classList.remove('d-none');
                summary.innerHTML = `
                    <div class="d-flex justify-content-between mb-2">
                        <span>Rate:</span>
                        <span class="fw-bold">$${price} / night</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Stay:</span>
                        <span class="fw-bold">${nights} night(s)</span>
                    </div>
                    <hr class="my-2 border-primary border-opacity-25">
                    <div class="d-flex justify-content-between fs-5">
                        <span class="fw-bold text-primary">Est. Total:</span>
                        <span class="fw-bold text-primary">$${total}</span>
                    </div>
                `;
            }
        }

        function selectRoom(type) {
            document.getElementById('bookingRoomType').value = type;
            document.getElementById('roomType').value = type;
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }

        // AJAX Forms with localized feedback
        const lang = '<?= getCurrentLang() ?>';
        const messages = {
            en: { processing: 'Processing...', success: 'Success!', error: 'Error:', connection: 'Connection error. Please try again.' },
            sw: { processing: 'Inatuma...', success: 'Imefanikiwa!', error: 'Hitilafu:', connection: 'Tatizo la mtandao. Tafadhali jaribu tena.' }
        };

        async function handleFormSubmit(formId, endpoint, resultId) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                const msg = messages[lang] || messages.en;
                
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${msg.processing}`;

                try {
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData);
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    
                    const resultDiv = document.getElementById(resultId);
                    if (result.success) {
                        resultDiv.innerHTML = `<div class="alert alert-success mt-3 shadow-sm border-0 rounded-4">
                            <i class="bi bi-check-circle-fill me-2"></i><strong>${msg.success}</strong> ${result.message || ''}
                        </div>`;
                        form.reset();
                        if (formId === 'fullBookingForm') {
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                                if(modal) modal.hide();
                            }, 3000);
                        }
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger mt-3 shadow-sm border-0 rounded-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>${msg.error}</strong> ${result.message}
                        </div>`;
                    }
                } catch (error) {
                    document.getElementById(resultId).innerHTML = `<div class="alert alert-danger mt-3 shadow-sm border-0 rounded-4">${msg.connection}</div>`;
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }

        handleFormSubmit('fullBookingForm', 'includes/book_room.php', 'fullBookingResult');
        handleFormSubmit('contactForm', 'includes/contact.php', 'contactResult');
    </script>
</body>
</html>