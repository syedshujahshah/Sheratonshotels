<?php
require_once 'db.php';

// Get booking parameters
$hotel_id = $_GET['hotel_id'] ?? '';
$room_id = $_GET['room_id'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$price = $_GET['price'] ?? 0;

// Validate required parameters
if (!$hotel_id || !$room_id || !$checkin || !$checkout) {
    echo "<script>alert('Missing booking information. Redirecting to homepage.'); window.location.href = 'index.php';</script>";
    exit;
}

// Get hotel and room details
$hotel = getHotelById($pdo, $hotel_id);
$stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$hotel || !$room) {
    echo "<script>alert('Hotel or room not found. Redirecting to homepage.'); window.location.href = 'index.php';</script>";
    exit;
}

// Calculate nights and total amount
$checkin_date = new DateTime($checkin);
$checkout_date = new DateTime($checkout);
$nights = $checkin_date->diff($checkout_date)->days;
$total_amount = $nights * $price;

$error = '';
$success = false;

// Handle form submission
if ($_POST) {
    $guest_name = trim($_POST['guest_name'] ?? '');
    $guest_email = trim($_POST['guest_email'] ?? '');
    $guest_phone = trim($_POST['guest_phone'] ?? '');
    $guests = intval($_POST['guests'] ?? 1);
    
    // Validation
    if (empty($guest_name)) {
        $error = "Guest name is required.";
    } elseif (empty($guest_email)) {
        $error = "Email address is required.";
    } elseif (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($guests < 1 || $guests > $room['max_guests']) {
        $error = "Number of guests must be between 1 and " . $room['max_guests'] . ".";
    } else {
        // Create booking
        $booking_data = [
            'hotel_id' => $hotel_id,
            'room_type_id' => $room_id,
            'guest_name' => $guest_name,
            'guest_email' => $guest_email,
            'guest_phone' => $guest_phone,
            'check_in_date' => $checkin,
            'check_out_date' => $checkout,
            'guests' => $guests,
            'total_nights' => $nights,
            'total_amount' => $total_amount
        ];
        
        if (createBooking($pdo, $booking_data)) {
            $booking_id = $pdo->lastInsertId();
            echo "<script>
                alert('Booking confirmed successfully!');
                window.location.href = 'confirmation.php?booking_id=$booking_id';
            </script>";
            exit;
        } else {
            $error = "Booking failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay - Sheraton Hotels</title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #666;
        }

        .booking-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .booking-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .form-title {
            font-size: 1.5rem;
            color: #1a365d;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e1e5e9;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .required {
            color: #dc3545;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #2d5a87;
            box-shadow: 0 0 0 3px rgba(45, 90, 135, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .submit-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .booking-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-title {
            font-size: 1.5rem;
            color: #1a365d;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e1e5e9;
            padding-bottom: 0.5rem;
        }

        .hotel-info {
            margin-bottom: 2rem;
        }

        .hotel-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .hotel-location {
            color: #666;
            margin-bottom: 1rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffd700;
        }

        .room-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .room-type {
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5rem;
        }

        .room-description {
            color: #666;
            font-size: 0.9rem;
        }

        .booking-details {
            margin-bottom: 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
        }

        .detail-row.total {
            border-top: 2px solid #e1e5e9;
            font-weight: bold;
            font-size: 1.2rem;
            color: #1a365d;
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .detail-label {
            color: #333;
        }

        .detail-value {
            color: #666;
            font-weight: 500;
        }

        .total .detail-value {
            color: #1a365d;
            font-weight: bold;
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
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 1rem;
            color: #666;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #2d5a87;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
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
        <button class="back-btn" onclick="goBack()">← Back to Hotel</button>
        
        <div class="booking-header">
            <h1 class="page-title">Complete Your Booking</h1>
            <p class="page-subtitle">You're just one step away from your perfect stay</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="loading" id="loadingDiv">
            <div class="spinner"></div>
            <p>Processing your booking...</p>
        </div>

        <div class="booking-container" id="bookingContainer">
            <div class="booking-form">
                <h2 class="form-title">Guest Information</h2>
                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label for="guest_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="guest_name" name="guest_name" 
                               value="<?php echo htmlspecialchars($_POST['guest_name'] ?? ''); ?>" 
                               required placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="guest_email">Email Address <span class="required">*</span></label>
                        <input type="email" id="guest_email" name="guest_email" 
                               value="<?php echo htmlspecialchars($_POST['guest_email'] ?? ''); ?>" 
                               required placeholder="Enter your email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="guest_phone">Phone Number</label>
                        <input type="tel" id="guest_phone" name="guest_phone" 
                               value="<?php echo htmlspecialchars($_POST['guest_phone'] ?? ''); ?>" 
                               placeholder="Enter your phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="guests">Number of Guests <span class="required">*</span></label>
                        <select id="guests" name="guests" required>
                            <?php for($i = 1; $i <= $room['max_guests']; $i++): ?>
                                <option value="<?php echo $i; ?>" 
                                        <?php echo (isset($_POST['guests']) && $_POST['guests'] == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        Confirm Booking - $<?php echo number_format($total_amount, 2); ?>
                    </button>
                </form>
            </div>

            <div class="booking-summary">
                <h2 class="summary-title">Booking Summary</h2>
                
                <div class="hotel-info">
                    <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p class="hotel-location"><?php echo htmlspecialchars($hotel['location'] . ', ' . $hotel['city']); ?></p>
                    <div class="hotel-rating">
                        <span class="stars">★★★★★</span>
                        <span><?php echo $hotel['rating']; ?>/5</span>
                    </div>
                </div>
                
                <div class="room-info">
                    <div class="room-type"><?php echo htmlspecialchars($room['room_type']); ?></div>
                    <div class="room-description"><?php echo htmlspecialchars($room['description']); ?></div>
                </div>
                
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="detail-label">Check-in:</span>
                        <span class="detail-value"><?php echo date('M d, Y', strtotime($checkin)); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Check-out:</span>
                        <span class="detail-value"><?php echo date('M d, Y', strtotime($checkout)); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nights:</span>
                        <span class="detail-value"><?php echo $nights; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Rate per night:</span>
                        <span class="detail-value">$<?php echo number_format($price, 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Max guests:</span>
                        <span class="detail-value"><?php echo $room['max_guests']; ?></span>
                    </div>
                    <div class="detail-row total">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value">$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        function goBack() {
            window.history.back();
        }

        // Form validation and submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const name = document.getElementById('guest_name').value.trim();
            const email = document.getElementById('guest_email').value.trim();
            const guests = document.getElementById('guests').value;
            
            // Basic validation
            if (!name) {
                e.preventDefault();
                alert('Please enter your full name.');
                document.getElementById('guest_name').focus();
                return false;
            }
            
            if (name.length < 2) {
                e.preventDefault();
                alert('Please enter a valid full name.');
                document.getElementById('guest_name').focus();
                return false;
            }
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                document.getElementById('guest_email').focus();
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                document.getElementById('guest_email').focus();
                return false;
            }
            
            if (!guests || guests < 1) {
                e.preventDefault();
                alert('Please select number of guests.');
                document.getElementById('guests').focus();
                return false;
            }
            
            // Show loading
            showLoading();
            return true;
        });

        function showLoading() {
            document.getElementById('loadingDiv').style.display = 'block';
            document.getElementById('bookingContainer').style.opacity = '0.5';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = 'Processing...';
        }

        // Auto-focus on first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('guest_name').focus();
        });

        // Real-time email validation
        document.getElementById('guest_email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                this.style.borderColor = '#dc3545';
                this.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
            } else {
                this.style.borderColor = '#e1e5e9';
                this.style.boxShadow = 'none';
            }
        });

        // Phone number formatting (optional)
        document.getElementById('guest_phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{3})/, '($1) $2');
            }
            this.value = value;
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
