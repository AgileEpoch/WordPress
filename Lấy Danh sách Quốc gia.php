<?php
/**
 * Lấy danh sách quốc gia, ưu tiên sử dụng danh sách chuẩn từ WooCommerce.
 *
 * @return array Mảng quốc gia (Code => Name)
 */
function custom_get_country_list() {
    // 1. Kiểm tra nếu WooCommerce đã được kích hoạt
    if ( class_exists( 'WooCommerce' ) ) {
        // Sử dụng danh sách quốc gia chuẩn của WooCommerce
        $countries_obj = new WC_Countries();
        return $countries_obj->get_countries();
    }
    
    // 2. Nếu không có WooCommerce, trả về danh sách rút gọn mặc định
    return array(
        'VN' => 'Việt Nam',
        'US' => 'Hoa Kỳ',
        'CA' => 'Canada',
        'AU' => 'Úc',
        'GB' => 'Vương quốc Anh',
        'FR' => 'Pháp',
        'DE' => 'Đức',
        'JP' => 'Nhật Bản',
        // Thêm các quốc gia khác nếu cần
    );
}

/**
 * 1. Hàm callback cho Dynamic Tag: Trả về chuỗi danh sách quốc gia theo định dạng Bricks (VALUE|LABEL)
 * @return string Chuỗi các tùy chọn được phân tách bằng dòng mới.
 */
function custom_get_countries_for_bricks_tag() {
    $countries = custom_get_country_list(); 
    $options = [];
    
    // Thêm tùy chọn mặc định (người dùng sẽ thấy tùy chọn này nếu chưa chọn)
    $options[] = '|— Chọn Quốc gia —'; 
    
    foreach ( $countries as $code => $name ) {
        // Định dạng theo yêu cầu của Bricks: VALUE|LABEL
        $options[] = $code . '|' . $name;
    }
    
    // Trả về chuỗi được phân tách bằng dòng mới (dùng \n)
    return implode( "\n", $options );
}


/**
 * 2. Đăng ký Custom Dynamic Data Tag mới trong Bricks.
 * Tag: {custom_countries}
 */
add_filter( 'bricks/dynamic_data/data', function( $data ) {
    // Đặt tên Tag của bạn là 'custom_countries'
    $data['custom_countries_quoc_gia'] = array(
        'label' => esc_html__( 'Danh Sách Quốc Gia Tùy Chỉnh', 'bricks' ),
        'callback' => 'custom_get_countries_for_bricks_tag',
        'group' => 'custom', // Nhóm tùy chỉnh
    );
    return $data;
});
?>
