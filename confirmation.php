<?php
require_once 'db.php';

$booking_id = $_GET['booking_id'] ?? '';

if (!$booking_id) {
    header('Location: index.php');
    exit;
}

$booking = getBookingById($pdo, $booking_id);

if (!$booking) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Sheraton Hotels</title>
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

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .confirmation-card {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2rem;
        }

        .confirmation-title {
            font-size: 2.5rem;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .confirmation-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .booking-id {
            background: #e3f2fd;
            color: #1976d2;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .booking-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .details-title {
            font-size: 1.5rem;
            color: #1a365d;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e1e5e9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #333;
        }

        .detail-value {
            color: #666;
        }

        .total-row {
            background: #1a365d;
            color: white;
            margin: 1rem -2rem -2rem;
            padding: 1rem 2rem;
            border-radius: 0 0 10px 10px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5a87 0%, #1a365d 100%);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #1a365d;
        }

        .email-notice {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            .confirmation-card {
                padding: 2rem 1rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo" onclick="goHome()">SHERATON</div>
        </nav>
    </header>

    <div class="container">
        <div class="confirmation-card">
            <div class="success-icon">✓</div>
            <h1 class="confirmation-title">Booking Confirmed!</h1>
            <p class="confirmation-subtitle">Thank you for choosing Sheraton Hotels. Your reservation has been successfully confirmed.</p>
            
            <div class="booking-id">
                Booking ID: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>

            <div class="email-notice">
                📧 A confirmation email has been sent to <?php echo htmlspecialchars($booking['guest_email']); ?>
            </div>

            <div class="booking-details">
                <h2 class="details-title">Booking Details</h2>
                
                <div class="detail-row">
                    <span class="detail-label">Hotel:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['hotel_name']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Room Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['room_type']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Guest Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['guest_name']); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['guest_email']); ?></span>
                </div>
                
                <?php if ($booking['guest_phone']): ?>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['guest_phone']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <span class="detail-label">Check-in:</span>
                    <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Check-out:</span>
                    <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Nights:</span>
                    <span class="detail-value"><?php echo $booking['total_nights']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Guests:</span>
                    <span class="detail-value"><?php echo $booking['guests']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Rate per night:</span>
                    <span class="detail-value">$<?php echo number_format($booking['price_per_night'], 2); ?></span>
                </div>
                
                <div class="total-row">
                    <div class="detail-row" style="border: none; color: white;">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value" style="color: white;">$<?php echo number_format($booking['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-primary" onclick="goHome()">Back to Home</button>
                <button class="btn btn-secondary" onclick="window.print()">Print Confirmation</button>
            </div>
        </div>
    </div>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        // Auto-print option (uncomment if needed)
        // window.onload = function() {
        //     setTimeout(function() {
        //         if (confirm('Would you like to print this confirmation?')) {
        //             window.print();
        //         }
        //     }, 1000);
        // };
    </script>
</body>
</html>
