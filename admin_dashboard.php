<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db_connection.php";

// current week starts from Monday
$weekStart = "DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";

// total requests this week
$stmt = $conn->query("SELECT COUNT(*) AS total FROM request WHERE CreationDate >= $weekStart");
$row = $stmt->fetch_assoc();
$totalRequests = $row['total'];

// pending requests this week
$stmt = $conn->query("SELECT COUNT(*) AS total FROM request WHERE Status = 'Pending' AND CreationDate >= $weekStart");
$row = $stmt->fetch_assoc();
$pendingRequests = $row['total'];

// in transit requests this week
$stmt = $conn->query("SELECT COUNT(*) AS total FROM request WHERE Status = 'In Transit' AND CreationDate >= $weekStart");
$row = $stmt->fetch_assoc();
$inTransitRequests = $row['total'];

// delivered requests this week
$stmt = $conn->query("SELECT COUNT(*) AS total FROM request WHERE Status = 'Delivered' AND CreationDate >= $weekStart");
$row = $stmt->fetch_assoc();
$deliveredRequests = $row['total'];

// total reviews this week
$stmt = $conn->query("SELECT COUNT(*) AS total FROM review WHERE DateSubmitted >= $weekStart");
$row = $stmt->fetch_assoc();
$totalReviews = $row['total'];

// average rating this week
$stmt = $conn->query("
    SELECT AVG(rating.Stars) AS avg_rating
    FROM rating
    JOIN review ON rating.RequestID = review.RequestID
    WHERE review.DateSubmitted >= $weekStart
");
$row = $stmt->fetch_assoc();
$averageRating = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;

// reviews and ratings this week
$stmt = $conn->query("
    SELECT 
        review.ReviewText,
        review.DateSubmitted,
        review.RequestID,
        rating.Stars
    FROM review
    LEFT JOIN rating ON review.RequestID = rating.RequestID
    WHERE review.DateSubmitted >= $weekStart
    ORDER BY review.DateSubmitted DESC
");

$reviews = [];
while ($row = $stmt->fetch_assoc()) {
    $reviews[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">
  <div class="container">

    <div class="top-bar">
      <a href="login.html">
        <img src="images/logout.png" alt="Logout" class="logout-icon">
      </a>
    </div>

    <div class="logo-container">
      <img src="images/thamen.bmp" alt="Thameen Logo" class="logo">
    </div>

    <h1 class="page-title">Admin Dashboard</h1>
    <div class="divider"></div>

    <div class="summary-grid">

      <div class="summary-card">
        <h3>Total Requests This Week</h3>
        <p><?php echo $totalRequests; ?></p>
      </div>

      <div class="summary-card">
        <h3>Pending</h3>
        <p><?php echo $pendingRequests; ?></p>
      </div>

      <div class="summary-card">
        <h3>In Transit</h3>
        <p><?php echo $inTransitRequests; ?></p>
      </div>

      <div class="summary-card">
        <h3>Delivered</h3>
        <p><?php echo $deliveredRequests; ?></p>
      </div>

      <div class="summary-card">
        <h3>Average Rating This Week</h3>
        <p><?php echo $averageRating; ?> ★</p>
      </div>

      <div class="summary-card">
        <h3>Reviews This Week</h3>
        <p><?php echo $totalReviews; ?></p>
      </div>

    </div>

    <h2 class="section-title">Customer Ratings & Reviews This Week</h2>

    <div class="review-list">

      <?php if (count($reviews) > 0): ?>

        <?php foreach ($reviews as $review): ?>
          <div class="review-card">

            <div class="stars">
              <?php
              $stars = isset($review['Stars']) ? (int)$review['Stars'] : 0;
              echo str_repeat("★", $stars) . str_repeat("☆", 5 - $stars);
              ?>
            </div>

            <div class="review-text">
              <?php echo htmlspecialchars($review['ReviewText']); ?>
            </div>

            <div class="review-meta">
              Request ID: <?php echo htmlspecialchars($review['RequestID']); ?>
            </div>

            <div class="review-meta">
              Date: <?php echo htmlspecialchars($review['DateSubmitted']); ?>
            </div>

          </div>
        <?php endforeach; ?>

      <?php else: ?>

        <div class="review-card">
          <div class="review-text">No reviews available this week.</div>
        </div>

      <?php endif; ?>

    </div>

    <a href="drivers_control.php" class="btn">Manage Drivers</a>

  </div>
</body>
</html>