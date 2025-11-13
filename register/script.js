


    // Password validation function
    function validatePassword(password) {
        // At least 8 characters, one uppercase, one lowercase, one number, one special character
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        return regex.test(password);
    }

    // Signup Form Submission with password validation
    if (signupForm) {
        signupForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Get password and confirm password values
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('signupConfirmPassword').value;

            // Password validation
            if (!validatePassword(password)) {
                signupMessageDiv.textContent = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
                signupMessageDiv.classList.remove('success');
                signupMessageDiv.classList.add('error');
                return;
            }

            // Confirm password match
            if (password !== confirmPassword) {
                signupMessageDiv.textContent = 'Passwords do not match.';
                signupMessageDiv.classList.remove('success');
                signupMessageDiv.classList.add('error');
                return;
            }

            // Continue with AJAX submission as before
            const formData = new FormData(signupForm);
            const action = signupForm.getAttribute('action');
            const submitButton = signupForm.querySelector('button[type="submit"]');

            signupMessageDiv.textContent = 'Registering...';
            signupMessageDiv.classList.remove('success', 'error');
            signupMessageDiv.style.color = 'blue';
            if (submitButton) submitButton.disabled = true;

            try {
                const response = await fetch(action, {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("HTTP Error Status:", response.status);
                    console.error("HTTP Error Response Body:", errorText);
                    throw new Error(`Server responded with status ${response.status}. Check network tab.`);
                }

                const result = await response.json();
                console.log("Signup server response:", result);

                if (result.success) {
                    signupMessageDiv.textContent = result.message;
                    signupMessageDiv.classList.remove('error');
                    signupMessageDiv.classList.add('success');
                    signupForm.reset();
                    setTimeout(() => {
                        closeModal();
                        openModal(loginModal, 'Login');
                        loginMessageDiv.textContent = "Registration successful! Please log in.";
                        loginMessageDiv.classList.add('success');
                    }, 1500);

                } else {
                    signupMessageDiv.textContent = result.message;
                    signupMessageDiv.classList.remove('success');
                    signupMessageDiv.classList.add('error');
                }

            } catch (error) {
                console.error('Error during registration:', error);
                signupMessageDiv.textContent = 'An unexpected error occurred. Please try again.';
                signupMessageDiv.classList.remove('success');
                signupMessageDiv.classList.add('error');
            } finally {
                if (submitButton) submitButton.disabled = false;
                signupMessageDiv.style.color = '';
            }
        });
    }

