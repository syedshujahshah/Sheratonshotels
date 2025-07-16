<?php
require_once 'db.php';

// Get search parameters
$destination = $_GET['destination'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$hotel_id = $_GET['hotel_id'] ?? '';

// Get hotels based on search or show specific hotel
if ($hotel_id) {
    $hotel = getHotelById($pdo, $hotel_id);
    $roomTypes = getRoomTypes($pdo, $hotel_id);
} else if ($destination) {
    $hotels = searchHotels($pdo, $destination, $checkin, $checkout);
} else {
    $hotels = getAllHotels($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hotel_id ? htmlspecialchars($hotel['name']) . ' - ' : ''; ?>Sheraton Hotels</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .header {
            background: linear-gradient(135deg, #1a365d 0%, #2d5a87 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #ffd700;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .search-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            padding: 0.8rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-btn {
            background: linear-gradient(135deg, #2d5a87 0%, #1a365d 100%);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: #1a365d;
            text-align: center;
        }

        .hotel-detail {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .hotel-header {
            position: relative;
            height: 400px;
            background-size: cover;
            background-position: center;
        }

        .hotel-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 2rem;
        }

        .hotel-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .hotel-location {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #ffd700;
        }

        .hotel-content {
            padding: 2rem;
        }

        .hotel-description {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
            color: #666;
        }

        .amenities {
            margin-bottom: 2rem;
        }

        .amenities h3 {
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .amenity-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .rooms-section {
            margin-top: 3rem;
        }

        .section-title {
            font-size: 2rem;
            color: #1a365d;
            margin-bottom: 2rem;
            text-align: center;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .room-card:hover {
            transform: translateY(-5px);
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .room-info {
            padding: 1.5rem;
        }

        .room-type {
            font-size: 1.3rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .room-description {
            color: #666;
            margin-bottom: 1rem;
        }

        .room-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .room-guests {
            color: #666;
        }

        .room-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2d5a87;
        }

        .book-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s;
        }

        .book-btn:hover {
            transform: translateY(-2px);
        }

        .hotels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .hotel-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .hotel-card:hover {
            transform: translateY(-5px);
        }

        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .hotel-info {
            padding: 1.5rem;
        }

        .hotel-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #1a365d;
        }

        .view-btn {
            background: linear-gradient(135deg, #2d5a87 0%, #1a365d 100%);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 2rem;
        }

        /* Date Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
        }

        .modal-title {
            color: #1a365d;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
        }

        .modal-form-group {
            margin-bottom: 1rem;
        }

        .modal-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .modal-form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal-btn {
            flex: 1;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }

        .modal-btn-confirm {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
        }

        .modal-btn-cancel {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .hotel-title {
                font-size: 2rem;
            }
            
            .rooms-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo" onclick="goHome()">SHERATON</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="hotels.php">Hotels</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <?php if (!$hotel_id): ?>
        <div class="search-bar">
            <form class="search-form" id="searchForm">
                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" value="<?php echo htmlspecialchars($destination); ?>" placeholder="Where are you going?">
                </div>
                <div class="form-group">
                    <label for="checkin">Check-in</label>
                    <input type="date" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                </div>
                <div class="form-group">
                    <label for="checkout">Check-out</label>
                    <input type="date" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($hotel_id && $hotel): ?>
            <button class="back-btn" onclick="goBack()">← Back to Hotels</button>
            
            <div class="hotel-detail">
                <div class="hotel-header" style="background-image: url('<?php echo htmlspecialchars($hotel['image_url']); ?>')">
                    <div class="hotel-overlay">
                        <h1 class="hotel-title"><?php echo htmlspecialchars($hotel['name']); ?></h1>
                        <p class="hotel-location"><?php echo htmlspecialchars($hotel['location'] . ', ' . $hotel['city'] . ', ' . $hotel['country']); ?></p>
                        <div class="hotel-rating">
                            <span class="stars">★★★★★</span>
                            <span><?php echo $hotel['rating']; ?>/5</span>
                        </div>
                    </div>
                </div>
                
                <div class="hotel-content">
                    <p class="hotel-description"><?php echo htmlspecialchars($hotel['description']); ?></p>
                    
                    <div class="amenities">
                        <h3>Hotel Amenities</h3>
                        <div class="amenities-list">
                            <?php 
                            $amenities = explode(', ', $hotel['amenities']);
                            foreach($amenities as $amenity): 
                            ?>
                                <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rooms-section">
                <h2 class="section-title">Available Rooms</h2>
                <div class="rooms-grid">
                    <?php foreach($roomTypes as $room): ?>
                    <div class="room-card">
                        <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" class="room-image">
                        <div class="room-info">
                            <h3 class="room-type"><?php echo htmlspecialchars($room['room_type']); ?></h3>
                            <p class="room-description"><?php echo htmlspecialchars($room['description']); ?></p>
                            <div class="room-details">
                                <span class="room-guests">Max <?php echo $room['max_guests']; ?> guests</span>
                                <span class="room-price">$<?php echo number_format($room['price_per_night'], 2); ?>/night</span>
                            </div>
                            <button class="book-btn" onclick="bookRoom(<?php echo $hotel['id']; ?>, <?php echo $room['id']; ?>, <?php echo $room['price_per_night']; ?>)">Book Now</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <h1 class="page-title">
                <?php echo $destination ? 'Hotels in ' . htmlspecialchars($destination) : 'All Hotels'; ?>
            </h1>
            
            <div class="hotels-grid">
                <?php 
                $hotelsToShow = isset($hotels) ? $hotels : getAllHotels($pdo);
                foreach($hotelsToShow as $hotel): 
                ?>
                <div class="hotel-card">
                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                    <div class="hotel-info">
                        <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <p class="hotel-location"><?php echo htmlspecialchars($hotel['location'] . ', ' . $hotel['city']); ?></p>
                        <div class="hotel-rating">
                            <span class="stars">★★★★★</span>
                            <span><?php echo $hotel['rating']; ?>/5</span>
                        </div>
                        <p class="hotel-description"><?php echo htmlspecialchars(substr($hotel['description'], 0, 100) . '...'); ?></p>
                        <button class="view-btn" onclick="viewHotel(<?php echo $hotel['id']; ?>)">View Details</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Store PHP variables in JavaScript
        const searchCheckin = "<?php echo addslashes($checkin); ?>";
        const searchCheckout = "<?php echo addslashes($checkout); ?>";

        function goHome() {
            window.location.href = 'index.php';
        }

        function goBack() {
            window.history.back();
        }

        function viewHotel(hotelId) {
            window.location.href = 'hotels.php?hotel_id=' + hotelId;
        }

        function bookRoom(hotelId, roomId, price) {
            console.log('Book Room clicked:', hotelId, roomId, price);
            
            // If we have dates from search, use them directly
            if (searchCheckin && searchCheckout && searchCheckin !== '' && searchCheckout !== '') {
                console.log('Using search dates:', searchCheckin, searchCheckout);
                window.location.href = 'booking.php?hotel_id=' + hotelId + '&room_id=' + roomId + '&checkin=' + searchCheckin + '&checkout=' + searchCheckout + '&price=' + price;
                return;
            }
            
            // Otherwise show date picker modal
            console.log('Showing date picker modal');
            showDateModal(hotelId, roomId, price);
        }

        function showDateModal(hotelId, roomId, price) {
            // Create modal overlay
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            
            // Get today's date for minimum date
            const today = new Date().toISOString().split('T')[0];
            
            // Create modal content
            overlay.innerHTML = `
                <div class="modal-content">
                    <h3 class="modal-title">Select Your Dates</h3>
                    <div class="modal-form-group">
                        <label for="modalCheckin">Check-in Date:</label>
                        <input type="date" id="modalCheckin" min="${today}">
                    </div>
                    <div class="modal-form-group">
                        <label for="modalCheckout">Check-out Date:</label>
                        <input type="date" id="modalCheckout" min="${today}">
                    </div>
                    <div class="modal-buttons">
                        <button class="modal-btn modal-btn-confirm" onclick="confirmBooking(${hotelId}, ${roomId}, ${price})">Confirm Booking</button>
                        <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Handle checkin date change
            document.getElementById('modalCheckin').addEventListener('change', function() {
                document.getElementById('modalCheckout').min = this.value;
            });
            
            // Close modal when clicking outside
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeModal();
                }
            });
        }

        function confirmBooking(hotelId, roomId, price) {
            const checkin = document.getElementById('modalCheckin').value;
            const checkout = document.getElementById('modalCheckout').value;
            
            console.log('Confirming booking with dates:', checkin, checkout);
            
            if (!checkin || !checkout) {
                alert('Please select both check-in and check-out dates.');
                return;
            }
            
            if (new Date(checkout) <= new Date(checkin)) {
                alert('Check-out date must be after check-in date.');
                return;
            }
            
            closeModal();
            window.location.href = 'booking.php?hotel_id=' + hotelId + '&room_id=' + roomId + '&checkin=' + checkin + '&checkout=' + checkout + '&price=' + price;
        }

        function closeModal() {
            const overlay = document.querySelector('.modal-overlay');
            if (overlay) {
                document.body.removeChild(overlay);
            }
        }

        // Handle search form
        document.getElementById('searchForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const destination = document.getElementById('destination').value;
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            
            let url = 'hotels.php';
            const params = [];
            
            if (destination) params.push('destination=' + encodeURIComponent(destination));
            if (checkin) params.push('checkin=' + checkin);
            if (checkout) params.push('checkout=' + checkout);
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            window.location.href = url;
        });

        // Set minimum dates on page load
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');
            
            if (checkinInput) {
                checkinInput.min = today;
                checkinInput.addEventListener('change', function() {
                    if (checkoutInput) {
                        checkoutInput.min = this.value;
                    }
                });
            }
            
            if (checkoutInput) {
                checkoutInput.min = today;
            }
        });
    </script>
</body>
</html>
