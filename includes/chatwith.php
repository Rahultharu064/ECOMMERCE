<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat with Doctors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
        /* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.live-chat-container {
    display: flex;
    background: #fff;
    margin-top:330px;
    border-radius: 20px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    max-width: 1200px;
    width: 90%;
    animation: fadeIn 1.5s ease-in-out;
}

.live-chat-form {
    flex: 1;
    padding: 2rem;
}

.available-doctors {
    flex: 1;
    padding: 2rem;
    background: linear-gradient(135deg, #f9f9f9, #e6f0ff);
}

h2, h3 {
    color: #2065d1;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #555;
}

select, textarea, input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

select:focus, textarea:focus, input[type="file"]:focus {
    border-color: #2065d1;
    outline: none;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-label input {
    margin-right: 10px;
}

.checkmark {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #2065d1;
    border-radius: 4px;
    margin-right: 10px;
    position: relative;
}

.checkbox-label input:checked + .checkmark::after {
    content: '✔';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #2065d1;
}

.submit-button {
    background-color: #2065d1;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.submit-button:hover {
    background-color: #1650a8;
}

.doctors-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.doctor-card {
    background: #fff;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.doctor-card:hover {
    transform: translateY(-5px);
}

.doctor-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #2065d1;
}

.doctor-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.doctor-info h4 {
    margin: 0;
    color: #2065d1;
}

.doctor-info p {
    margin: 0.5rem 0;
    color: #555;
}

.status {
    font-weight: bold;
}

.status.online {
    color: #27ae60;
}

.status.busy {
    color: #e74c3c;
}

.wait-time {
    font-size: 0.9rem;
    color: #777;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
    </style>
</head>
<body>
    <?php
    include "../includes/header.php";
    ?>
    <section class="live-chat-container">
        <div class="live-chat-form">
            <h2>Live Chat with Doctors</h2>
            <form id="consultationForm">
                <!-- Reason for Consultation -->
                <div class="form-group">
                    <label for="reason">Reason for Consultation</label>
                    <select id="reason" name="reason" required>
                        <option value="">Select a reason</option>
                        <option value="cold">Cold</option>
                        <option value="fever">Fever</option>
                        <option value="skin-issues">Skin Issues</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Symptoms Description -->
                <div class="form-group">
                    <label for="symptoms">Symptoms or Brief Description</label>
                    <textarea id="symptoms" name="symptoms" rows="4" placeholder="Describe your symptoms..." required></textarea>
                </div>

                <!-- Upload Medical Documents -->
                <div class="form-group">
                    <label for="documents">Upload Medical Documents (Optional)</label>
                    <input type="file" id="documents" name="documents">
                </div>

                <!-- Emergency Checkbox -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="emergency" name="emergency">
                        <span class="checkmark"></span>
                        This is an emergency
                    </label>
                </div>

                <!-- Choose Estimated Wait Time -->
                <div class="form-group">
                    <label for="waitTime">Choose Estimated Wait Time</label>
                    <select id="waitTime" name="waitTime" required>
                        <option value="">Select wait time</option>
                        <option value="2">2 minutes</option>
                        <option value="5">5 minutes</option>
                        <option value="10">10 minutes</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-button">Proceed to Chat</button>
            </form>
        </div>

        <!-- Available Doctors Section -->
        <div class="available-doctors">
            <h3>Available Doctors</h3>
            <div class="doctors-list">
                <!-- General Physician -->
                <div class="doctor-card online">
                    <div class="doctor-image">
                        <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80" alt="Dr. John Doe">
                    </div>
                    <div class="doctor-info">
                        <h4>Dr. John Doe</h4>
                        <p>General Physician</p>
                        <p class="status">Online Now</p>
                    </div>
                    <p class="wait-time">Estimated Wait Time: 2 minutes</p>
                </div>

                <!-- Dermatologist -->
                <div class="doctor-card busy">
                    <div class="doctor-image">
                        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80" alt="Dr. Jane Smith">
                    </div>
                    <div class="doctor-info">
                        <h4>Dr. Jane Smith</h4>
                        <p>Dermatologist</p>
                        <p class="status">Busy</p>
                    </div>
                    <p class="wait-time">Estimated Wait Time: 5 minutes</p>
                </div>

                <!-- Pediatrician -->
                <div class="doctor-card online">
                    <div class="doctor-image">
                        <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?ixlib=rb-1.2.1&auto=format&fit=crop&w=100&h=100&q=80" alt="Dr. Emily Brown">
                    </div>
                    <div class="doctor-info">
                        <h4>Dr. Emily Brown</h4>
                        <p>Pediatrician</p>
                        <p class="status">Online Now</p>
                    </div>
                    <p class="wait-time">Estimated Wait Time: 1 minute</p>
                </div>
            </div>
        </div>
    </section>
    

    <script src="script.js">
        // Handle Form Submission
document.getElementById('consultationForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const reason = document.getElementById('reason').value;
    const symptoms = document.getElementById('symptoms').value;
    const isEmergency = document.getElementById('emergency').checked;
    const waitTime = document.getElementById('waitTime').value;

    if (!reason || !symptoms || !waitTime) {
        alert('Please fill in all required fields.');
        return;
    }

    alert(`Consultation request submitted!\nReason: ${reason}\nSymptoms: ${symptoms}\nEmergency: ${isEmergency ? 'Yes' : 'No'}\nWait Time: ${waitTime} minutes`);
});
    </script>
</body>
</html>