<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply | NMG Insurance Agency</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/NMG3.png">

    <!-- External CSS link -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/view_requirements.css">

    <!-- FontAwesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="menu-container nav-wrapper">
            <div class="brand">
                <a href="index.php">
                    <img src="img/NMG22.png" alt="insurancy-logo">
                </a>
            </div>

            <ul class="nav-list">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="benefits.php">Insurance</a></li>
                <li><a href="contact.php">Contacts</a></li>
            </ul>
        </div>
    </nav>

    <!-- Car Insurance Requirements Section -->
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
                <!-- Apply Insurance Requirements -->
                <div class="requirement-card" data-type="apply-insurance">
                    <h3>📌 Valid ID <i class="fas fa-chevron-down toggle"></i></h3>
                    <p class="details">>A valid passport, driver’s license, or any government-issued ID is required.</p>
                </div>

                <div class="requirement-card" data-type="apply-insurance">
                    <h3>📌 Vehicle Plate Number <i class="fas fa-chevron-down toggle"></i></h3>
                    <p class="details">For insurance purposes, it helps verify the vehicle's identity, link it to a specific policy, and facilitate claims or legal processes.</p>
                </div>

                <div class="requirement-card" data-type="apply-insurance">
                    <h3>📌 Vehicle Chasis Number <i class="fas fa-chevron-down toggle"></i></h3>
                    <p class="details"> It serves as the vehicle’s fingerprint, containing details such as the make, model, year, and country of production.</p>
                </div>

                <div class="requirement-card" data-type="apply-insurance">
                    <h3>📌 Vehicle OR-CR <i class="fas fa-chevron-down toggle"></i></h3>
                    <p class="details">Proof of payment for vehicle registration.</p>
                </div>


                <!-- For Retrieval Requirements -->
                <div class="requirement-card" data-type="for-retrieval">
                    <h3>📌 Affidavit of Loss <i class="fas fa-chevron-down toggle"></i></h3>
                    <p class="details">If the document was lost, an affidavit of loss is required.</p>
                </div>

                <div class="requirement-card" data-type="for-retrieval">
                    <h3>📌 Police Blotter <i class="fas fa-chevron-down toggle"></i></h3>
                    <p class="details">Entry for a lost insurance document is a formal record detailing the loss.</p>
                </div>

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
    
        
        requirementCards.forEach(card => card.style.display = "none");
    
        
        requirementCards.forEach(card => {
            card.addEventListener("click", function () {
                this.querySelector(".details").classList.toggle("show");
            });
        });
    
      
        document.getElementById("download-btn").addEventListener("click", function () {
            alert("PDF Download Feature Coming Soon!");
        });
    });
</script>
    
    
    
    
</body>
</html>
