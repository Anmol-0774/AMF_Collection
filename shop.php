<?php
session_start();
require "db.php"; // DB connection

// Fetch products from DB
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Shop · AMF Collection</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{ font-family:'Poppins',sans-serif; background:#12121e; color:#fff; }
.navbar{ background: rgba(12,12,22,.9); border-bottom:1px solid rgba(255,255,255,.1); backdrop-filter: blur(6px); }
.navbar-brand{ font-family:'Playfair Display',serif; color:#d4af37; }
.navbar-brand:hover{ color:#ffd369; }
.btn-gold{ background:#d4af37; color:#101018; border:none; font-weight:600; }
.btn-gold:hover{ background:#ffd369; color:#101018; }
.card{ background:#1c1c2e; border:none; border-radius:12px; transition:.3s; }
.card:hover{ transform:translateY(-6px); box-shadow:0 10px 25px rgba(0,0,0,.5); }
.card img{ border-radius:12px 12px 0 0; height:230px; object-fit:cover; }
.price{ color:#ffd369; font-weight:700; font-size:1.1rem; }
.sidebar{ background:#1c1c2e; padding:20px; border-radius:12px; position:sticky; top:90px; }
.sidebar h5{ font-weight:600; margin-bottom:15px; color:#ffd369; }
.form-select, .form-control{ background:#12121e; border:1px solid rgba(255,255,255,.2); color:#fff; }
.form-select:focus, .form-control:focus{ border-color:#ffd369; box-shadow:none; }
</style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top py-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="home.php"><i class="bi bi-gem"></i> AMF Collection</a>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="shop.php">Shop</a></li>
        <li class="nav-item ms-lg-2">
          <a class="btn btn-outline-light position-relative" href="checkout.php">
            <i class="bi bi-bag"></i>
            <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main -->
<main class="container my-5">
  <div class="row">
    <!-- Sidebar -->
    <aside class="col-md-3">
      <div class="sidebar mb-4">
        <h5>Search</h5>
        <input id="searchInput" type="text" class="form-control mb-3" placeholder="Search products...">

        <h5>Categories</h5>
        <select id="categoryFilter" class="form-select mb-3">
          <option value="All">All</option>
          <?php
          $categories = array_unique(array_column($products, 'category'));
          foreach($categories as $cat){
              echo "<option value=\"$cat\">$cat</option>";
          }
          ?>
        </select>

        <h5>Sort by</h5>
        <select id="sortFilter" class="form-select mb-3">
          <option value="newest">Newest</option>
          <option value="low">Price: Low to High</option>
          <option value="high">Price: High to Low</option>
        </select>
      </div>
    </aside>

    <!-- Products -->
    <div class="col-md-9">
      <h4 class="mb-4">Shop <small class="text-muted" id="itemCount"></small></h4>
      <div id="productsContainer" class="row g-4">
        <!-- Products loaded by JS -->
      </div>
    </div>
  </div>
</main>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const productsContainer = document.getElementById('productsContainer');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const sortFilter = document.getElementById('sortFilter');
const itemCount = document.getElementById('itemCount');

// Get PHP products in JS
let products = <?php echo json_encode($products); ?>;

function renderProducts(){
    let filter = searchInput.value.toLowerCase();
    let category = categoryFilter.value;
    let sort = sortFilter.value;

    let filtered = products.filter(p => 
        (p.title.toLowerCase().includes(filter) || p.description.toLowerCase().includes(filter)) &&
        (category === "All" || p.category === category)
    );

    // Sorting
    if(sort === "low") filtered.sort((a,b)=> a.price - b.price);
    if(sort === "high") filtered.sort((a,b)=> b.price - a.price);
    if(sort === "newest") filtered.sort((a,b)=> b.id - a.id);

    // Count
    itemCount.textContent = `Showing ${filtered.length} items`;

    // Render
    productsContainer.innerHTML = filtered.length ? "" : "<p class='text-muted'>No products found.</p>";

    filtered.forEach(p=>{
        productsContainer.innerHTML += `
        <div class="col-md-4">
          <div class="card h-100">
            <img src="${p.img}" class="card-img-top" alt="${p.title}">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">${p.title}</h5>
              <p class="price">$${parseFloat(p.price).toFixed(2)}</p>
              <p class="small">${p.description.substring(0,60)}...</p>
              <a href="product-details.php?id=${p.id}" class="btn btn-gold mt-auto">View Details</a>
            </div>
          </div>
        </div>
        `;
    });
}

// Listeners
searchInput.addEventListener('input', renderProducts);
categoryFilter.addEventListener('change', renderProducts);
sortFilter.addEventListener('change', renderProducts);

// First render
renderProducts();
</script>
</body>
</html>
