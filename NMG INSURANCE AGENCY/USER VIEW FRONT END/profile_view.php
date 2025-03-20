<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile_view.css">
</head>
<body>

<?php include 'profile.php'; ?>

<div class="profile-container">
    <!-- Cover Photo -->
    <div class="cover-photo">
        <img src="img/cover.jpg" alt="Cover Photo">
    </div>

    <!-- Profile Picture & User Info -->
    <div class="profile-section">
        <div class="profile-picture">
            <img src="img/userprofile.png" alt="User Picture">
        </div>
        <div class="user-info">
            <h2>John Cena</h2>
            <p><strong>Email:</strong> johndoe@example.com</p>
            <p><strong>Phone:</strong> +1 234 567 890</p>
            <p><strong>Address:</strong> 123 Main Street, City, Country</p>
        </div>
    </div>

    <!-- Car Insurance Table -->
    <div class="insurance-section">
        <h3>Car Insurance Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Policy Number</th>
                    <th>Car Model</th>
                    <th>Insurance Type</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ABC12345</td>
                    <td>Toyota Corolla</td>
                    <td>Comprehensive</td>
                    <td>2025-06-30</td>
                </tr>
                <tr>
                    <td>XYZ67890</td>
                    <td>Honda Civic</td>
                    <td>Third-Party</td>
                    <td>2025-12-15</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
