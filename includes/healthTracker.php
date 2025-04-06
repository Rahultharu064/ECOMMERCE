<?php
// Database connection
require_once 'config.php';

// Function to get health metrics from database
function getHealthMetrics($userId, $conn) {
    $metrics = [];
    
    // Get blood pressure data
    $bpQuery = "SELECT systolic, diastolic, reading_time FROM blood_pressure 
                WHERE user_id = ? ORDER BY reading_time DESC LIMIT 20";
    $stmt = $conn->prepare($bpQuery);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $bpResult = $stmt->get_result();
    $metrics['blood_pressure'] = $bpResult->fetch_all(MYSQLI_ASSOC);
    
    // Get medication data
    $medQuery = "SELECT 
                    (SELECT COUNT(*) FROM medication_log 
                     WHERE user_id = ? AND taken = 1 AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS taken,
                    (SELECT COUNT(*) FROM medication_log 
                     WHERE user_id = ? AND taken = 0 AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS missed,
                    next_dose_time FROM medication_schedule 
                 WHERE user_id = ? LIMIT 1";
    $stmt = $conn->prepare($medQuery);
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    $medResult = $stmt->get_result();
    $metrics['medication'] = $medResult->fetch_assoc();
    
    // Get heart rate data
    $hrQuery = "SELECT heart_rate, reading_time FROM heart_rate 
                WHERE user_id = ? ORDER BY reading_time DESC LIMIT 20";
    $stmt = $conn->prepare($hrQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $hrResult = $stmt->get_result();
    $metrics['heart_rate'] = $hrResult->fetch_all(MYSQLI_ASSOC);
    
    // Get sleep data
    $sleepQuery = "SELECT sleep_hours, sleep_quality, date FROM sleep_data 
                   WHERE user_id = ? ORDER BY date DESC LIMIT 7";
    $stmt = $conn->prepare($sleepQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $sleepResult = $stmt->get_result();
    $metrics['sleep'] = $sleepResult->fetch_all(MYSQLI_ASSOC);
    
    return $metrics;
}

// Get user ID from session (you'll need to implement your authentication)
session_start();
$userId = $_SESSION['user_id'] ?? 1; // Default to 1 if not logged in

// Get health metrics
$healthMetrics = getHealthMetrics($userId, $conn);

// Determine status indicators
function getBpStatus($systolic, $diastolic) {
    if ($systolic < 120 && $diastolic < 80) return ['status' => 'Normal', 'class' => 'status-normal'];
    if ($systolic < 130 && $diastolic < 80) return ['status' => 'Elevated', 'class' => 'status-warning'];
    if ($systolic < 140 || $diastolic < 90) return ['status' => 'High (Stage 1)', 'class' => 'status-warning'];
    return ['status' => 'High (Stage 2)', 'class' => 'status-danger'];
}

function getHrStatus($heartRate) {
    if ($heartRate >= 60 && $heartRate <= 100) return ['status' => 'Normal', 'class' => 'status-normal'];
    if ($heartRate > 100) return ['status' => 'High', 'class' => 'status-warning'];
    return ['status' => 'Low', 'class' => 'status-warning'];
}

function getSleepQuality($quality) {
    if ($quality >= 80) return ['status' => 'Excellent', 'class' => 'status-good'];
    if ($quality >= 60) return ['status' => 'Good', 'class' => 'status-normal'];
    if ($quality >= 40) return ['status' => 'Fair', 'class' => 'status-warning'];
    return ['status' => 'Poor', 'class' => 'status-danger'];
}

// Get latest readings
$latestBp = $healthMetrics['blood_pressure'][0] ?? ['systolic' => 120, 'diastolic' => 80];
$bpStatus = getBpStatus($latestBp['systolic'], $latestBp['diastolic']);

$medication = $healthMetrics['medication'] ?? ['taken' => 95, 'missed' => 5, 'next_dose_time' => '8:00 AM'];

$latestHr = $healthMetrics['heart_rate'][0] ?? ['heart_rate' => 72];
$hrStatus = getHrStatus($latestHr['heart_rate']);

$latestSleep = $healthMetrics['sleep'][0] ?? ['sleep_hours' => 7.5, 'sleep_quality' => 75];
$sleepQuality = getSleepQuality($latestSleep['sleep_quality'] ?? 75);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            background-image: linear-gradient(135deg, #f4f4f9 0%, #dfdbe5 100%);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .feature-title {
            font-size: 2.5rem;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 10px;
        }

        .feature-subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 40px;
        }

        /* Tracker Grid */
        .tracker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .tracker-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .tracker-card h3 {
            font-size: 1.5rem;
            color: #34495e;
            margin-bottom: 20px;
        }

        .tracker-card h3 i {
            margin-right: 10px;
            color: #e74c3c;
        }

        .tracker-info {
            margin-top: 20px;
        }

        .tracker-info p {
            font-size: 1rem;
            color: #555;
            margin: 10px 0;
        }

        .status-normal {
            color: #27ae60;
        }

        .status-good {
            color: #2980b9;
        }

        .status-warning {
            color: #f39c12;
        }

        .status-danger {
            color: #e74c3c;
        }

        .btn-tracker {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 10px;
        }

        .btn-tracker:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <section class="health-tracker">
        <div class="container">
            <h2 class="feature-title">Comprehensive Health Tracker</h2>
            <p class="feature-subtitle">Monitor your health metrics in real-time and stay on top of your wellness goals.</p>
            
            <div class="tracker-grid">
                <!-- Blood Pressure Tracker -->
                <div class="tracker-card">
                    <h3><i class="fas fa-heartbeat"></i> Blood Pressure</h3>
                    <canvas id="bpChart"></canvas>
                    <div class="tracker-info">
                        <p><strong>Last Reading:</strong> <?= $latestBp['systolic'] ?? 120 ?>/<?= $latestBp['diastolic'] ?? 80 ?> mmHg</p>
                        <p><strong>Status:</strong> <span class="<?= $bpStatus['class'] ?>"><?= $bpStatus['status'] ?></span></p>
                        <button class="btn-tracker">View History</button>
                    </div>
                </div>

                <!-- Medication Adherence Tracker -->
                <div class="tracker-card">
                    <h3><i class="fas fa-pills"></i> Medication Adherence</h3>
                    <canvas id="medicationChart"></canvas>
                    <div class="tracker-info">
                        <p><strong>Adherence Rate:</strong> <?= round(($medication['taken'] / ($medication['taken'] + $medication['missed'])) * 100) ?>%</p>
                        <p><strong>Next Dose:</strong> <?= $medication['next_dose_time'] ?? '8:00 AM' ?></p>
                        <button class="btn-tracker">Set Reminder</button>
                    </div>
                </div>

                <!-- Heart Rate Tracker -->
                <div class="tracker-card">
                    <h3><i class="fas fa-heart"></i> Heart Rate</h3>
                    <canvas id="heartRateChart"></canvas>
                    <div class="tracker-info">
                        <p><strong>Current Rate:</strong> <?= $latestHr['heart_rate'] ?? 72 ?> BPM</p>
                        <p><strong>Status:</strong> <span class="<?= $hrStatus['class'] ?>"><?= $hrStatus['status'] ?></span></p>
                        <button class="btn-tracker">Analyze Trends</button>
                    </div>
                </div>

                <!-- Sleep Tracker -->
                <div class="tracker-card">
                    <h3><i class="fas fa-bed"></i> Sleep Tracker</h3>
                    <canvas id="sleepChart"></canvas>
                    <div class="tracker-info">
                        <p><strong>Last Night:</strong> <?= $latestSleep['sleep_hours'] ?? 7.5 ?> Hours</p>
                        <p><strong>Quality:</strong> <span class="<?= $sleepQuality['class'] ?>"><?= $sleepQuality['status'] ?></span></p>
                        <button class="btn-tracker">View Insights</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Prepare data from PHP for JavaScript
    const bpData = <?= json_encode($healthMetrics['blood_pressure']) ?>;
    const hrData = <?= json_encode($healthMetrics['heart_rate']) ?>;
    const sleepData = <?= json_encode($healthMetrics['sleep']) ?>;
    const medData = <?= json_encode($healthMetrics['medication']) ?>;

    // Blood Pressure Chart
    const bpCtx = document.getElementById('bpChart').getContext('2d');
    const bpChart = new Chart(bpCtx, {
        type: 'line',
        data: {
            labels: bpData.map(entry => new Date(entry.reading_time).toLocaleTimeString()),
            datasets: [{
                label: 'Systolic (mmHg)',
                data: bpData.map(entry => entry.systolic),
                borderColor: '#e74c3c',
                fill: false,
            }, {
                label: 'Diastolic (mmHg)',
                data: bpData.map(entry => entry.diastolic),
                borderColor: '#3498db',
                fill: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'mmHg'
                    }
                }
            }
        }
    });

    // Medication Adherence Chart
    const medicationCtx = document.getElementById('medicationChart').getContext('2d');
    const medicationChart = new Chart(medicationCtx, {
        type: 'doughnut',
        data: {
            labels: ['Taken', 'Missed'],
            datasets: [{
                data: [medData.taken || 95, medData.missed || 5],
                backgroundColor: ['#27ae60', '#e74c3c'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Heart Rate Chart
    const heartRateCtx = document.getElementById('heartRateChart').getContext('2d');
    const heartRateChart = new Chart(heartRateCtx, {
        type: 'line',
        data: {
            labels: hrData.map(entry => new Date(entry.reading_time).toLocaleTimeString()),
            datasets: [{
                label: 'Heart Rate (BPM)',
                data: hrData.map(entry => entry.heart_rate),
                borderColor: '#e74c3c',
                fill: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'BPM'
                    }
                }
            }
        }
    });

    // Sleep Chart
    const sleepCtx = document.getElementById('sleepChart').getContext('2d');
    const sleepChart = new Chart(sleepCtx, {
        type: 'bar',
        data: {
            labels: sleepData.map(entry => new Date(entry.date).toLocaleDateString('en-US', { weekday: 'short' })),
            datasets: [{
                label: 'Sleep (Hours)',
                data: sleepData.map(entry => entry.sleep_hours),
                backgroundColor: '#3498db',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            }
        }
    });
    </script>
</body>
</html>