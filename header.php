<header class="sticky-header">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
        <!-- Brand Title -->
        <h2 style="margin: 0; font-size: 1.4rem;">
            <a href="index.php" style="text-decoration: none; color: inherit;">🥟 Maki's Siomai</a>
        </h2>
        
        <!-- Header Button Group -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="index.php" role="button" class="outline" style="width: auto; padding: 6px 12px; margin: 0; font-size: 0.9rem; border-color: #374151; white-space: nowrap;">
                Home
            </a>
            <button id="menu-toggle" class="outline" style="width: auto; padding: 5px 12px; margin: 0; font-size: 1.2rem; border-color: #374151;">
                ☰
            </button>
        </div>
    </div>

    <!-- Mobile Nav Dropdown (All features restored!) -->
    <nav id="mobile-nav" class="dropdown-nav" style="display: none;">
        <div class="menu-grid">
            <a href="add_stock.php" role="button" class="outline">📥 Stock In</a>
            <a href="log_sale.php" role="button" class="outline">💰 Record Sale</a>
            <a href="add_expense.php" role="button" class="outline">📉 Expense</a>
            <a href="add_product.php" role="button" class="outline">📦 Add Product</a>
            <a href="workers.php" role="button" class="outline">👥 Workers</a>
        </div>
    </nav>
</header>

<style>
.sticky-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #111923;
    border-bottom: 1px solid #374151;
    margin-bottom: 20px;
}

.dropdown-nav {
    background: #182330;
    border-top: 1px solid #374151;
    padding: 15px 20px;
}

/* Arrange menu items in a clean, responsive grid layout */
.menu-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 items on the first row */
    gap: 10px;
}

/* Make the last two items stretch cleanly or format on the second row */
.menu-grid a {
    text-align: center;
    padding: 10px 5px;
    font-size: 0.85rem;
    white-space: nowrap;
    margin: 0;
}

/* Adjust grid for smaller phones so things fit perfectly */
@media (max-width: 480px) {
    .menu-grid {
        grid-template-columns: repeat(2, 1fr); /* Switch to 2 columns on small screens */
    }
    .menu-grid a {
        font-size: 0.8rem;
        padding: 8px 4px;
    }
}
</style>

<script>
document.getElementById('menu-toggle').addEventListener('click', function() {
    var nav = document.getElementById('mobile-nav');
    if (nav.style.display === 'none' || nav.style.display === '') {
        nav.style.display = 'block';
        this.textContent = '✕';
    } else {
        nav.style.display = 'none';
        this.textContent = '☰';
    }
});
</script>