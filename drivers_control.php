<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db_connection.php";

$successMessage = "";
$nameError = "";
$phoneError = "";
$idError = "";

// add driver
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_driver"])) {

    $fullName = trim($_POST["fullName"]);
    $phoneNumber = trim($_POST["phoneNumber"]);
    $nationalId = trim($_POST["nationalId"]);

    if ($fullName === "") {
        $nameError = "Full name is required.";
    }

    if ($phoneNumber === "") {
        $phoneError = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phoneNumber)) {
        $phoneError = "Phone number must be exactly 10 digits.";
    }

    if ($nationalId === "") {
        $idError = "National ID is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $nationalId)) {
        $idError = "National ID must be exactly 10 digits.";
    }

    // check if phone number already exists
    if ($phoneError === "") {
        $stmt = $conn->prepare("SELECT DriverID FROM driver WHERE PhoneNumber = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $phoneError = "Phone number already exists.";
        }

        $stmt->close();
    }

    // check if national id already exists
    if ($idError === "") {
        $stmt = $conn->prepare("SELECT DriverID FROM driver WHERE NationalID = ?");
        $stmt->bind_param("s", $nationalId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $idError = "National ID already exists.";
        }

        $stmt->close();
    }

    if ($nameError === "" && $phoneError === "" && $idError === "") {
        $stmt = $conn->prepare("INSERT INTO driver (FullName, PhoneNumber, NationalID, Status, AdminID) VALUES (?, ?, ?, 'Available', 1)");
        $stmt->bind_param("sss", $fullName, $phoneNumber, $nationalId);
        $stmt->execute();
        $stmt->close();

        $successMessage = "Driver added successfully.";
    }
}

// delete driver
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_driver"])) {

    $driverID = (int) $_POST["driver_id"];

    // check if driver has active requests
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM request WHERE DriverID = ? AND Status != 'Delivered'");
    $stmt->bind_param("i", $driverID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row["total"] > 0) {
        $successMessage = "Cannot delete this driver because he has an active request.";
    } else {
        $stmt = $conn->prepare("DELETE FROM driver WHERE DriverID = ?");
        $stmt->bind_param("i", $driverID);
        $stmt->execute();
        $stmt->close();

        $successMessage = "Driver deleted successfully.";
    }
}

// assign request to driver
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["assign_request"])) {

    $driverID = (int) $_POST["driver_id"];
    $requestID = (int) $_POST["request_id"];

    if ($driverID > 0 && $requestID > 0) {

        // update request
        $stmt = $conn->prepare("UPDATE request SET DriverID = ?, Status = 'In Transit' WHERE RequestID = ? AND Status = 'Pending'");
        $stmt->bind_param("ii", $driverID, $requestID);
        $stmt->execute();
        $stmt->close();

        // update driver status
        $stmt = $conn->prepare("UPDATE driver SET Status = 'Busy' WHERE DriverID = ?");
        $stmt->bind_param("i", $driverID);
        $stmt->execute();
        $stmt->close();

        $successMessage = "Request assigned successfully.";
    }
}

// get drivers
$driversResult = $conn->query("SELECT DriverID, FullName, PhoneNumber, NationalID, Status FROM driver ORDER BY DriverID DESC");

// get pending requests
$pendingRequests = [];
$requestsResult = $conn->query("SELECT RequestID FROM request WHERE Status = 'Pending' ORDER BY RequestID DESC");

while ($row = $requestsResult->fetch_assoc()) {
    $pendingRequests[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drivers Control</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="drivers-page">
  <div class="container">

    <div class="top-bar">
      <a href="login.html">
        <img src="images/logout.png" class="logout-icon">
      </a>
    </div>

    <div class="logo-container">
      <img src="images/thamen.bmp" class="logo">
    </div>

    <h1 class="page-title">Drivers Control</h1>
    <div class="divider"></div>

    <?php if ($successMessage !== ""): ?>
      <div class="success-box" style="display:block;">
        <?php echo $successMessage; ?>
      </div>
    <?php endif; ?>

    <div class="layout">

      <!-- add driver -->
      <div class="box">
        <h2>Add Driver</h2>

        <form method="post">

          <div class="input-group">
            <input type="text" name="fullName" class="input-field" placeholder="Full Name" value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>">
            <span class="error-text"><?php echo $nameError; ?></span>
          </div>

          <div class="input-group">
            <input type="text" name="phoneNumber" class="input-field" placeholder="Phone Number" value="<?php echo isset($_POST['phoneNumber']) ? htmlspecialchars($_POST['phoneNumber']) : ''; ?>">
            <span class="error-text"><?php echo $phoneError; ?></span>
          </div>

          <div class="input-group">
            <input type="text" name="nationalId" class="input-field" placeholder="National ID" value="<?php echo isset($_POST['nationalId']) ? htmlspecialchars($_POST['nationalId']) : ''; ?>">
            <span class="error-text"><?php echo $idError; ?></span>
          </div>

          <button type="submit" name="add_driver" class="btn">Add Driver</button>

        </form>
      </div>

      <!-- drivers list -->
      <div class="box">
        <h2>Drivers List</h2>

        <div class="driver-list">

          <?php while ($driver = $driversResult->fetch_assoc()): ?>

            <div class="driver-card">

              <h4><?php echo $driver["FullName"]; ?></h4>
              <p>Phone: <?php echo $driver["PhoneNumber"]; ?></p>
              <p>National ID: <?php echo $driver["NationalID"]; ?></p>
              <p>Status: <?php echo $driver["Status"]; ?></p>

              <?php if ($driver["Status"] === "Available"): ?>

                <?php if (count($pendingRequests) > 0): ?>

                  <form method="post">

                    <input type="hidden" name="driver_id" value="<?php echo $driver["DriverID"]; ?>">

                    <select name="request_id" class="input-field" required>
                      <option value="">Select Request</option>

                      <?php foreach ($pendingRequests as $request): ?>
                        <option value="<?php echo $request["RequestID"]; ?>">
                          Request #<?php echo $request["RequestID"]; ?>
                        </option>
                      <?php endforeach; ?>

                    </select>

                    <button type="submit" name="assign_request" class="btn">Assign</button>

                  </form>

                <?php else: ?>
                  <p>No pending requests</p>
                <?php endif; ?>

              <?php endif; ?>

              <!-- delete driver -->
              <form method="post">
                <input type="hidden" name="driver_id" value="<?php echo $driver["DriverID"]; ?>">
                <button type="submit" name="delete_driver" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
              </form>

            </div>

          <?php endwhile; ?>

        </div>
      </div>

    </div>
  </div>
</body>
</html>