<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Edit Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="create-request-container">>
        <a href="login.html" class="logout-link">
            <img src="images/logout.png" alt="Logout" class="logout-icon">
        </a>

        <div class="container">
            <div class="logo-container">
                <img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
            </div>
            
            <h2 class="Home-title">Edit Request Page</h2>
            <div class="divider"></div>

            <form class="create-form" id="editRequestForm">
                <div class="input-group">
                    <label class="price-label" style="text-align: left; display: block;">Item Type :</label>
                    <select id="itemType" class="input-field" onchange="updatePrice()" required>
                        <option value="jewelry" selected>Jewelry</option>
                        <option value="cash">Cash</option>
                        <option value="electronics">Electronics</option>
                    </select>
                </div>

                <<div class="input-group">
                    <label class="price-label" style="text-align: left; display: block;">Item Value Range :</label>
                    <select id="itemValue" class="input-field" onchange="checkValue()">
                        <option value="less5000">Less than 5,000 SAR</option>
                        <option value="5000-10000" selected>5,000 - 10,000 SAR</option>
                        <option value="more10000">More than 10,000 SAR</option>
                    </select>
                </div>

                 <div class="input-group">
                    <label class="price-label" style="text-align: left; display: block;">Pickup Location :</label>
                    <input type="text" id="pickupLocation" class="input-field" value="Riyadh, Al Rawabi" required>
                </div>

                <div class="input-group">
                    <label class="price-label" style="text-align: left; display: block;">Dropoff Location :</label>
                    <input type="text" id="dropoffLocation" class="input-field" value="Riyadh, Al Narjis" required>
                </div>

                <div class="input-group">
                    <label class="price-label" style="text-align: left; display: block;">Security Code :</label>
                    <input type="password" id="securityCode" class="input-field" value="4567" maxlength="4" required>
                    <div class="security-note">4-digit code (shared with receiver)</div>
                </div>

                <div class="price-box">
                    <span class="price-label">Service Price:</span>
                    <span class="price-amount" id="priceDisplay">200 SAR</span>
                </div>

                <div class="edit-buttons-container" style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="button" onclick="validateAndSave()" class="role-btn" style="background-color: white; color: #5f1428; flex: 1;">Save Changes</button>
                    <a href="RequestDetails.html" class="role-btn" style="background-color: #fefefe; color: #5f1428; flex: 1; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updatePrice() {
            const type = document.getElementById('itemType').value;
            const priceDisplay = document.getElementById('priceDisplay');
            const prices = {
                'jewelry': '200 SAR',
                'cash': '150 SAR',
                'electronics': '120 SAR'
            };
            priceDisplay.innerText = prices[type];
        }

        function checkValue() {
            const value = document.getElementById('itemValue').value;
            if (value === 'more10000') {
                alert("Note: Insurance coverage is limited to 10,000 SAR.");
            }
        }

        function validateAndSave() {
            const pickup = document.getElementById('pickupLocation').value.trim();
            const dropoff = document.getElementById('dropoffLocation').value.trim();
            const security = document.getElementById('securityCode').value.trim();

            if (pickup === "" || dropoff === "" || security === "") {
                alert("Error: All fields must be filled!");
                return; 
            }

            if (confirm("Are you sure you want to save changes?")) {
                alert("✅ Changes saved successfully!");
                window.location.href = 'RequestDetails.php';
            }
        }

        window.onload = updatePrice;
    </script>
</body>
</html>