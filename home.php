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

// Get categories
$categories = [];
if (!empty($products)) {
    $categories = array_unique(array_column($products, 'category'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AMF Collection — Luxury Fashion</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <!-- AOS (Animate on Scroll) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
  <!-- Swiper for banner carousel -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <style>
    :root{
      /* Luxury Indigo x Soft Gold */
      --bg-grad-1:#0f0c29;
      --bg-grad-2:#302b63;
      --bg-grad-3:#24243e;
      --surface:#111322; /* deep surface */
      --card:#171a2e; /* card base */
      --muted:#9aa0b4; /* muted text */
      --text:#f5f7ff; /* main text */
      --gold:#d4af37; /* metallic gold */
      --gold-soft:#ffd369; /* soft gold for UI */
      --accent:#7aa2ff; /* cool accent */
      --shadow: rgba(0,0,0,.45);
      --glass: rgba(255,255,255,.06);
      --glass-stroke: rgba(255,255,255,.12);
      --radius: 18px;
    }

    html,body{
      background: linear-gradient(120deg,var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial;
    }

    /* ===== Navbar ===== */
    .navbar{
      backdrop-filter: saturate(140%) blur(10px);
      background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
      border-bottom: 1px solid var(--glass-stroke);
    }
    .navbar-brand{
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      letter-spacing:.5px;
      color: var(--gold) !important;
    }
    .nav-link{ color:#e9ecff !important; opacity:.85; transition: .25s ease; }
    .nav-link:hover{ color: var(--gold-soft) !important; opacity:1; }
    .btn-gold{
      background: linear-gradient(140deg, var(--gold) 0%, var(--gold-soft) 100%);
      color:#14151f; border:none; font-weight:600; border-radius: 999px; padding:.6rem 1rem;
      box-shadow: 0 6px 20px rgba(212,175,55,.25);
      transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
    }
    .btn-gold:hover{ transform: translateY(-2px); filter: brightness(1.05); box-shadow:0 10px 26px rgba(212,175,55,.35); }

    /* ===== Hero ===== */
    .hero{
      position: relative; isolation:isolate;
      min-height: 84vh; display:grid; place-items:center; text-align:center; overflow:hidden;
    }
    .hero::before{
      content:""; position:absolute; inset:0; z-index:-2;
      background: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat fixed;
      filter: saturate(0.9) brightness(.55) contrast(1.05);
    }
    .hero::after{
      content:""; position:absolute; inset:0; z-index:-1;
      background: radial-gradient(1200px 600px at 50% 30%, rgba(212,175,55,.25), transparent 60%);
      mix-blend-mode: screen;
    }
    .hero-badge{
      display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .9rem; border-radius:999px;
      background: var(--glass); border:1px solid var(--glass-stroke); color:#fff; font-size:.9rem;
      backdrop-filter: blur(6px);
    }
    .hero-title{
      font-family:'Playfair Display', serif; font-weight:700; letter-spacing:.3px;
      font-size: clamp(2.2rem, 4.5vw, 4rem);
      line-height:1.05; color:#fff;
      text-shadow: 0 10px 30px rgba(0,0,0,.45);
    }
    .hero-sub{ color:#dfe3ff; opacity:.9; max-width: 760px; margin-inline:auto; }

    /* Floating orbs */
    .orb{ position:absolute; border-radius:50%; filter: blur(30px); opacity:.35; animation: float 12s ease-in-out infinite; }
    .orb.gold{ background: radial-gradient(circle at 40% 30%, var(--gold), transparent 60%); width:220px; height:220px; top:10%; left:8%; }
    .orb.blue{ background: radial-gradient(circle at 60% 60%, var(--accent), transparent 60%); width:260px; height:260px; bottom:8%; right:10%; animation-delay:2.5s; }
    @keyframes float{ 0%,100%{ transform: translateY(0)} 50%{ transform: translateY(-14px)} }

    /* ===== Utility strip (USPs) ===== */
    .usp{
      background: rgba(255,255,255,.04);
      border:1px solid var(--glass-stroke);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px var(--shadow);
      backdrop-filter: blur(8px);
    }

    /* ===== Section headings ===== */
    .section-title{
      font-family:'Playfair Display', serif; font-weight:700; color:#fff; letter-spacing:.2px;
    }
    .section-sub{ color: var(--muted); }

    /* ===== Product cards ===== */
    .product-card{
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
      border:1px solid var(--glass-stroke);
      border-radius: var(--radius);
      overflow:hidden;
      box-shadow: 0 10px 30px var(--shadow);
      transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
      cursor: pointer;
    }
    .product-card:hover{
      transform: translateY(-10px);
      border-color: rgba(212,175,55,.45);
      box-shadow: 0 18px 48px rgba(0,0,0,.6), 0 10px 30px rgba(212,175,55,.2);
    }
    .product-img{ aspect-ratio: 1/1; object-fit: cover; width:100%; filter: saturate(1.05) contrast(1.02); }
    .price{ color: var(--gold-soft); font-weight:700; font-size:1.05rem; }
    .tag{ display:inline-block; font-size:.75rem; color:#111; background: linear-gradient(160deg, var(--gold) 0%, var(--gold-soft) 100%); padding:.35rem .65rem; border-radius: 999px; font-weight:700; }

    /* ===== Category chips ===== */
    .chip{ background:var(--glass); border:1px solid var(--glass-stroke); color:#e9ecff; padding:.55rem .9rem; border-radius:999px; transition: .25s; backdrop-filter: blur(6px); }
    .chip:hover{ border-color: var(--gold-soft); color:#fff; transform: translateY(-2px); }

    /* ===== Newsletter ===== */
    .newsletter{
      background: linear-gradient(140deg, rgba(20,20,36,.7), rgba(20,20,36,.35));
      border:1px solid var(--glass-stroke);
      border-radius: var(--radius);
      backdrop-filter: blur(8px);
    }
    .form-control{ background: rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); color:#fff; }
    .form-control::placeholder{ color:#cbd1ff; opacity:.6; }

    /* ===== Footer ===== */
    footer{
      border-top:1px solid var(--glass-stroke);
      background: rgba(10,11,22,.6);
    }

    /* Small helpers */
    .divider{ height:1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent); }
    
    /* Banner slider */
    .swiper {
      width: 100%;
      height: 400px;
      border-radius: var(--radius);
      overflow: hidden;
      margin: 30px 0;
    }
    .swiper-slide {
      background-position: center;
      background-size: cover;
    }
    .swiper-pagination-bullet {
      background: var(--text);
      opacity: 0.5;
    }
    .swiper-pagination-bullet-active {
      background: var(--gold);
      opacity: 1;
    }
    
    /* Search and filter section */
    .search-filter {
      background: var(--glass);
      border: 1px solid var(--glass-stroke);
      border-radius: var(--radius);
      padding: 15px;
      margin-bottom: 30px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="home.php">
      <i class="bi bi-gem"></i>
      <span>AMF Collection</span>
    </a>
    
    <div class="d-flex align-items-center gap-3 ms-auto">
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="account.php" class="text-white text-decoration-none">My Account</a>
        <a href="logout.php" class="text-white text-decoration-none">Logout</a>
        <a href="order_history.php" class="text-white text-decoration-none">My Orders</a>
        <a class="btn btn-outline-light position-relative" href="checkout.php">
          <i class="bi bi-bag"></i>
          <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
          </span>
        </a>
      <?php else: ?>
        <a href="login.php" class="text-white text-decoration-none">Login</a>
        <a href="signup.php" class="text-white text-decoration-none">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

  <!-- ===== HERO ===== -->
  <header class="hero d-grid">
    <div class="orb gold"></div>
    <div class="orb blue"></div>

    <div class="container position-relative">
      <div class="hero-badge mb-4 animate__animated animate__fadeInDown">
        <i class="bi bi-stars"></i>
        Premium • Limited Releases • Fast Delivery
      </div>
      <h1 class="hero-title animate__animated animate__fadeInUp animate__delay-1s">Where Luxury Meets Everyday Style</h1>
      <p class="hero-sub mt-3 animate__animated animate__fadeInUp animate__delay-2s">Discover curated fashion, cosmetics, footwear, and decor — crafted to elevate your daily look. Experience the AMF difference with premium quality and a seamless shopping journey.</p>
      <div class="d-flex justify-content-center gap-3 mt-4 animate__animated animate__fadeInUp animate__delay-3s">
        <a href="#products" class="btn btn-gold"><i class="bi bi-bag-check me-1"></i> Shop Now</a>
        <a href="#new" class="btn btn-outline-light" style="border-radius:999px; border:1px solid var(--glass-stroke); backdrop-filter: blur(6px);">New Arrivals</a>
      </div>
    </div>
  </header>

  <div class="container my-5">
    <!-- ===== USP STRIP ===== -->
    <div class="usp p-3 p-md-4" data-aos="fade-up">
      <div class="row g-3 text-center text-md-start">
        <div class="col-6 col-md-3 d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
          <i class="bi bi-truck fs-4 text-warning"></i>
          <div>
            <div class="fw-semibold">Fast Delivery</div>
            <div class="small text-muted">2–4 business days</div>
          </div>
        </div>
        <div class="col-6 col-md-3 d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
          <i class="bi bi-shield-check fs-4 text-warning"></i>
          <div>
            <div class="fw-semibold">Secure Payments</div>
            <div class="small text-muted">SSL & PCI compliant</div>
          </div>
        </div>
        <div class="col-6 col-md-3 d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
          <i class="bi bi-arrow-repeat fs-4 text-warning"></i>
          <div>
            <div class="fw-semibold">Easy Returns</div>
            <div class="small text-muted">14-day guarantee</div>
          </div>
        </div>
        <div class="col-6 col-md-3 d-flex align-items-center gap-2 justify-content-center justify-content-md-start">
          <i class="bi bi-gift fs-4 text-warning"></i>
          <div>
            <div class="fw-semibold">Member Rewards</div>
            <div class="small text-muted">Exclusive perks</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Banner Slider -->
    <div class="swiper my-5" data-aos="fade-up">
      <div class="swiper-wrapper">
        <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1500&auto=format&fit=crop')"></div>
        <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=1500&auto=format&fit=crop')"></div>
        <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1520006403909-838d6b92c22e?q=80&w=1500&auto=format&fit=crop')"></div>
      </div>
      <div class="swiper-pagination"></div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter" data-aos="fade-up">
      <div class="row g-3">
        <div class="col-md-6">
          <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
        </div>
        <div class="col-md-3">
          <select id="categoryFilter" class="form-select">
            <option value="All">All Categories</option>
            <?php foreach($categories as $cat): ?>
              <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <select id="sortFilter" class="form-select">
            <option value="newest">Newest First</option>
            <option value="low">Price: Low to High</option>
            <option value="high">Price: High to Low</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ===== PRODUCTS SECTION ===== -->
    <section id="products" class="my-5">
      <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-3">
        <div>
          <h2 class="section-title mb-1" data-aos="fade-right">Our Products</h2>
          <p class="section-sub" data-aos="fade-right" data-aos-delay="100">Discover our premium collection.</p>
        </div>
        <span id="itemCount" class="text-muted mt-3 mt-md-0" data-aos="fade-left"></span>
      </div>

      <div class="row g-4" id="productsContainer">
        <!-- Products will be loaded here by JavaScript -->
        <?php foreach($products as $product): ?>
        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
          <article class="product-card h-100" data-id="<?php echo $product['id']; ?>">
            <img class="product-img" src="<?php echo $product['img']; ?>" alt="<?php echo $product['title']; ?>" />
            <div class="p-3 p-md-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0"><?php echo $product['title']; ?></h5>
                <span class="tag"><?php echo $product['category']; ?></span>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                <button class="btn btn-gold btn-sm add-to-cart-btn" data-id="<?php echo $product['id']; ?>"><i class="bi bi-bag-plus me-1"></i>Add</button>
              </div>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ===== NEWSLETTER ===== -->
    <section class="newsletter p-4 p-md-5 my-5" data-aos="fade-up">
      <div class="row align-items-center g-3">
        <div class="col-12 col-md-7">
          <h3 class="section-title mb-2">Join AMF Insider</h3>
          <p class="section-sub">Get first access to drops, secret sales & style tips. No spam, ever.</p>
        </div>
        <div class="col-12 col-md-5">
          <form class="d-flex gap-2">
            <input class="form-control form-control-lg" type="email" placeholder="Enter your email" required>
            <button class="btn btn-gold btn-lg" type="submit">Subscribe</button>
          </form>
        </div>
      </div>
    </section>
  </div>

  <!-- ===== FOOTER ===== -->
  <footer class="pt-5 pb-4 mt-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-12 col-md-4">
          <div class="d-flex align-items-center gap-2 mb-2 text-warning"><i class="bi bi-gem"></i><strong>AMF Collection</strong></div>
          <p class="text-muted">Curated luxury essentials for your everyday life. Designed with love, delivered with care.</p>
        </div>
        <div class="col-6 col-md-2">
          <h6 class="text-white-50">Shop</h6>
          <ul class="list-unstyled small">
            <?php foreach($categories as $cat): ?>
              <li><a class="text-decoration-none text-muted" href="#" data-category="<?php echo $cat; ?>"><?php echo $cat; ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="col-6 col-md-3">
          <h6 class="text-white-50">Support</h6>
          <ul class="list-unstyled small">
            <li><a class="text-decoration-none text-muted" href="#">FAQ</a></li>
            <li><a class="text-decoration-none text-muted" href="#">Shipping & Returns</a></li>
            <li><a class="text-decoration-none text-muted" href="#">Privacy Policy</a></li>
            <li><a class="text-decoration-none text-muted" href="#">Terms of Service</a></li>
          </ul>
        </div>
        <div class="col-12 col-md-3">
          <h6 class="text-white-50">Stay Connected</h6>
          <div class="d-flex gap-3 fs-5">
            <a class="text-muted" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a class="text-muted" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a class="text-muted" href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a class="text-muted" href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>
      </div>
      <div class="divider my-4"></div>
      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center text-muted small">
        <span>© 2025 AMF Collection. All rights reserved.</span>
        <span>Crafted with <i class="bi bi-heart-fill text-danger"></i> for style lovers.</span>
      </div>
    </div>
  </footer>

  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Login to Your Account</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email address</label>
              <input type="email" class="form-control" id="loginEmail">
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="loginPassword">
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <button type="submit" class="btn btn-gold w-100">Login</button>
          </form>
        </div>
        <div class="modal-footer justify-content-center">
          <span class="text-muted">Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Sign Up</a></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Register Modal -->
  <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create an Account</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="registerName" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="registerName">
            </div>
            <div class="mb-3">
              <label for="registerEmail" class="form-label">Email address</label>
              <input type="email" class="form-control" id="registerEmail">
            </div>
            <div class="mb-3">
              <label for="registerPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="registerPassword">
            </div>
            <div class="mb-3">
              <label for="registerConfirmPassword" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="registerConfirmPassword">
            </div>
            <button type="submit" class="btn btn-gold w-100">Create Account</button>
          </form>
        </div>
        <div class="modal-footer justify-content-center">
          <span class="text-muted">Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      easing: 'ease-out-quart',
      once: true,
      offset: 80
    });

    // Initialize Swiper
    const swiper = new Swiper('.swiper', {
      autoplay: {
        delay: 5000,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      loop: true,
    });

    // Get PHP products in JS
    let products = <?php echo json_encode($products); ?>;

    const productsContainer = document.getElementById('productsContainer');
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortFilter = document.getElementById('sortFilter');
    const itemCount = document.getElementById('itemCount');

    // Function to render products
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
        productsContainer.innerHTML = filtered.length ? "" : "<p class='text-muted text-center py-5'>No products found.</p>";

        filtered.forEach(p => {
            productsContainer.innerHTML += `
            <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
              <article class="product-card h-100" data-id="${p.id}">
                <img class="product-img" src="${p.img}" alt="${p.title}" />
                <div class="p-3 p-md-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">${p.title}</h5>
                    <span class="tag">${p.category}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="price">$${parseFloat(p.price).toFixed(2)}</div>
                    <button class="btn btn-gold btn-sm add-to-cart-btn" data-id="${p.id}"><i class="bi bi-bag-plus me-1"></i>Add</button>
                  </div>
                </div>
              </article>
            </div>
            `;
        });

        // Add event listeners to product cards and buttons
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.classList.contains('add-to-cart-btn')) {
                    const productId = card.getAttribute('data-id');
                    // Redirect to product details page
                    window.location.href = `product-details.php?id=${productId}`;
                }
            });
        });

        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const productId = btn.getAttribute('data-id');
                addToCart(productId, 1);
            });
        });
    }

    // Function to add product to cart
    function addToCart(productId, quantity) {
        // In a real application, you would make an AJAX call to add to cart
        // For this example, we'll redirect to the product details page with add to cart action
        window.location.href = `product-details.php?id=${productId}&action=add&quantity=${quantity}`;
    }

    // Event listeners for search and filter
    searchInput.addEventListener('input', renderProducts);
    categoryFilter.addEventListener('change', renderProducts);
    sortFilter.addEventListener('change', renderProducts);

    // Category links in footer
    document.querySelectorAll('a[data-category]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const category = link.getAttribute('data-category');
            categoryFilter.value = category;
            renderProducts();
            
            // Scroll to products section
            document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // First render
    renderProducts();
  </script>
</body>
</html>