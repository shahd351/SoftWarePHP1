
<?php
// rate_review.php - Allows user to rate and review a delivered request (once only)
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must login first.'); window.location.href='login.html';</script>";
    exit;
}

$userID = $_SESSION['user_id'];
$requestID = isset($_GET['requestID']) ? (int)$_GET['requestID'] : 0;

if ($requestID == 0) {
    echo "<script>alert('Invalid request ID.'); window.location.href='MyRequests.php';</script>";
    exit;
}

require_once 'db_connection.php';

// 1. Fetch request and verify ownership + status 'Delivered'
$stmt = $conn->prepare("SELECT * FROM request WHERE RequestID = ? AND UserID = ? AND Status = 'Delivered'");
$stmt->bind_param("ii", $requestID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Request not found, not delivered, or does not belong to you.'); window.location.href='MyRequests.php';</script>";
    exit;
}
$request = $result->fetch_assoc();

// 2. Check if already rated for this request
$check = $conn->prepare("SELECT RatingID FROM rating WHERE RequestID = ? AND UserID = ?");
$check->bind_param("ii", $requestID, $userID);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo "<script>alert('You have already rated this request.'); window.location.href='RequestDetails.php?requestID=$requestID';</script>";
    exit;
}

// 3. Handle form submission
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stars = (int)$_POST['stars'];
    $reviewText = trim($_POST['reviewText']);

    if ($stars < 1 || $stars > 5) {
        $errorMsg = "Please select a rating from 1 to 5 stars.";
    } elseif ($reviewText === '') {
        $errorMsg = "Review field cannot be empty.";
    } else {
        // Insert into rating
        $insRat = $conn->prepare("INSERT INTO rating (Stars, DateSubmitted, UserID, RequestID) VALUES (?, CURDATE(), ?, ?)");
        $insRat->bind_param("iii", $stars, $userID, $requestID);
        $ratOk = $insRat->execute();

        // Insert into review
        $insRev = $conn->prepare("INSERT INTO review (ReviewText, DateSubmitted, UserID, RequestID) VALUES (?, CURDATE(), ?, ?)");
        $insRev->bind_param("sii", $reviewText, $userID, $requestID);
        $revOk = $insRev->execute();

        if ($ratOk && $revOk) {
            echo "<script>alert('Thank you! Your rating and review have been submitted.'); window.location.href='MyRequests.php';</script>";
            exit;
        } else {
            $errorMsg = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate & Review - Thamean</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stars-container .star {
            font-size: 36px;
            cursor: pointer;
            color: #ccc;
            transition: 0.2s;
            display: inline-block;
        }
        .stars-container .star.active {
            color: gold;
            text-shadow: 0 0 2px orange;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="review-page">
<div class="container">
    <div class="top-bar">
        <a href="login.html">
            <img src="images/logout.png" alt="Logout" style="width:40px; height:40px; object-fit:contain;">
        </a>
    </div>

    <div class="logo-container">
        <img src="images/thamen.bmp" alt="Thameen Logo" class="logo">
    </div>

    <h1 class="page-title">Rate & Review</h1>
    <div class="divider"></div>

    

        <?php if ($errorMsg): ?>
            <div class="error-msg"><?= htmlspecialchars($errorMsg) ?></div>


<?php endif; ?>

        <form method="POST" id="ratingForm">
            <label class="label">Select Rating</label>
            <div class="stars-container">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <input type="hidden" name="stars" id="starsValue" value="0">
            <span class="error-text" id="ratingError"></span>

            <label class="label" for="reviewText">Write Your Review</label>
            <textarea id="reviewText" name="reviewText" class="textarea-field" placeholder="Write your review here..."></textarea>
            <span class="error-text" id="reviewError"></span>

            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</div>

<script>
    const stars = document.querySelectorAll('.star');
    const starsInput = document.getElementById('starsValue');
    let selectedRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-value'));
            starsInput.value = selectedRating;
            stars.forEach((s, idx) => {
                if (idx < selectedRating) s.classList.add('active');
                else s.classList.remove('active');
            });
            document.getElementById('ratingError').innerText = '';
        });
    });

    document.getElementById('ratingForm').addEventListener('submit', function(e) {
        const reviewText = document.getElementById('reviewText').value.trim();
        const ratingErrorSpan = document.getElementById('ratingError');
        const reviewErrorSpan = document.getElementById('reviewError');
        let valid = true;

        ratingErrorSpan.innerText = '';
        reviewErrorSpan.innerText = '';

        if (selectedRating === 0) {
            ratingErrorSpan.innerText = 'Please select a rating from 1 to 5 stars.';
            valid = false;
        }
        if (reviewText === '') {
            reviewErrorSpan.innerText = 'Review field cannot be empty.';
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
