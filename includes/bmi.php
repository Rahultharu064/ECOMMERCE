<?php

include "../includes/config.php";
include "../includes/auth.php";

// BMI Calculation Functions
function getBMICategory($bmi) {
    if ($bmi < 16) return 'Severely Underweight';
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal Weight';
    if ($bmi < 30) return 'Overweight';
    if ($bmi < 35) return 'Obese Class I';
    if ($bmi < 40) return 'Obese Class II';
    return 'Obese Class III';
}

function calculateBodyFat($age, $bmi, $gender) {
    return (1.20 * $bmi) + (0.23 * $age) - ($gender == 'male' ? 16.2 : 5.4);
}

function calculateIdealWeight($height, $gender) {
    return $gender == 'male' 
        ? 48 + 1.1 * ($height - 152) 
        : 45.4 + 0.9 * ($height - 152);
}

// Default Recommendations
$default_recommendations = [
    'Severely Underweight' => [
        ['name' => 'High-Calorie Nutritional Shake', 'price' => 899, 'type' => 'Supplement', 'image' => 'nutritional-shake.jpg'],
        ['name' => 'Multivitamin Complex Capsules', 'price' => 599, 'type' => 'Supplement', 'image' => 'multivitamin.jpg'],
        ['name' => 'Appetite Stimulant Syrup', 'price' => 349, 'type' => 'Medicine', 'image' => 'appetite-syrup.jpg']
    ],
    'Underweight' => [
        ['name' => 'Weight Gain Protein Powder', 'price' => 1299, 'type' => 'Supplement', 'image' => 'protein-powder.jpg'],
        ['name' => 'Digestive Enzymes Formula', 'price' => 649, 'type' => 'Supplement', 'image' => 'enzymes.jpg'],
        ['name' => 'Nutritional Boost Tablets', 'price' => 499, 'type' => 'Supplement', 'image' => 'boost-tablets.jpg']
    ],
    'Normal Weight' => [
        ['name' => 'Daily Multivitamin Gummies', 'price' => 399, 'type' => 'Supplement', 'image' => 'gummies.jpg'],
        ['name' => 'Omega-3 Fish Oil Supplements', 'price' => 799, 'type' => 'Supplement', 'image' => 'fish-oil.jpg'],
        ['name' => 'Immune Support Capsules', 'price' => 549, 'type' => 'Supplement', 'image' => 'immune-capsules.jpg']
    ],
    'Overweight' => [
        ['name' => 'Metabolism Boost Tea', 'price' => 299, 'type' => 'Supplement', 'image' => 'herbal-tea.jpg'],
        ['name' => 'Meal Replacement Shakes', 'price' => 999, 'type' => 'Supplement', 'image' => 'meal-shake.jpg'],
        ['name' => 'Soluble Fiber Supplement', 'price' => 449, 'type' => 'Supplement', 'image' => 'fiber-supplement.jpg']
    ],
    'Obese Class I' => [
        ['name' => 'Weight Management Capsules', 'price' => 899, 'type' => 'Supplement', 'image' => 'weight-capsules.jpg'],
        ['name' => 'Blood Sugar Support Formula', 'price' => 749, 'type' => 'Supplement', 'image' => 'sugar-support.jpg'],
        ['name' => 'Cholesterol Control Tablets', 'price' => 649, 'type' => 'Medicine', 'image' => 'cholesterol-tablets.jpg']
    ],
    'Obese Class II' => [
        ['name' => 'Clinical Nutrition Pack', 'price' => 1599, 'type' => 'Supplement', 'image' => 'nutrition-pack.jpg'],
        ['name' => 'Metabolic Rate Optimizer', 'price' => 1199, 'type' => 'Supplement', 'image' => 'metabolic-optimizer.jpg'],
        ['name' => 'Professional Coaching Program', 'price' => 2499, 'type' => 'Service', 'image' => 'coaching-program.jpg']
    ],
    'Obese Class III' => [
        ['name' => 'Medical Grade Supplements', 'price' => 1999, 'type' => 'Supplement', 'image' => 'medical-supplements.jpg'],
        ['name' => 'Bariatric Care Package', 'price' => 2999, 'type' => 'Package', 'image' => 'bariatric-care.jpg'],
        ['name' => '1-on-1 Nutritionist Session', 'price' => 1499, 'type' => 'Service', 'image' => 'nutritionist-session.jpg']
    ]
];

