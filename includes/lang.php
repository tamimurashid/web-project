<?php
/**
 * Ujamaa Hostel - Localization System
 */

session_start();

// Set language based on GET parameter, session, or default
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'] === 'sw' ? 'sw' : 'en';
    $_SESSION['lang'] = $lang;
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
}

$translations = [
    'en' => [
        'nav_home' => 'Home',
        'nav_about' => 'About',
        'nav_rooms' => 'Rooms',
        'nav_community' => 'Community',
        'nav_contact' => 'Contact',
        'nav_book' => 'Book Now',
        
        'hero_badge' => 'Njiro, Arusha - Tanzania',
        'hero_title_1' => 'Your Home',
        'hero_title_2' => 'Away From Home',
        'hero_desc' => 'Experience authentic Tanzanian hospitality, communal dinners, and a vibrant volunteer community at the gateway to Serengeti & Kilimanjaro.',
        'hero_cta_book' => 'Book Your Stay',
        'hero_cta_rooms' => 'Explore Rooms',
        
        'stat_rating' => 'Guest Rating',
        'stat_laundry' => 'Laundry & Dinner',
        'stat_guests' => 'Happy Guests',
        'stat_malls' => 'Walk to Malls',
        
        'booking_checkin' => 'Check-in',
        'booking_checkout' => 'Check-out',
        'booking_room_type' => 'Room Type',
        'booking_room_placeholder' => 'Choose a room...',
        'booking_cta' => 'Check Availability',
        
        'about_subtitle' => 'Karibu Sana',
        'about_title_1' => 'More Than Just a Hostel. It\'s a',
        'about_title_2' => 'Family',
        'about_desc' => 'Located in the safe and quiet suburb of Njiro, Ujamaa Hostel is the primary hub for international volunteers and travelers in Arusha. We provide a cozy, home-away-from-home environment where you can connect with like-minded people.',
        'about_feat_security' => '24h Security',
        'about_feat_bikes' => 'Free Bicycles',
        'about_feat_breakfast' => 'Free Breakfast',
        'about_feat_mamas' => 'The \'Mamas\'',
        
        'rooms_title' => 'Our Accommodations',
        'rooms_desc' => 'Simple, clean, and comfortable rooms designed for your comfort.',
        'room_price_night' => 'NIGHT',
        'room_feat_laundry' => 'Free Laundry Service',
        'room_feat_dinner' => 'Communal Dinner Included',
        'room_feat_guests' => 'Up to %d Guests',
        'room_cta' => 'Book This Room',
        
        'community_title_1' => 'Feel the',
        'community_title_2' => 'Spirit of Ujamaa',
        'community_desc' => 'Ujamaa means family-hood in Swahili. Every night, we gather for a communal dinner prepared by our wonderful local \'Mamas\'. It\'s the best time to share stories and plan your next safari.',
        
        'contact_title' => 'Get In Touch',
        'contact_desc' => 'Have questions about volunteering or your stay? We\'d love to hear from you.',
        'contact_loc' => 'Our Location',
        'contact_phone' => 'Phone',
        'contact_email' => 'Email',
        'contact_first_name' => 'First Name',
        'contact_last_name' => 'Last Name',
        'contact_subject' => 'Subject',
        'contact_message' => 'Message',
        'contact_cta' => 'Send Message',
        
        'map_title' => 'Find Us in Arusha',
        'map_desc' => 'We are located in the peaceful Njiro area, just a short walk from major amenities.'
    ],
    'sw' => [
        'nav_home' => 'Mwanzo',
        'nav_about' => 'Kuhusu',
        'nav_rooms' => 'Vyumba',
        'nav_community' => 'Jamii',
        'nav_contact' => 'Mawasiliano',
        'nav_book' => 'Weka Nafasi',
        
        'hero_badge' => 'Njiro, Arusha - Tanzania',
        'hero_title_1' => 'Nyumba Yako',
        'hero_title_2' => 'Mbali na Nyumbani',
        'hero_desc' => 'Pata ukarimu wa kweli wa Kitanzania, chakula cha pamoja cha jioni, na jamii mahususi ya watu wa kujitolea kwenye lango la Serengeti na Kilimanjaro.',
        'hero_cta_book' => 'Weka Nafasi Sasa',
        'hero_cta_rooms' => 'Angalia Vyumba',
        
        'stat_rating' => 'Daraja la Wageni',
        'stat_laundry' => 'Dobi na Chakula',
        'stat_guests' => 'Wageni Wetu',
        'stat_malls' => 'Malls Karibu',
        
        'booking_checkin' => 'Kuingia',
        'booking_checkout' => 'Kutoka',
        'booking_room_type' => 'Aina ya Chumba',
        'booking_room_placeholder' => 'Chagua chumba...',
        'booking_cta' => 'Angalia Nafasi',
        
        'about_subtitle' => 'Karibu Sana',
        'about_title_1' => 'Zaidi ya Hosteli. Sisi ni',
        'about_title_2' => 'Familia',
        'about_desc' => 'Iko katika kitongoji salama na tulivu cha Njiro, Ujamaa Hostel ni kituo kikuu kwa wajitolea wa kimataifa na wasafiri jijini Arusha. Tunatoa mazingira tulivu ambapo unaweza kuungana na watu wenye maoni kama yako.',
        'about_feat_security' => 'Ulinzi Saa 24',
        'about_feat_bikes' => 'Baiskeli Bure',
        'about_feat_breakfast' => 'Chai ya Asubuhi',
        'about_feat_mamas' => 'Kina \'Mama\'',
        
        'rooms_title' => 'Malazi Yetu',
        'rooms_desc' => 'Vyumba rahisi, safi, na vya kustarehe vilivyoundwa kwa ajili ya faraja yako.',
        'room_price_night' => 'USIKU',
        'room_feat_laundry' => 'Huduma ya Dobi Bure',
        'room_feat_dinner' => 'Chakula cha Jioni Bure',
        'room_feat_guests' => 'Hadi Wageni %d',
        'room_cta' => 'Chagua Chumba hiki',
        
        'community_title_1' => 'Hisi',
        'community_title_2' => 'Roho ya Ujamaa',
        'community_desc' => '\'Ujamaa\' maana yake ni undugu kwa Kiswahili. Kila usiku tunajumuika kwa chakula cha jioni kilichoandaliwa na \'Mama\' zetu wazuri. Ni wakati mzuri wa kubadilishana mawazo na kupanga safari yako ijayo.',
        
        'contact_title' => 'Wasiliana Nasi',
        'contact_desc' => 'Una maswali kuhusu kujitolea au kukaa kwako? Tungependa kusikia kutoka kwako.',
        'contact_loc' => 'Mahali Tulipo',
        'contact_phone' => 'Simu',
        'contact_email' => 'Barua Pepe',
        'contact_first_name' => 'Jina la Kwanza',
        'contact_last_name' => 'Jina la Mwisho',
        'contact_subject' => 'Mada',
        'contact_message' => 'Ujumbe',
        'contact_cta' => 'Tuma Ujumbe',
        
        'map_title' => 'Tupate Jijini Arusha',
        'map_desc' => 'Tunapatikana katika eneo lenye amani la Njiro, umbali mfupi kutoka kwa huduma muhimu.'
    ]
];

/**
 * Translation helper function
 */
function __($key, ...$args) {
    global $translations, $lang;
    $text = isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
    if (!empty($args)) {
        return vsprintf($text, $args);
    }
    return $text;
}

/**
 * Get current language code
 */
function getCurrentLang() {
    global $lang;
    return $lang;
}
