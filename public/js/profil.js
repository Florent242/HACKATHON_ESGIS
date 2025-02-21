document.addEventListener('DOMContentLoaded', () => {
    const profileForm = document.getElementById('profileForm');
    
    // Handle form submission
    profileForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Collect form data
        const formData = {
            fullName: document.getElementById('fullName').value,
            email: document.getElementById('email').value,
            specialization: document.getElementById('specialization').value,
            github: document.getElementById('github').value,
            bio: document.getElementById('bio').value
        };

        // Simulate API call
        console.log('Saving profile data:', formData);
        
        // Show success message
        const saveBtn = document.querySelector('.save-btn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Modifications enregistrées !';
        saveBtn.style.backgroundColor = '#059669'; // Success green color
        
        // Reset button after 2 seconds
        setTimeout(() => {
            saveBtn.textContent = originalText;
            saveBtn.style.backgroundColor = '#3B82F6';
        }, 2000);
    });

    // Add input validation
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            if (input.value.trim() === '') {
                input.style.borderColor = '#EF4444'; // Error red color
            } else {
                input.style.borderColor = '#1E2028'; // Normal border color
            }
        });
    });
});