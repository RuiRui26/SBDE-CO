<?php
    include '../../DB_connection/db.php'; // include the PDO connection file

    $database = new Database();
    $conn = $database->getConnection();

    $query = "SELECT * FROM requirements";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply | NMG Insurance Agency</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">

    <!-- External CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/view_requirements.css">

    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <!-- Navigation -->
    <?php include 'nav.php'; ?>

    <!-- Insurance Requirements Section -->
    <section class="insurance-requirements">
        <div class="container">
            <h2 class="heading">Insurance Requirements</h2>

            <div class="filter-section">
                <input type="text" id="search" placeholder="Search requirements...">
                <select id="insurance-type">
                    <option value="all">Select an Option</option>
                    <option value="apply-insurance">Apply Insurance</option>
                    <option value="for-retrieval">Appoint For Lost Document</option>
                </select>
            </div>

            <div class="requirements-list">
                <?php foreach ($result as $row) : ?>
                    <div class="requirement-card" data-type="<?= htmlspecialchars($row['type']) ?>">
                        <h3>📌 <?= htmlspecialchars($row['title']) ?> <i class="fas fa-chevron-down toggle"></i></h3>
                        <p class="details"><?= htmlspecialchars($row['details']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <p class="para-line white">Copyright NMG Insurance Agency ©2025</p>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const requirementCards = document.querySelectorAll(".requirement-card");
            const searchInput = document.getElementById("search");
            const filterDropdown = document.getElementById("insurance-type");
            const heading = document.querySelector(".heading");

            const headingMap = {
                "all": "Insurance Requirements",
                "apply-insurance": "Apply Insurance Requirements",
                "lto-transaction": "LTO Transaction Requirements",
                "for-retrieval": "Lost Document Requirements"
            };

            function updateRequirements(filterType) {
                requirementCards.forEach(card => {
                    if (filterType === "all" || card.dataset.type === filterType) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });

                heading.textContent = headingMap[filterType] || "Insurance Requirements";
            }

            filterDropdown.addEventListener("change", function () {
                const selectedType = this.value;
                searchInput.value = "";
                updateRequirements(selectedType);
            });

            searchInput.addEventListener("input", function () {
                const searchValue = this.value.toLowerCase().trim();
                requirementCards.forEach(card => {
                    const cardText = card.textContent.toLowerCase();
                    card.style.display = cardText.includes(searchValue) ? "block" : "none";
                });

                filterDropdown.value = "all";
                heading.textContent = "Search Results";
            });

            // Hide all requirement cards initially
            requirementCards.forEach(card => card.style.display = "none");

            // Initially hide all details
            requirementCards.forEach(card => {
                const details = card.querySelector(".details");
                details.style.display = "none";  // Hide details initially
            });

            // Toggle details and chevron icon
            requirementCards.forEach(card => {
                card.addEventListener("click", function () {
                    const details = this.querySelector(".details");
                    const toggleIcon = this.querySelector(".toggle");

                    // Toggle the details visibility
                    if (details.style.display === "none") {
                        details.style.display = "block";  // Show details
                    } else {
                        details.style.display = "none";  // Hide details
                    }

                    // Toggle the chevron icon direction
                    if (details.style.display === "block") {
                        toggleIcon.classList.remove("fa-chevron-down");
                        toggleIcon.classList.add("fa-chevron-up");
                    } else {
                        toggleIcon.classList.remove("fa-chevron-up");
                        toggleIcon.classList.add("fa-chevron-down");
                    }
                });
            });
        });
    </script>

</body>
</html>
