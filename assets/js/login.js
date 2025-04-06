function validateForm() {
    let isValid = true;

    // Name validation
    const name = document.getElementById('name').value.trim();
    const nameError = document.getElementById('nameError');
    if (!name || !/^[a-zA-Z ]+$/.test(name)) {
        nameError.textContent = 'Please enter a valid name';
        nameError.style.display = 'block';
        isValid = false;
    } else {
        nameError.style.display = 'none';
    }

    // Email validation
    const email = document.getElementById('email').value.trim();
    const emailError = document.getElementById('emailError');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        emailError.textContent = 'Please enter a valid email address';
        emailError.style.display = 'block';
        isValid = false;
    } else {
        emailError.style.display = 'none';
    }

    // Password validation
    const password = document.getElementById('password').value;
    const passwordError = document.getElementById('passwordError');
    if (password.length < 8) {
        passwordError.textContent = 'Password must be at least 8 characters';
        passwordError.style.display = 'block';
        isValid = false;
    } else {
        passwordError.style.display = 'none';
    }

    // Confirm Password validation
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    if (password !== confirmPassword) {
        confirmPasswordError.textContent = 'Passwords do not match';
        confirmPasswordError.style.display = 'block';
        isValid = false;
    } else {
        confirmPasswordError.style.display = 'none';
    }

    // Phone validation
    const phone = document.getElementById('phone').value.trim();
    const phoneError = document.getElementById('phoneError');
    const phoneRegex = /^\d{10}$/;
    if (!phoneRegex.test(phone)) {
        phoneError.textContent = 'Please enter a valid 10-digit phone number';
        phoneError.style.display = 'block';
        isValid = false;
    } else {
        phoneError.style.display = 'none';
    }

    // Gender validation
    const gender = document.getElementById('gender').value;
    const genderError = document.getElementById('genderError');
    if (!gender) {
        genderError.textContent = 'Please select your gender';
        genderError.style.display = 'block';
        isValid = false;
    } else {
        genderError.style.display = 'none';
    }

    // Date of Birth validation
    const dob = document.getElementById('dob').value;
    const dobError = document.getElementById('dobError');
    if (!dob) {
        dobError.textContent = 'Please select your date of birth';
        dobError.style.display = 'block';
        isValid = false;
    } else {
        dobError.style.display = 'none';
    }

    // Role validation
    const role = document.getElementById('role').value;
    const roleError = document.getElementById('roleError');
    if (!role) {
        roleError.textContent = 'Please select your role';
        roleError.style.display = 'block';
        isValid = false;
    } else {
        roleError.style.display = 'none';
    }

    if (isValid) {
        // Submit form or send to server
        alert('Form submitted successfully!');
        // Here you would typically send the data to the server
        return false; // Prevent form submission for this example
    }
    return false;
}