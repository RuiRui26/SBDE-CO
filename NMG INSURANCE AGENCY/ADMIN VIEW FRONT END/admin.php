<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <!-- Favicon -->
    <link rel="icon" type="imag2/png" href="img2/logo.png">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="sidebar">
        <img src="img2/logo.png" alt="Logo" class="logo"> 
        <ul class="menu">
            <li><a href="dashboard.html"><img src="img2/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.html"><img src="img2/adminprofile.png" alt="Admin Icon"> Admin Profile</a></li>
            <li><a href="customer.html"><img src="img2/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="search.html"><img src="img2/search.png" alt="Search Icon"> Search Policy</a></li>
            <li><a href="activitylog.html"><img src="img2/activitylog.png" alt="Activity Icon"> Activity Log</a></li>
            <li><a href="#"><img src="img2/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

    <div class="admin-container">
        <div class="cover-photo"></div>

        <div class="admin-profile">
            <img src="img2/samplepic.png" alt="Admin Picture" class="admin-picture">
            <div class="admin-info">
                <h1 id="adminName">John Doe</h1>
                <p id="adminPosition">System Administrator</p>
                <p id="adminEmail">admin@example.com</p>
                <p id="adminPhone">+123 456 7890</p>
                <div class="admin-actions">
                    <button onclick="openModal()">Edit Profile</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <h2>Edit Profile</h2>
            <label for="name">Name:</label>
            <input type="text" id="name" value="John Doe">

            <label for="position">Position:</label>
            <input type="text" id="position" value="System Administrator">

            <label for="email">Email:</label>
            <input type="email" id="email" value="admin@example.com">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" value="+123 456 7890">

            <div class="modal-buttons">
                <button onclick="saveProfile()">Save</button>
                <button onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        function saveProfile() {
            document.getElementById('adminName').textContent = document.getElementById('name').value;
            document.getElementById('adminPosition').textContent = document.getElementById('position').value;
            document.getElementById('adminEmail').textContent = document.getElementById('email').value;
            document.getElementById('adminPhone').textContent = document.getElementById('phone').value;
            closeModal();
        }
    </script>


</body>
</html>