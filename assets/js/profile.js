import * as FilePond from 'filepond';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';

FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginFileValidateType,
    FilePondPluginImageEdit
);

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

    profileImageUploader();
});

function profileImageUploader() {
	const modal = document.getElementById('image-upload-modal');
	const inputWrapper = document.getElementById('profile-image-upload-wrapper');
	const openBtn = document.getElementById('open-image-modal');
	const closeBtn = document.getElementById('close-image-modal');

    let pond = null;
    let tempFileData = null; // store { tempName, tempUrl }
    
    openBtn.onclick = () => {
        modal.style.display = 'flex';
    
        setTimeout(() => {
            if (pond) pond.destroy();
    
            const inputElement = document.getElementById('profile-image-upload');
            pond = FilePond.create(inputElement, {
                labelIdle: 'Click or drag to upload a new profile photo',
                credits: false,
                allowMultiple: false,
                allowImagePreview: true,
                allowImageCrop: true,
                // from filepond-plugin-image-edit
                allowImageEdit: true,
                // also from filepond-plugin-image-resize
                allowImageResize: true,
                imageCropAspectRatio: '1:1',
                imageResizeTargetWidth: 300,
                imageResizeTargetHeight: 300,
    
                server: {
                    process: {
                        url: linesAuth.ajaxUrl,
                        method: 'POST',
                        withCredentials: true,
                        headers: { 'X-WP-Nonce': linesAuth.nonce },
                        ondata: (formData) => {
                            formData.append('action', 'lines_auth_temp_upload_image'); // STEP 1
                            return formData;
                        },
                        onload: (res) => {
                            const json = JSON.parse(res);
                            if (json.success) {
                                tempFileData = {
                                    tempName: json.data.tempName,
                                    tempUrl: json.data.tempUrl
                                };
                                // we only have a temp file so far
                                // 'Update' button will finalize it
                            } else {
                                alert(json.data || 'Temp upload failed');
                            }
                        }
                    }
                }
            });
        }, 50);
    };
    
    closeBtn.onclick = () => {
        if (pond) pond.destroy();
        modal.style.display = 'none';
    };
    
    const updateBtn = document.getElementById('update-image-button');
    updateBtn.onclick = () => {
        if (!tempFileData) {
            alert('No image uploaded yet.');
            return;
        }
        // STEP 2: finalize
        const formData = new FormData();
        formData.append('action', 'lines_auth_finalize_upload');
        formData.append('nonce', linesAuth.nonce);
        formData.append('field', 'profileImage');
        formData.append('tempName', tempFileData.tempName);
    
        fetch(linesAuth.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(json => {
            if (json.success) {
                document.getElementById('profile-image-preview').src = json.data.url;
                if (pond) pond.destroy();
                modal.style.display = 'none';
            } else {
                alert(json.data || 'Finalize failed');
            }
        });
    };
    
}