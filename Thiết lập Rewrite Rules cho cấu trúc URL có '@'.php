<?php 
// ----------------------------------------------------
// 3.1. Thiết lập Rewrite Rules cho cấu trúc URL có '@'
// ----------------------------------------------------
function custom_add_author_rewrite_rule() {
    global $custom_author_base;
    
    // Quy tắc: /author/@slug -> index.php?author_name=slug (slug là giá trị ten_cong_khai)
    add_rewrite_rule(
        '^' . $custom_author_base . '/@([^/]+)/?$',
        'index.php?author_name=$matches[1]',
        'top'
    );
}
add_action( 'init', 'custom_add_author_rewrite_rule' );

// ----------------------------------------------------
// 3.2. Lọc truy vấn (request) để WordPress tìm tác giả bằng Custom Field
// ----------------------------------------------------
function custom_filter_author_request( $query_vars ) {
    
    // Chỉ hoạt động nếu tham số author_name tồn tại (từ Rewrite Rule)
    if ( ! empty( $query_vars['author_name'] ) ) {
        
        // 1. Lấy slug từ URL (ví dụ: cong-ty-a hoặc cong-ty-a-random)
        $slug_from_url = str_replace( '@', '', $query_vars['author_name'] );
        
        // 2. Tìm kiếm User ID dựa trên Custom Field Meta Key 'ten_cong_khai'
        $user_query = new WP_User_Query( array(
            'meta_key' => CUSTOM_AUTHOR_SLUG_META_KEY,
            'meta_value' => $slug_from_url, 
            'number' => 1,
            'fields' => 'ID',
        ) );

        // 3. Nếu tìm thấy người dùng
        if ( ! empty( $user_query->results ) ) {
            $user_id = $user_query->results[0];
            
            // 4. Thay đổi tham số truy vấn: Xóa 'author_name' và thêm 'author' bằng ID
            unset( $query_vars['author_name'] );
            $query_vars['author'] = $user_id;
        }
    }

    return $query_vars;
}
add_filter( 'request', 'custom_filter_author_request' );
?>
