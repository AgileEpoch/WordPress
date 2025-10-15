<?php 
function restrict_non_admin_access() {
    // 1. Kiểm tra xem người dùng có đang ở trang quản trị (admin area) hay không
    // 2. Kiểm tra xem người dùng KHÔNG PHẢI là Quản trị viên (administrator) hay không
    // 3. Đảm bảo người dùng KHÔNG ở trang wp-login.php hoặc admin-ajax.php
    if ( is_admin() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && 'wp-login.php' != basename( $_SERVER['PHP_SELF'] ) ) {

        // Lấy URL trang chủ
        $redirect_url = home_url();

        // Chuyển hướng người dùng
        wp_redirect( $redirect_url );
        exit; // Dừng việc thực thi mã sau khi chuyển hướng
    }
}
// Móc hàm vào hành động 'admin_init'
add_action( 'admin_init', 'restrict_non_admin_access' );
?>
