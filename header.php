<header style="position: relative;">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
        <!-- Brand Title (Now clickable to return home) -->
        <h2 style="margin: 0; font-size: 1.4rem;">
            <a href="index.php" style="text-decoration: none; color: inherit;">🥟 Maki's Siomai</a>
        </h2>
        
        <!-- Header Button Group (Always visible on mobile) -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <!-- Unnested Home Button -->
            <a href="index.php" role="button" class="outline" style="width: auto; padding: 6px 12px; margin: 0; font-size: 0.9rem; border-color: #374151; white-space: nowrap;">
                 Home
            </a>
            <!-- Hamburger Icon Button -->
            <button id="menu-toggle" class="outline" style="width: auto; padding: 5px 12px; margin: 0; font-size: 1.2rem; border-color: #374151;">
                ☰
            </button>
        </div>
    </div>

    <!-- Mobile Nav Dropdown (Hidden by default) -->
    <nav class="sticky-nav">
    <ul>
        <li><strong><a href="index.php" class="secondary">🏠 Home</a></strong></li>
    </ul>
    <ul>
        <li><a href="add_stock.php" class="outline">📥 Stock In</a></li>
        <li><a href="log_sale.php" class="outline">💰 Record Sale</a></li>
        <li><a href="add_expense.php" class="outline">📉 Expense</a></li>
    </ul>
</nav>

<style>
.sticky-nav {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #111923; /* Matches standard dark theme background */
    border-bottom: 1px solid #374151;
    padding: 10px 20px;
    margin-bottom: 20px;
}
/* Ensure main content doesn't get obscured and handles body padding */
body {
    padding-top: 0 !important;
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
<hr style="margin-top: 5px; margin-bottom: 20px;">

