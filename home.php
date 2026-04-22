<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Home Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

	<div class="home-container">
        <!-- في أعلى اليمين -->
        <a href="login.html" class="logout-link">
            <img src="images/logout.png" alt="Logout" class="logout-icon">
        </a>

		<div class="container">
			<div class="logo-container">
				<img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
			</div>
			
			<h2 class="Home-title">Home Page</h2>
			<div class="divider"></div>
			
			<div class="search-section">
				<form id="searchForm" class="search-container" action="RequestDetails.html" method="get">
                    <input type="text" id="requestId" placeholder="📦 Enter RequestID" class="search-input" required>
                    <button type="submit" class="search-btn">Search</button>
                </form>
			</div>
			
			<div class="home-cards-vertical">
				<a href="MyRequests.php" class="home-card-btn">
					<div class="card-content">
                        <img src="images/MyRequest.png" alt="My Request" class="card-icon">
							<div class="card-title">MY REQUEST</div>
					</div>
				</a>
				
				<a href="CreateRequest.php" class="home-card-btn">
					<div class="card-content">
                        <img src="images/NewRequest.png" alt="New Request" class="card-icon">
							<div class="card-title">CREATE NEW REQUEST</div>
					</div>
				</a>
			</div>
		</div>
	</div>

</body>
</html>