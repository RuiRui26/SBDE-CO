<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="icon" type="image/png" href="img4/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/messages1.css">
</head>

<body>
    <!-- Navigation -->
    <nav>
        <div class="menu-container nav-wrapper">
            <div class="brand">
                <a href="index.php">
                    <img src="img/NMG22.png" alt="Insurancy Logo">
                </a>
            </div>
            <div class="hamberger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-list">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="benefits.php">Insurance</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h2>Conversations</h2>
            <input type="text" class="search-bar" placeholder="Search Conversations">
        </div>

        <div class="conversation-details">
            <h3>Solis Alekxiz</h3>
            <p><strong>Address:</strong> Not Available</p>

            <!-- Chat Window -->
            <div class="chat-window" id="chatWindow">
                <!-- User Message (Applicant's Inquiry) -->
                <div class="chat-message agent">
                    <p>Hi po ma'am, nung last pa po ako ma’am nag-apply till now po hindi pa gumagalaw yung status ko po</p>
                </div>

                <!-- Agent Response (Processing Message) -->
                <div class="chat-message user">
                    <p>Hello Solis! We are sad to hear that. Your document will be processed soon.</p>
                </div>

                <!-- Agent Follow-Up (Ready for Release) -->
                <div class="chat-message user">
                    <p>Solis! We are delighted to let you know that your document is ready for release.</p>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="chat-input-area">
                <input type="text" id="messageInput" placeholder="Type your message...">
                <button id="sendMessageBtn">Send</button>
            </div>
        </div>
    </div>

    <script>
        // Send Message Button
        document.getElementById("sendMessageBtn").addEventListener("click", function () {
            const messageInput = document.getElementById("messageInput");
            const chatWindow = document.getElementById("chatWindow");
            const message = messageInput.value.trim();

            if (message !== "") {
                const newMessage = document.createElement("div");
                newMessage.classList.add("chat-message", "agent");
                newMessage.innerphp = `<p>${message}</p>`;
                chatWindow.appendChild(newMessage);
                messageInput.value = "";
                chatWindow.scrollTop = chatWindow.scrollHeight; // Auto-scroll to the latest message
            }
        });

        // Open Profile Modal
        function openModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }

        // Close Profile Modal
        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        // Save Profile Changes
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
