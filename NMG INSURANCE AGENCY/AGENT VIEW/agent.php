<?php
session_start(); 
//require('../../Logout_Login/Restricted.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/agent.css">
</head>
<body>

     <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header">
            <h2>Conversations</h2>
            <input type="text" class="search-bar" placeholder="Search Conversations">
        </div>

        <div class="main-container">
            <div class="conversations-list">
                <h3>All (167)</h3>
                <div class="conversation-item unresolved">
                    <span class="status">Unanswered</span>
                    <p>Dinampo Andy Rlig - Reschedule</p>
                </div>
                <div class="conversation-item resolved">
                    <span class="status">Resolved</span>
                    <p>Solis Alekxiz - Technical Issue</p>
                </div>
                <div class="conversation-item resolved">
                    <span class="status">Resolved</span>
                    <p>Enriquez Robby Patrick - Payment</p>
                </div>
                <div class="conversation-item cancelled">
                    <span class="status">Cancelled</span>
                    <p>Buenaventura John Mchales - Reschedule</p>
                </div>
            </div>

            <div class="conversation-details">
                <h3>Solis Alekxiz</h3>
                <p><strong>Address:</strong> Not Available</p>
                <div class="chat-window">
                    <div class="chat-message">
                        <p>Hi po ma'am, nung last pa po ako ma’am nag-apply till now po hindi pa gumagalaw yung status ko po</p>
                    </div>
                    <div class="chat-response">
                        <p>Hello Solis!</p>
                        <p>We are sad to hear that. Your document will be processed soon.</p>
                    </div>
                    <div class="chat-response">
                        <p>Solis! We are delighted to let you know that your document is ready for release.</p>
                    </div>
                </div>
                <button class="resolve-btn">Mark as Resolved</button>
            </div>

            <div class="user-details">
                <h3>Details:</h3>
                <p><strong>Name:</strong> Solis Alekxiz</p>
                <p><strong>Phone:</strong> 09126142634</p>
            </div>
        </div>
    </div>

    <script>
        document.querySelector(".resolve-btn").addEventListener("click", function() {
            alert("Conversation marked as resolved.");
        });
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
