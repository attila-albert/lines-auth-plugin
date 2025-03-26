// Define your function as usual.
function openGoogleOAuth() {
    var width = 600,
        height = 600;
    var left = (screen.width / 2) - (width / 2);
    var top = (screen.height / 2) - (height / 2);
    window.open(
        window.location.origin + '/oauth/google',
        'GoogleOAuth',
        'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left + ',resizable=yes,scrollbars=yes'
    );
}

// Attach the function to the global window object.
window.openGoogleOAuth = openGoogleOAuth;