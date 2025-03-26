document.addEventListener('DOMContentLoaded', function() {
    var signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission.
            
            var formData = new FormData(signupForm);

            // Send AJAX request using Fetch API.
            fetch(linesAuth.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                var responseDiv = document.getElementById('signup-response');
                if (data.success) {
                    responseDiv.innerHTML = '<p class="signup__success">' + data.data.message + '</p>';
                    signupForm.reset();
                } else {
                    var errorMsg = '';
                    if (data.data && data.data.errors) {
                        errorMsg = data.data.errors.join('<br>');
                    } else if (data.data && data.data.message) {
                        errorMsg = data.data.message;
                    } else {
                        errorMsg = 'An unknown error occurred.';
                    }
                    responseDiv.innerHTML = '<p class="signup__error">' + errorMsg + '</p>';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
            });
        });
    }
});
