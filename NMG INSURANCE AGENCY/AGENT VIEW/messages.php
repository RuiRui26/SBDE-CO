<?php
session_start();
//require('../../Logout_Login/Restricted.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="icon" type="image/png" href="img4/logo.png">
    <link rel="stylesheet" href="css/messages.css">
    <style>
        .content {
            margin-left: 250px;
            width: calc(100% - 250px);
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <img src="img4/logo.png" alt="Logo" class="logo">
        <ul class="menu">
            <li><a href="dashboard.php"><img src="img4/dashboard.png" alt="Dashboard Icon"> Dashboard</a></li>
            <li><a href="admin.php"><img src="img4/adminprofile.png" alt="Admin Icon"> Agent Profile</a></li>
            <li><a href="customer.php"><img src="img4/customers.png" alt="Customers Icon"> Customers</a></li>
            <li><a href="../../Logout_Login/Logout.php"><img src="img4/logout.png" alt="Logout Icon"> Logout</a></li>
        </ul>
    </div>

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
                <div class="chat-window" id="chatWindow">
                    <div class="chat-message user">
                        <p>Hi po ma'am, nung last pa po ako ma’am nag-apply till now po hindi pa gumagalaw yung status ko po</p>
                    </div>
                    <div class="chat-message agent">
                        <p>Hello Solis!</p>
                        <p>We are sad to hear that. Your document will be processed soon.</p>
                    </div>
                    <div class="chat-message agent">
                        <p>Solis! We are delighted to let you know that your document is ready for release.</p>
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="messageInput" placeholder="Type your message...">
                    <button id="sendMessageBtn">Send</button>
                </div>
                <button class="resolve-btn">Mark as Resolved</button>
            </div>
        </div>
    </div>

    <script>
        document.querySelector(".resolve-btn").addEventListener("click", function() {
            alert("Conversation marked as resolved.");
        });

        document.getElementById("sendMessageBtn").addEventListener("click", function() {
            const messageInput = document.getElementById("messageInput");
            const chatWindow = document.getElementById("chatWindow");
            const message = messageInput.value.trim();

            if (message !== "") {
                const newMessage = document.createElement("div");
                newMessage.classList.add("chat-message", "agent");
                newMessage.innerHTML = `<p>${message}</p>`;
                chatWindow.appendChild(newMessage);
                messageInput.value = "";
                chatWindow.scrollTop = chatWindow.scrollHeight; // Scroll to bottom
            }
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