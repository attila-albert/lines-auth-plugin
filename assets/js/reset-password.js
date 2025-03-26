document.getElementById('reset-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = new FormData(this);
    form.append('nonce', linesAuth.nonce);

    fetch(linesAuth.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: form
    })
    .then(r => r.json())
    .then(json => {
        const msg = document.getElementById('reset-message');
        if ( json.success ) {
            msg.textContent = json.data;
            msg.className = 'reset-password__message success';
            // redirect to login
            window.location.href = linesAuth.redirectUrl;
        } else {
            msg.textContent = json.data;
            msg.className = 'reset-password__message error';
        }
    });
    
});
