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
        <img src="images/logout.png" alt="Logout" class="logout-icon">
      </a>
    </div>

    <div class="logo-container">
      <img src="images/thamen.bmp" alt="Thameen Logo" class="logo">
    </div>

    <h1 class="page-title">Drivers Control</h1>
    <div class="divider"></div>

    <div id="successBox" class="success-box">Driver added successfully.</div>

    <div class="layout">
      <div class="box">
        <h2>Add Driver</h2>
        <div class="input-group">
          <input type="text" id="fullName" class="input-field" placeholder="Full Name">
          <span class="error-text" id="nameError"></span>
        </div>
        <div class="input-group">
          <input type="text" id="phoneNumber" class="input-field" placeholder="Phone Number">
          <span class="error-text" id="phoneError"></span>
        </div>
        <div class="input-group">
          <input type="text" id="nationalId" class="input-field" placeholder="National ID">
          <span class="error-text" id="idError"></span>
        </div>
        <button class="btn" onclick="addDriver()">Add Driver</button>
      </div>

      <div class="box">
        <h2>Drivers List</h2>
        <div class="driver-list" id="driverList">
          <div class="driver-card">
            <h4>Ahmed Alqahtani</h4>
            <p>Phone: 0551234567</p>
            <p>National ID: 1234567890</p>
			<p>Statatus: Available</p>
            <button class="delete-btn" onclick="deleteDriver(this)">Delete</button>
          </div>
          <div class="driver-card">
            <h4>Faisal Alharbi</h4>
            <p>Phone: 0509876543</p>
            <p>National ID: 9876543210</p>
			<p>Statatus: Busy</p>
            <button class="delete-btn" onclick="deleteDriver(this)">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function clearErrors() {
      document.getElementById("nameError").textContent = "";
      document.getElementById("phoneError").textContent = "";
      document.getElementById("idError").textContent = "";
    }

    function addDriver() {
      clearErrors();
      const successBox = document.getElementById("successBox");
      successBox.style.display = "none";
      const fullName = document.getElementById("fullName").value.trim();
      const phoneNumber = document.getElementById("phoneNumber").value.trim();
      const nationalId = document.getElementById("nationalId").value.trim();

      let valid = true;
      if (fullName === "") { document.getElementById("nameError").textContent = "Required"; valid = false; }
      if (phoneNumber === "") { document.getElementById("phoneError").textContent = "Required"; valid = false; }
      if (nationalId === "") { document.getElementById("idError").textContent = "Required"; valid = false; }

      if (!valid) return;

      const newDriver = document.createElement("div");
      newDriver.className = "driver-card";
      newDriver.innerHTML = `<h4>${fullName}</h4><p>Phone: ${phoneNumber}</p><p>National ID: ${nationalId}</p><p>Statatus: Available</p><button class="delete-btn" onclick="deleteDriver(this)">Delete</button>`;
      document.getElementById("driverList").prepend(newDriver);
      successBox.style.display = "block";
      document.getElementById("fullName").value = "";
      document.getElementById("phoneNumber").value = "";
      document.getElementById("nationalId").value = "";
    }

    function deleteDriver(button) {
      if (confirm("Are you sure you want to delete the driver?")) {
        button.closest(".driver-card").remove();
      }
    }
  </script>
</body>
</html>