// Get Recommended Products
function getRecommendedProducts($conn, $category, $default_recommendations) {
    // Try database first
    $category = mysqli_real_escape_string($conn, $category);
    $query = "SELECT id, product_name as name, price, image_path, 
              'product' as source, prescription_required 
              FROM products 
              WHERE bmi_category = '$category' 
              AND quantity > 0
              ORDER BY RAND() 
              LIMIT 6";
    $result = mysqli_query($conn, $query);
    
    $products = [];
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    // Fallback to default recommendations
    if(empty($products) && isset($default_recommendations[$category])) {
        foreach($default_recommendations[$category] as $item) {
            $products[] = [
                'id' => 0,
                'name' => $item['name'],
                'price' => $item['price'],
                'image_path' => '../uploads/products/' . $item['image'],
                'source' => 'default',
                'prescription_required' => ($item['type'] == 'Medicine') ? 'Yes' : 'No',
                'type' => $item['type']
            ];
        }
    }
    
    return $products;
}

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $age = intval($_POST['age']);
    $gender = in_array($_POST['gender'], ['male', 'female']) ? $_POST['gender'] : 'male';
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $user_id = $_SESSION['user_id'];
    
    // Validate Inputs
    $errors = [];
    if ($age < 1 || $age > 120) $errors[] = "Age must be between 1-120";
    if ($weight < 20 || $weight > 300) $errors[] = "Weight must be between 20-300 kg";
    if ($height < 100 || $height > 250) $errors[] = "Height must be between 100-250 cm";
    
    if (empty($errors)) {
        // Calculate Metrics
        $bmi = $weight / (($height / 100) ** 2);
        $category = getBMICategory($bmi);
        $body_fat = calculateBodyFat($age, $bmi, $gender);
        $ideal_weight = calculateIdealWeight($height, $gender);
        
        // Save to Database
        $stmt = mysqli_prepare($conn, "INSERT INTO bmi_history 
            (user_id, age, gender, weight, height, bmi, category, body_fat, ideal_weight) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisdddddd", 
            $user_id, $age, $gender, $weight, $height, $bmi, $category, $body_fat, $ideal_weight);
        mysqli_stmt_execute($stmt);
        
        // Get Recommendations
        $recommended_products = getRecommendedProducts($conn, $category, $default_recommendations);
        
        // Store Results
        $_SESSION['bmi_result'] = [
            'bmi' => $bmi,
            'category' => $category,
            'body_fat' => $body_fat,
            'ideal_weight' => $ideal_weight,
            'products' => $recommended_products,
            'source' => !empty($recommended_products) && $recommended_products[0]['source'] == 'default' ? 'default' : 'database'
        ];
        
        header("Location: bmi.php");
        exit();
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Get User's BMI History
$history = [];
$history_query = "SELECT * FROM bmi_history 
                 WHERE user_id = {$_SESSION['user_id']} 
                 ORDER BY created_at DESC 
                 LIMIT 5";
$history_result = mysqli_query($conn, $history_query);
while($row = mysqli_fetch_assoc($history_result)) {
    $history[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Analysis | MediCare Pharmacy</title>
   
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2A9D8F;
            --secondary: #264653;
            --accent: #E9C46A;
            --danger: #E76F51;
            --success: #588157;
            --light-bg: #f8fbfe;
        }

        .health-container {
            max-width: 1200px;
            margin: 230px auto;
            padding: 2rem;
        }

        .health-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .calculator-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .input-group input, 
        .input-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(42,157,143,0.2);
        }

        .calculate-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .result-container {
            margin-top: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .result-card {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid var(--primary);
            margin-bottom: 2rem;
        }

        #bmi-value {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary);
            margin: 1rem 0;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid var(--primary);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .product-card {
            position: relative;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .product-image {
            height: 150px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--secondary);
        }

        .product-price {
            color: var(--primary);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .view-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .prescription-badge {
            background: var(--danger);
            color: white;
        }

        .default-badge {
            background: var(--accent);
            color: var(--secondary);
        }

        .recommendation-source {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
            text-align: center;
        }

        .source-database {
            color: var(--primary);
            font-weight: bold;
        }

        .source-default {
            color: var(--accent);
            font-weight: bold;
        }

        .bmi-category {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .severely-underweight { background: #ffcdd2; color: #c62828; }
        .underweight { background: #ffecb3; color: #ff8f00; }
        .normal { background: #c8e6c9; color: #388e3c; }
        .overweight { background: #ffcc80; color: #e65100; }
        .obese-1 { background: #ffab91; color: #d84315; }
        .obese-2 { background: #ff9e80; color: #bf360c; }
        .obese-3 { background: #ff7043; color: #870000; }

        .history-container {
            margin-top: 3rem;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .history-table th, 
        .history-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .history-table th {
            background: var(--secondary);
            color: white;
        }

        .history-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
    include 'header.php'
    ?>
   
        <div class="health-container">
            <div class="health-header">
                <h2><i class="fas fa-heartbeat"></i> Health Analysis</h2>
                <p>Get personalized health insights and product recommendations</p>
            </div>

            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="calculator-card">
                <div class="form-grid">
                    <div class="input-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" required>
                            <option value="male" <?= isset($_POST['gender']) && $_POST['gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= isset($_POST['gender']) && $_POST['gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-birthday-cake"></i> Age</label>
                        <input type="number" name="age" min="1" max="120" 
                               value="<?= isset($_POST['age']) ? htmlspecialchars($_POST['age']) : '' ?>" required>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-weight"></i> Weight (kg)</label>
                        <input type="number" name="weight" step="0.1" min="20" max="300" 
                               value="<?= isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : '' ?>" required>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-ruler-vertical"></i> Height (cm)</label>
                        <input type="number" name="height" min="100" max="250" 
                               value="<?= isset($_POST['height']) ? htmlspecialchars($_POST['height']) : '' ?>" required>
                    </div>
                </div>

                <button type="submit" class="calculate-btn">
                    <i class="fas fa-calculator"></i> Calculate Health Metrics
                </button>

                <?php if(isset($_SESSION['bmi_result'])): ?>
                    <?php $result = $_SESSION['bmi_result']; ?>
                    <div class="result-container">
                        <div class="result-card">
                            <h3><i class="fas fa-chart-line"></i> Your Health Report</h3>
                            <div id="bmi-value"><?= number_format($result['bmi'], 1) ?></div>
                            <div id="bmi-category" class="bmi-category <?= strtolower(str_replace(' ', '-', $result['category'])) ?>">
                                <?= $result['category'] ?>
                            </div>
                            
                            <div class="metrics-grid">
                                <div class="metric-card">
                                    <h4><i class="fas fa-percentage"></i> Body Fat Percentage</h4>
                                    <div><?= number_format($result['body_fat'], 1) ?>%</div>
                                </div>
                                <div class="metric-card">
                                    <h4><i class="fas fa-weight-hanging"></i> Ideal Weight</h4>
                                    <div><?= number_format($result['ideal_weight'], 1) ?> kg</div>
                                </div>
                            </div>
                        </div>

                        <h3><i class="fas fa-prescription-bottle-alt"></i> Recommended Products</h3>
                        <?php if(!empty($result['products'])): ?>
                            <div class="products-grid">
                                <?php foreach($result['products'] as $product): ?>
                                    <div class="product-card">
                                        <?php if($product['source'] == 'default'): ?>
                                            <span class="product-badge default-badge">Default</span>
                                        <?php endif; ?>
                                        <?php if($product['prescription_required'] == 'Yes'): ?>
                                            <span class="product-badge prescription-badge">Rx</span>
                                        <?php endif; ?>
                                        
                                        <div class="product-image">
                                            <img src="<?= $product['image_path'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        </div>
                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                            <div class="product-price">₹<?= number_format($product['price'], 2) ?></div>
                                            <?php if($product['source'] == 'product' && $product['id'] != 0): ?>
                                                <a href="product_details.php?id=<?= $product['id'] ?>" class="view-btn">
                                                    View Product
                                                </a>
                                            <?php else: ?>
                                                <button class="view-btn" onclick="alert('This is a general recommendation. Ask our pharmacists for similar products.')">
                                                    Learn More
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="recommendation-source">
                                Recommendations source: 
                                <span class="<?= $result['source'] == 'database' ? 'source-database' : 'source-default' ?>">
                                    <?= $result['source'] == 'database' ? 'Our Product Database' : 'General Health Guidelines' ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <p>No specific recommendations available. Please consult with our pharmacist.</p>
                        <?php endif; ?>
                    </div>
                    <?php unset($_SESSION['bmi_result']); ?>
                <?php endif; ?>
            </form>

            <?php if(!empty($history)): ?>
                <div class="history-container">
                    <h3><i class="fas fa-history"></i> Your Recent Health Checks</h3>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>BMI</th>
                                <th>Category</th>
                                <th>Weight</th>
                                <th>Height</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($history as $record): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($record['created_at'])) ?></td>
                                    <td><?= number_format($record['bmi'], 1) ?></td>
                                    <td>
                                        <span class="bmi-category <?= strtolower(str_replace(' ', '-', $record['category'])) ?>">
                                            <?= $record['category'] ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($record['weight'], 1) ?> kg</td>
                                    <td><?= number_format($record['height'], 1) ?> cm</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
        include 'footer.php';
        ?>
   

<script src="../assets/js/dashboard.js"></script>
<script>
    // Form Validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let valid = true;
        const inputs = document.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.style.borderColor = '#E76F51';
                valid = false;
            } else {
                input.style.borderColor = '#e0e0e0';
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill all required fields!');
        }
    });

    // Add tooltips to badges
    document.querySelectorAll('.prescription-badge').forEach(badge => {
        badge.title = "Prescription required";
    });
    document.querySelectorAll('.default-badge').forEach(badge => {
        badge.title = "General recommendation - similar products available";
    });
</script>
</body>
</html>