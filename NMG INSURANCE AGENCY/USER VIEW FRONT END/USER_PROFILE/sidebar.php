<aside class="sidebar">
    <div class="logo">
        <img src="../img/NMG3.png" alt="NMG Insurance Logo"> 
    </div>
    <nav class="menu">
        <button class="menu-item <?php echo ($_SERVER['PHP_SELF'] == '/profile.php') ? 'active' : ''; ?>" aria-label="Home" onclick="window.location.href='profile.php'">
            <img src="../img/dashboardd.png" alt="Dashboard Icon">
            <span>Home</span>
        </button>
        <button class="menu-item <?php echo ($_SERVER['PHP_SELF'] == '/transactions.php') ? 'active' : ''; ?>" aria-label="Transactions" onclick="window.location.href='transactions.php'">
            <img src="../img/transaction.png" alt="Transactions Icon">
            <span>Transactions</span>
        </button>
        <button class="menu-item <?php echo ($_SERVER['PHP_SELF'] == '/register_insurance.php') ? 'active' : ''; ?>" aria-label="Apply Insurance" onclick="window.location.href='../register_insurance.php'">
            <img src="../img/apply.png" alt="Apply Insurance Icon">
            <span>Apply Insurance</span>
        </button>
        <button class="menu-item <?php echo ($_SERVER['PHP_SELF'] == '/lost_documents.php') ? 'active' : ''; ?>" aria-label="Lost Documents" onclick="window.location.href='lost_documents.php'">
            <img src="../img/lost.png" alt="Lost Documents Icon">
            <span>Lost Documents</span>
        </button>
    </nav>
</aside>
