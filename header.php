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
    <nav id="mobile-nav" style="display: none; background: #1f2937; border: 1px solid #374151; border-radius: 8px; padding: 10px; position: absolute; width: 100%; left: 0; z-index: 100; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
        <ul style="display: block; list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 8px;"><a href="add_product.php" style="display: block; padding: 8px;">➕ Add Product</a></li>
            <li style="margin-bottom: 8px;"><a href="add_stock.php" style="display: block; padding: 8px;">📦 Add Stock</a></li>
            <li style="margin-bottom: 8px;"><a href="log_sale.php" style="display: block; padding: 8px;">💰 Log Sale</a></li>
            <li style="margin-bottom: 8px;"><a href="log_expense.php" style="display: block; padding: 8px;">💸 Log Expense</a></li>
            <li style="margin-bottom: 8px;"><a href="add_seller.php" style="display: block; padding: 8px;">👥 Workers</a></li>
            <li><a href="history.php" style="display: block; padding: 8px;">📜 History</a></li>
        </ul>
    </nav>
</header>

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