document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.profile__item').forEach(item => {
        const editBtn   = item.querySelector('.profile__edit');
        const saveBtn   = item.querySelector('.profile__save');
        const cancelBtn = item.querySelector('.profile__cancel');
        const inputs    = item.querySelectorAll('input');

        // When Edit is clicked: enable fields, show Save/Cancel
        editBtn.addEventListener('click', () => {
            inputs.forEach(i => i.disabled = false);
            editBtn.style.display   = 'none';
            saveBtn.style.display   = 'inline-block';
            cancelBtn.style.display = 'inline-block';
        });

        // Cancel reverts any changes and disables inputs
        cancelBtn.addEventListener('click', () => {
            inputs.forEach(i => {
                i.value    = i.defaultValue;
                i.disabled = true;
            });
            editBtn.style.display   = '';
            saveBtn.style.display   = 'none';
            cancelBtn.style.display = 'none';
        });

        // Save submits via AJAX and, on success, disables inputs
        saveBtn.addEventListener('click', () => {
            const field = item.dataset.field;
            const form  = new FormData();
            form.append('action', 'lines_auth_update_profile');
            form.append('field', field);
            form.append('nonce', linesAuth.nonce);
            inputs.forEach(i => form.append(i.name, i.value));

            fetch(linesAuth.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: form
            })
            .then(response => response.json())
            .then(json => {
                if ( json.success ) {
                    inputs.forEach(i => {
                        i.defaultValue = i.value;
                        i.disabled     = true;
                    });
                    editBtn.style.display   = '';
                    saveBtn.style.display   = 'none';
                    cancelBtn.style.display = 'none';
                } else {
                    alert(json.data);
                }
            });
        });
    });
});
