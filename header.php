<header class="sticky-header">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
        <!-- Brand Title -->
        <h2 style="margin: 0; font-size: 1.4rem;">
            <a href="index.php" style="text-decoration: none; color: inherit;">🥟 Maki's Siomai</a>
        </h2>
        
        <!-- Header Button Group (Always visible) -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="index.php" role="button" class="outline" style="width: auto; padding: 6px 12px; margin: 0; font-size: 0.9rem; border-color: #374151; white-space: nowrap;">
                Home
            </a>
            <button id="menu-toggle" class="outline" style="width: auto; padding: 5px 12px; margin: 0; font-size: 1.2rem; border-color: #374151;">
                ☰
            </button>
        </div>
    </div>

    <!-- Mobile Nav Dropdown (Hidden by default, slides down smoothly) -->
    <nav id="mobile-nav" class="dropdown-nav" style="display: none;">
        <ul>
            <li><a href="add_stock.php" class="outline">📥 Stock In</a></li>
            <li><a href="log_sale.php" class="outline">💰 Record Sale</a></li>
            <li><a href="add_expense.php" class="outline">📉 Expense</a></li>
        </ul>
    </nav>
</header>

<style>
/* Make the entire header bar sticky at the very top */
.sticky-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #111923; /* Matches Pico CSS dark theme background */
    border-bottom: 1px solid #374151;
    margin-bottom: 20px;
}

/* Style the dropdown menu so it appears clean when opened */
.dropdown-nav {
    background: #182330;
    border-top: 1px solid #374151;
    padding: 15px 20px;
}

.dropdown-nav ul {
    display: flex;
    flex-direction: column;
    gap: 10px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.dropdown-nav ul li a {
    display: block;
    width: 100%;
    text-align: center;
}
</style>

<script>
document.getElementById('menu-toggle').addEventListener('click', function() {
    var nav = document.getElementById('mobile-nav');
    if (nav.style.display === 'none' || nav.style.display === '') {
        nav.style.display = 'block';
        this.textContent = '✕'; // Change burger to close icon
    } else {
        nav.style.display = 'none';
        this.textContent = '☰';
    }
});
</script>