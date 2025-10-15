<?php
/**
 * Định nghĩa các hằng số cho chức năng avatar tùy chỉnh.
 * Đặt các hằng số này ở đầu file functions.php của Child Theme hoặc plugin của bạn.
 */
define( 'MY_CUSTOM_AVATAR_META_KEY', 'avatar' ); // Khóa meta để lưu ID ảnh avatar của người dùng
define( 'MY_CUSTOM_DEFAULT_AVATAR_FILENAME', '/images/default-avatar.png' ); // Đường dẫn tương đối đến ảnh avatar mặc định trong Child Theme

/**
 * Xử lý thứ tự ưu tiên Avatar tùy chỉnh.
 *
 * Hàm này hook vào 'pre_get_avatar_data' để can thiệp vào quá trình lấy dữ liệu avatar.
 * Thứ tự ưu tiên: Avatar tự tải lên > Avatar mạng xã hội > Avatar mặc định tùy chỉnh.
 *
 * @param array $args Dữ liệu avatar hiện có (URL, kích thước, v.v.).
 * @param mixed $id_or_email ID người dùng, email, hoặc đối tượng comment.
 * @return array Các đối số avatar đã sửa đổi.
 */
function my_custom_avatar_priority( $args, $id_or_email ) {
    $user_id = 0;

    // 1. Xác định ID người dùng từ $id_or_email
    if ( is_numeric( $id_or_email ) ) {
        $user_id = $id_or_email;
    } elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ) {
        $user_id = $id_or_email->user_id;
    } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        if ( $user ) {
            $user_id = $user->ID;
        }
    }

    // --- Ưu tiên 1: Avatar người dùng tự tải lên (qua ID ảnh trong user meta) ---
    if ( $user_id ) {
        // Lấy ID ảnh đính kèm từ user meta
        $avatar_attachment_id = get_user_meta( $user_id, MY_CUSTOM_AVATAR_META_KEY, true );

        if ( ! empty( $avatar_attachment_id ) ) {
            // Lấy URL của ảnh từ ID đính kèm. Bạn có thể thay 'full' bằng các kích thước khác
            // như 'thumbnail', 'medium', 'large' hoặc một kích thước tùy chỉnh đã đăng ký.
            $custom_avatar_array = wp_get_attachment_image_src( $avatar_attachment_id, 'full' );

            if ( $custom_avatar_array && is_array( $custom_avatar_array ) ) {
                $args['url'] = $custom_avatar_array[0]; // Gán URL của avatar tùy chỉnh
                $args['found_avatar'] = true; // Đánh dấu rằng đã tìm thấy avatar
                return $args; // Trả về ngay lập tức để ưu tiên cao nhất
            }
        }
    }

    // --- Ưu tiên 2: Avatar mạng xã hội hoặc Gravatar ---
    // Các plugin đăng nhập mạng xã hội hoặc Gravatar sẽ tự động xử lý và điền URL avatar
    // vào $args['url'] nếu ưu tiên 1 không được tìm thấy.
    // Chúng ta không cần thêm mã cụ thể ở đây.
    // Logic của WordPress sẽ tiếp tục chạy sau hàm này (vì filter này có độ ưu tiên 99),
    // cho phép các nguồn avatar khác (như Gravatar hoặc plugin xã hội) có cơ hội điền vào.

    // --- Ưu tiên 3: Avatar mặc định tùy chỉnh của WordPress ---
    // Chỉ áp dụng avatar mặc định nếu không có avatar nào được tìm thấy cho đến thời điểm này.
    // Tức là, $args['found_avatar'] vẫn là false và $args['url'] vẫn trống.
    if ( ! $args['found_avatar'] && empty( $args['url'] ) ) {
        $custom_default_avatar_url = get_stylesheet_directory_uri() . MY_CUSTOM_DEFAULT_AVATAR_FILENAME;

        // Tùy chọn: Kiểm tra xem file ảnh mặc định có tồn tại trên server hay không
        // Điều này giúp ngăn ngừa việc hiển thị ảnh hỏng nếu file không có.
        if ( file_exists( get_stylesheet_directory() . MY_CUSTOM_DEFAULT_AVATAR_FILENAME ) ) {
             $args['url'] = $custom_default_avatar_url;
             $args['found_avatar'] = true; // Báo hiệu đã tìm thấy avatar (mặc định)
        }
    }

    return $args;
}
// Hook vào 'pre_get_avatar_data' với độ ưu tiên cao (99) để chạy sau các plugin khác
add_filter( 'pre_get_avatar_data', 'my_custom_avatar_priority', 99, 2 );

/**
 * Thêm avatar mặc định tùy chỉnh vào danh sách lựa chọn trong Cài đặt > Thảo luận.
 *
 * Hàm này giúp quản trị viên có thể chọn ảnh avatar mặc định của bạn từ giao diện WordPress.
 *
 * @param array $avatar_defaults Danh sách các URL avatar mặc định hiện có.
 * @return array Danh sách avatar mặc định đã sửa đổi.
 */
function add_my_custom_default_avatar_option( $avatar_defaults ) {
    $my_avatar_url = get_stylesheet_directory_uri() . MY_CUSTOM_DEFAULT_AVATAR_FILENAME;

    // Chỉ thêm vào danh sách nếu file ảnh mặc định thực sự tồn tại
    if ( file_exists( get_stylesheet_directory() . MY_CUSTOM_DEFAULT_AVATAR_FILENAME ) ) {
        $avatar_defaults[$my_avatar_url] = "Avatar Mặc định Của Tôi";
    }
    return $avatar_defaults;
}
// Hook vào 'avatar_defaults' để thêm tùy chọn vào bảng điều khiển WordPress
add_filter( 'avatar_defaults', 'add_my_custom_default_avatar_option' );
?>
