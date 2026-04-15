<?php
include 'db_connection.php';

$sql = "SELECT * FROM User";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h3>Users List:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['FullName'] . " - " . $row['PhoneNumber'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "No users found.";
}

$conn->close();
?>