-- Bảng carts lưu thông tin giỏ hàng của user
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(10),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    -- Có thể bổ sung thêm các trường khác nếu cần
);

-- Tạo chỉ mục cho user_id để truy vấn nhanh hơn
CREATE INDEX idx_user_id ON carts(user_id);
