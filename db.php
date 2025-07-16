<?php
// Database configuration
$host = 'localhost';
$dbname = 'dbplwpcii0tguw';
$username = 'ulnrcogla9a1t';
$password = 'yolpwow1mwr2';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get all hotels
function getAllHotels($pdo) {
    $stmt = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC");
    return $stmt->fetchAll();
}

// Function to search hotels
function searchHotels($pdo, $destination, $checkin, $checkout) {
    $sql = "SELECT DISTINCT h.* FROM hotels h 
            JOIN room_types rt ON h.id = rt.hotel_id 
            WHERE (h.city LIKE ? OR h.location LIKE ? OR h.name LIKE ?) 
            AND rt.available_rooms > 0
            ORDER BY h.rating DESC";
    
    $searchTerm = "%$destination%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

// Function to get hotel by ID
function getHotelById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Function to get room types for a hotel
function getRoomTypes($pdo, $hotel_id) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE hotel_id = ? AND available_rooms > 0");
    $stmt->execute([$hotel_id]);
    return $stmt->fetchAll();
}

// Function to create booking
function createBooking($pdo, $data) {
    $sql = "INSERT INTO bookings (hotel_id, room_type_id, guest_name, guest_email, guest_phone, 
            check_in_date, check_out_date, guests, total_nights, total_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['hotel_id'],
        $data['room_type_id'],
        $data['guest_name'],
        $data['guest_email'],
        $data['guest_phone'],
        $data['check_in_date'],
        $data['check_out_date'],
        $data['guests'],
        $data['total_nights'],
        $data['total_amount']
    ]);
}

// Function to get booking by ID
function getBookingById($pdo, $id) {
    $sql = "SELECT b.*, h.name as hotel_name, rt.room_type, rt.price_per_night 
            FROM bookings b 
            JOIN hotels h ON b.hotel_id = h.id 
            JOIN room_types rt ON b.room_type_id = rt.id 
            WHERE b.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>
