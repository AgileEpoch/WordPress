<?php
/**
 * Tùy chỉnh Author Slug bằng Custom Field và thêm ký tự '@' vào URL.
 * Đảm bảo key này khớp với trường được tạo tự động (Module 2).
 */
define( 'CUSTOM_AUTHOR_SLUG_META_KEY', 'ten_cong_khai' );

// Lấy Author Base một lần để tối ưu hiệu suất
global $custom_author_base;
$custom_author_base = get_option('author_base', 'author');

// ----------------------------------------------------
// 1. Lọc liên kết tác giả (author_link) để chèn slug tùy chỉnh và ký tự '@'
// ----------------------------------------------------
function custom_change_author_link_for_bricks( $link, $author_id, $author_nicename ) {
    global $custom_author_base;
    
    // Lấy slug đã được làm sạch và xử lý trùng lặp từ trường 'ten_cong_khai'
    $custom_slug = get_the_author_meta( CUSTOM_AUTHOR_SLUG_META_KEY, $author_id );
    
    if ( $custom_slug ) {
        // Tạo slug mới có '@'
        $new_slug = '@' . $custom_slug;

        // Chuỗi cần thay thế: /author/admin
        $old_url_part = '/' . $custom_author_base . '/' . $author_nicename;
        
        // Chuỗi mới: /author/@ten-cong-khai
        $new_url_part = '/' . $custom_author_base . '/' . $new_slug;
        
        // Thực hiện thay thế trong đường dẫn
        $link = str_replace( $old_url_part, $new_url_part, $link );
    }

    return $link;
}
add_filter( 'author_link', 'custom_change_author_link_for_bricks', 9, 3 );
?>
