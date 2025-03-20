<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="img/NMG3.png" alt="Company Logo"> 
            </div>
            <nav class="menu">
                <button class="menu-item" data-tooltip="Dashboard">
                    <img src="img/dashboardd.png" alt="Dashboard">
                </button>
                <button class="menu-item" data-tooltip="Transactions">
                    <img src="img/transaction.png" alt="Transactions">
                </button>
                <button class="menu-item" data-tooltip="Apply Insurance">
                    <img src="img/apply.png" alt="Apply Insurance">
                </button>
                <button class="menu-item" data-tooltip="Lost Documents">
                    <img src="img/lost.png" alt="Lost Documents">
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-bar">
                <h2>Welcome, John Cena!</h2>
                
                <!-- Profile Section with Dropdown -->
                <div class="profile-dropdown">
                    <div class="profile" onclick="toggleDropdown()">
                        <img src="img/userprofile.png" alt="User">
                        <span>John Cena</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu" id="dropdownMenu">
                        <li><a href="profile_view.php">View Profile</a></li>
                        <li><a href="#">Settings</a></li>
                        <li><a href="#">Logout</a></li>
                    </ul>
                </div>
            </header>
        </main>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("dropdownMenu").classList.toggle("show");
        }
        window.onclick = function(event) {
            if (!event.target.matches('.profile, .profile *')) {
                let dropdown = document.getElementById("dropdownMenu");
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show'); 
                }
            }
        };
    </script>
</body>
</html>
