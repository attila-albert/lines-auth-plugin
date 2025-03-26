<?php
/**
 * Profile View for Lines Auth Plugin.
 *
 * Displays and allows editing of the current user's profile.
 *
 * @package LinesAuthPlugin
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>

<div class="profile">
    <h1 class="profile__title">Your Profile</h1>

    <!-- Avatar Preview and Upload -->
    <div class="profile__image-section">
        <img id="profile-image-preview" src="<?php echo esc_url( $current_user->profileImage ?: plugin_dir_url(__FILE__) . '../assets/img/default-avatar.svg' ); ?>" alt="Profile Photo">
        <button id="open-image-modal" class="profile__upload-btn">Click to upload</button>
    </div>

    <!-- Modal -->
    <div id="image-upload-modal" class="modal">
        <div class="modal__content">
            <div id="profile-image-upload-wrapper">
                <input type="file" id="profile-image-upload" name="filepond" />
            </div>
            <button id="update-image-button" class="modal__save">Update</button>
            <button id="close-image-modal" class="modal__cancel">Cancel</button>
        </div>
    </div>

    <?php $fields = ['username' => 'Username','name' => 'Full Name','email' => 'Email','birth' => 'Birth Date']; ?>
    <?php foreach ( $fields as $key => $label ) : ?>
        <div class="profile__item" data-field="<?php echo esc_attr( $key ); ?>">
            <label class="profile__label"><?php echo esc_html( $label ); ?></label>
            <input 
                id="profile-<?php echo esc_attr( $key ); ?>" 
                class="profile__input" 
                type="<?php echo $key === 'birth' ? 'date' : ($key === 'email' ? 'email' : 'text'); ?>" 
                name="value"
                value="<?php echo esc_attr( $current_user->$key ); ?>" 
                disabled
            />
            <button class="profile__edit" data-field="<?php echo esc_attr( $key ); ?>">✎</button>
            <button class="profile__save" style="display:none;">Save</button>
            <button class="profile__cancel" style="display:none;">Cancel</button>
        </div>
    <?php endforeach; ?>

    <div class="profile__item profile__password" data-field="password">
        <label class="profile__label">Change Password</label>
        <input type="password" name="old_password" placeholder="Old Password" disabled>
        <input type="password" name="new_password" placeholder="New Password" disabled>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" disabled>
        <button class="profile__edit" data-field="password">✎</button>
        <button class="profile__save" style="display:none;">Save</button>
        <button class="profile__cancel" style="display:none;">Cancel</button>
    </div>

    <div class="profile__actions">
        <a href="<?php echo esc_url( home_url( '/logout' ) ); ?>" class="profile__logout-button">Logout</a>
    </div>
</div>

<?php
get_footer();
