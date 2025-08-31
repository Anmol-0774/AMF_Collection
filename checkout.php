<?php
session_start();
require "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart with product details
$stmt = $conn->prepare("
SELECT c.id as cart_id, p.id as product_id, p.title, p.price, p.img, c.quantity, p.stock 
FROM cart c 
JOIN products p ON c.product_id = p.id
WHERE c.user_id = ?
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
$cart_items = [];
while($row = $res->fetch_assoc()){
    $cart_items[] = $row;
}

// Fetch saved addresses
$address_stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
$address_stmt->bind_param("i", $user_id);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
$saved_addresses = [];
while($row = $address_result->fetch_assoc()) {
    $saved_addresses[] = $row;
}

// Pakistan provinces and districts
$pakistan_provinces = [
    "Azad Kashmir" => ["Bhimber", "Kotli", "Mirpur", "Muzaffarabad", "Hattian", "Neelum", "Poonch", "Bagh", "Haveli", "Sudhnati"],
    "Balochistan" => ["Awaran", "Barkhan", "Chagai", "Dera Bugti", "Gwadar", "Harnai", "Jafarabad", "Jhal Magsi", "Kachhi", "Kalat", "Kech", "Kharan", "Khuzdar", "Killa Abdullah", "Killa Saifullah", "Kohlu", "Lasbela", "Lehri", "Loralai", "Mastung", "Musakhel", "Nasirabad", "Nushki", "Panjgur", "Pishin", "Quetta", "Sherani", "Sibi", "Sohbatpur", "Washuk", "Zhob", "Ziarat"],
    "Federal" => ["Islamabad"],
    "Gilgit-Baltistan" => ["Ghanche", "Ghizer", "Gilgit", "Hunza", "Kharmang", "Shigar", "Nagar", "Astore", "Diamer"],
    "Khyber Pakhtunkhwa" => ["Abbottabad", "Bajaur", "Bannu", "Battagram", "Buner", "Charsadda", "Chitral", "Dera Ismail Khan", "Hangu", "Haripur", "Karak", "Kohat", "Kurram", "Lakki Marwat", "Lower Dir", "Lower Kohistan", "Malakand", "Mansehra", "Mardan", "Mohmand", "North Waziristan", "Nowshera", "Orakzai", "Peshawar", "Shangla", "South Waziristan", "Swabi", "Swat", "Tank", "Torghar", "Upper Dir", "Upper Kohistan"],
    "Punjab" => ["Attock", "Bahawalnagar", "Bahawalpur", "Bhakkar", "Chakwal", "Chiniot", "Dera Ghazi Khan", "Faisalabad", "Gujranwala", "Gujrat", "Hafizabad", "Jhang", "Jhelum", "Kasur", "Khanewal", "Khushab", "Lahore", "Layyah", "Lodhran", "Mandi Bahauddin", "Mianwali", "Multan", "Muzaffargarh", "Narowal", "Nankana Sahib", "Okara", "Pakpattan", "Rahim Yar Khan", "Rajanpur", "Rawalpindi", "Sahiwal", "Sargodha", "Sheikhupura", "Sialkot", "Toba Tek Singh", "Vehari"],
    "Sindh" => ["Badin", "Dadu", "Ghotki", "Hyderabad", "Jacobabad", "Jamshoro", "Karachi Central", "Karachi East", "Karachi South", "Karachi West", "Kashmore", "Khairpur", "Larkana", "Matiari", "Mirpur Khas", "Naushahro Feroze", "Shaheed Benazirabad", "Qambar Shahdadkot", "Sanghar", "Shikarpur", "Sukkur", "Tando Allahyar", "Tando Muhammad Khan", "Tharparkar", "Thatta", "Umerkot", "Sujawal"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout · AMF Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body{background:#12121e;color:#fff;font-family:'Poppins',sans-serif;}
.card{background:#1c1c2e;border:none;border-radius:12px;color:#fff;}
.btn-gold{background:linear-gradient(140deg, #d4af37 0%, #ffd369 100%);color:#101018;border:none;font-weight:600;}
.btn-gold:hover{background:linear-gradient(140deg, #ffd369 0%, #d4af37 100%);color:#101018;}
.remove-btn{background:#ff4d4d;color:#fff;border:none;padding:3px 8px;border-radius:5px;}
.remove-btn:hover{background:#ff0000;}
.address-card {cursor: pointer; transition: all 0.3s;}
.address-card:hover {transform: translateY(-5px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);}
.address-card.selected {border: 2px solid #d4af37;}
.form-control {background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #fff;}
.form-control:focus {background: rgba(255,255,255,0.12); border-color: #d4af37; box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25); color: #fff;}
</style>
</head>
<body>
<div class="container py-5">
<h2 class="mb-4"><i class="bi bi-bag-check me-2"></i>Checkout</h2>

<?php if(empty($cart_items)): ?>
    <div class="alert alert-warning">Your cart is empty.</div>
<?php else: ?>
<div class="row">
    <div class="col-md-8">
        <!-- Delivery Address Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-truck me-2"></i>Delivery Address</h5>
            </div>
            <div class="card-body">
                <!-- Saved Addresses -->
                <?php if(!empty($saved_addresses)): ?>
                <h6 class="mb-3">Select a saved address:</h6>
                <div class="row mb-4">
                    <?php foreach($saved_addresses as $index => $address): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card address-card p-3" onclick="selectAddress(<?php echo $address['id']; ?>)">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="addressOption" 
                                    id="address<?php echo $address['id']; ?>" 
                                    value="<?php echo $address['id']; ?>">
                                <label class="form-check-label" for="address<?php echo $address['id']; ?>">
                                    <strong><?php echo htmlspecialchars($address['recipient_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($address['phone']); ?><br>
                                    <?php echo htmlspecialchars($address['address']); ?>, <?php echo htmlspecialchars($address['city']); ?><br>
                                    <?php echo htmlspecialchars($address['district']); ?>, <?php echo htmlspecialchars($address['province']); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <?php endif; ?>

                <h6 class="mb-3"><?php echo empty($saved_addresses) ? 'Add' : 'Or add a new'; ?> delivery address:</h6>
                
                <form id="addressForm" method="post" action="save_address.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recipient_name" class="form-label">Recipient Name *</label>
                            <input type="text" class="form-control" id="recipient_name" name="recipient_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="province" class="form-label">Province/Region *</label>
                            <select class="form-select" id="province" name="province" required>
                                <option value="">Select Province</option>
                                <?php foreach($pakistan_provinces as $province => $districts): ?>
                                    <option value="<?php echo $province; ?>"><?php echo $province; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="district" class="form-label">District *</label>
                            <select class="form-select" id="district" name="district" required disabled>
                                <option value="">Select District</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address_category" class="form-label">Address Category *</label>
                            <select class="form-select" id="address_category" name="address_category" required>
                                <option value="">Select Category</option>
                                <option value="home">Home</option>
                                <option value="office">Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Complete Address (House/Street No, Area) *</label>
                        <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="landmark" class="form-label">Landmark (Optional)</label>
                        <input type="text" class="form-control" id="landmark" name="landmark">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="save_address" name="save_address" checked>
                        <label class="form-check-label" for="save_address">
                            Save this address for future purchases
                        </label>
                    </div>
                    
                    <button type="button" class="btn btn-gold" onclick="addAddress()">Add Address</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Order Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                        <tr>
                            <td><?php echo $item['title']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'],2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total:</th>
                            <th>$<span id="grandTotal">0</span></th>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="d-grid">
                    <button type="button" class="btn btn-gold btn-lg" onclick="placeOrder()">Place Order</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>

<script>
// Pakistan provinces and districts data
const pakistanProvinces = <?php echo json_encode($pakistan_provinces); ?>;

// Update totals
function updateTotals(){
    let grandTotal = 0;
    <?php foreach($cart_items as $item): ?>
        grandTotal += <?php echo $item['price'] * $item['quantity']; ?>;
    <?php endforeach; ?>
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
}

// Province change handler
document.getElementById('province').addEventListener('change', function() {
    const province = this.value;
    const districtSelect = document.getElementById('district');
    
    districtSelect.innerHTML = '<option value="">Select District</option>';
    districtSelect.disabled = !province;
    
    if (province) {
        pakistanProvinces[province].forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        });
    }
});

// Address selection
function selectAddress(addressId) {
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelector(`#address${addressId}`).closest('.address-card').classList.add('selected');
    document.querySelector(`#address${addressId}`).checked = true;
}

// Add new address via AJAX
function addAddress() {
    const form = document.getElementById('addressForm');
    const formData = new FormData(form);
    
    fetch('save_address.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Address saved successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the address.');
    });
}

// Place order
function placeOrder() {
    const selectedAddress = document.querySelector('input[name="addressOption"]:checked');
    
    if (!selectedAddress) {
        alert('Please select or add a delivery address.');
        return;
    }
    
    // Submit the order with the selected address
    window.location.href = `place_order.php?address_id=${selectedAddress.value}`;
}

// Initialize totals on page load
updateTotals();
</script>
</body>
</html>