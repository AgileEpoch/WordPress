<?php
/**
 * Hàm kiểm tra sự trùng lặp của slug và tạo chuỗi ngẫu nhiên (có độ dài ngẫu nhiên, tối đa 12 ký tự) nếu cần.
 *
 * @param string $slug Slug đã được làm sạch để kiểm tra.
 * @param int $current_user_id ID người dùng hiện tại đang được cập nhật.
 * @return string Slug đã được đảm bảo là duy nhất.
 */
function custom_get_unique_author_slug( $slug, $current_user_id ) {
    $original_slug = $slug;
    $count = 0;
    
    // Nguồn ký tự và giới hạn độ dài
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $max_length = 12; 
    $min_length = 4; // Độ dài ngẫu nhiên tối thiểu

    do {
        $slug_to_check = $original_slug;
        
        if ( $count > 0 ) {
            // 1. TẠO ĐỘ DÀI NGẪU NHIÊN: Chọn độ dài từ 4 đến 12 ký tự.
            $random_length = mt_rand($min_length, $max_length); 
            
            // 2. TẠO CHUỖI NGẪU NHIÊN: Với độ dài đã chọn
            $random_suffix = substr( str_shuffle( $characters ), 0, $random_length );
            
            // Nối chuỗi ngẫu nhiên vào slug
            $slug_to_check .= '-' . $random_suffix;
        }

        // Truy vấn database để tìm người dùng đã sử dụng slug này
        $user_query = new WP_User_Query( array(
            'meta_key'   => 'ten_cong_khai',
            'meta_value' => $slug_to_check,
            'exclude'    => array( $current_user_id ),
            'number'     => 1,
            'fields'     => 'ID',
        ) );

        if ( empty( $user_query->results ) ) {
            return $slug_to_check;
        }

        $count++;
    } while ( $count < 5 );

    // Trường hợp thất bại sau 5 lần thử
    return $original_slug . '-' . time();
}


/**
 * 2.1. Lấy giá trị Nickname, xử lý trùng lặp, lưu slug, và cập nhật Display Name.
 * (PHẦN CODE NÀY KHÔNG CẦN THAY ĐỔI)
 */
function custom_process_slug_on_profile_update( $user_id ) {
    
    // Bắt sự kiện Nickname (Biệt danh) được gửi qua form
    if ( ! empty( $_POST['nickname'] ) ) {
        
        $nickname_value = sanitize_text_field( $_POST['nickname'] );
        
        // 2. Làm sạch chuỗi gốc (Ví dụ: "công ty a" -> "cong-ty-a")
        $sanitized_slug = sanitize_title( $nickname_value );
        
        // 3. Gọi hàm kiểm tra và xử lý trùng lặp để có slug duy nhất
        $unique_slug = custom_get_unique_author_slug( $sanitized_slug, $user_id );
        
        // 4. Lưu chuỗi slug duy nhất vào trường meta 'ten_cong_khai' (Custom Field)
        update_user_meta( $user_id, 'ten_cong_khai', $unique_slug );

        
        // 5. Cập nhật Display Name (Tên hiển thị công khai) để khớp với Nickname
        $user = get_user_by( 'id', $user_id );
        if ( $user->display_name !== $nickname_value ) {
            wp_update_user( array( 
                'ID'           => $user_id,
                'display_name' => $nickname_value 
            ) );
        }
    }
}
// Hook vào sự kiện khi hồ sơ người dùng được cập nhật
add_action('profile_update', 'custom_process_slug_on_profile_update', 99);
// Hook vào sự kiện khi người dùng mới được tạo
add_action('user_register', 'custom_process_slug_on_profile_update', 99);
?>
