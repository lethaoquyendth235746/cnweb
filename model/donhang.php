<?php
class DONHANG{
	
	// Thêm đơn hàng mới, trả về khóa của dòng mới thêm
	public function themdonhang($nguoidung_id,$diachi_id,$tongtien, $phuongthuc = 'COD', $trangthaithanhtoan = 0){
		$db = DATABASE::connect();
		try{
			$sql = "INSERT INTO donhang(nguoidung_id, diachi_id, tongtien, trangthai, phuongthucthanhtoan, trangthaithanhtoan) 
					VALUES(:nguoidung_id, :diachi_id, :tongtien, 0, :phuongthucthanhtoan, :trangthaithanhtoan)";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':nguoidung_id',$nguoidung_id);			
			$cmd->bindValue(':diachi_id',$diachi_id);
			$cmd->bindValue(':tongtien',$tongtien);
			$cmd->bindValue(':phuongthucthanhtoan',$phuongthuc);
			$cmd->bindValue(':trangthaithanhtoan',$trangthaithanhtoan, PDO::PARAM_INT);
			$cmd->execute();
			$id = $db->lastInsertId();
			return $id;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

	// Đọc ds đơn hàng của 1 khách
	public function laydanhsachdonhangtheokh($id){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM donhang WHERE nguoidung_id=:id ORDER BY ngay DESC, id DESC";
            $cmd = $dbcon->prepare($sql);
			$cmd->bindValue(":id", $id);
            $cmd->execute();
            $result = $cmd->fetchAll();
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Đọc chi tiết 1 đơn hàng theo ID
    public function laydonhangtheoid($id){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM donhang WHERE id=:id";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":id", $id);
            $cmd->execute();
            return $cmd->fetch();
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    /**
     * Đặt hàng an toàn bằng Transaction & Kiểm tra tồn kho trước khi trừ
     * Trạng thái mặc định: 0 (Chờ xác nhận)
     */
    public function datHangAnToan($nguoidung_id, $diachi_id, $tongtien, $giohang, $phuongthucthanhtoan = 'COD', $trangthaithanhtoan = 0){
        $db = DATABASE::connect();
        try {
            $db->beginTransaction();

            // 1. Kiểm tra tồn kho từng sản phẩm trong giỏ
            foreach($giohang as $id => $item){
                $soluongMua = (int)$item["soluong"];
                $stmtCheck = $db->prepare("SELECT id, tenmathang, soluongton FROM mathang WHERE id = :id FOR UPDATE");
                $stmtCheck->bindValue(":id", $id);
                $stmtCheck->execute();
                $product = $stmtCheck->fetch();

                if(!$product){
                    $db->rollBack();
                    return [
                        'success' => false,
                        'message' => "Linh kiện mã #{$id} không tồn tại trong hệ thống!"
                    ];
                }

                if((int)$product["soluongton"] < $soluongMua){
                    $db->rollBack();
                    return [
                        'success' => false,
                        'message' => "Linh kiện \"{$product['tenmathang']}\" chỉ còn {$product['soluongton']} sản phẩm trong kho, không đủ số lượng đặt mua ({$soluongMua})!"
                    ];
                }
            }

            // 2. Tạo đơn hàng với trangthai = 0 (Chờ xác nhận) kèm phương thức & trạng thái thanh toán
            $sqlOrder = "INSERT INTO donhang (nguoidung_id, diachi_id, tongtien, trangthai, ngay, phuongthucthanhtoan, trangthaithanhtoan) 
                         VALUES (:nguoidung_id, :diachi_id, :tongtien, 0, NOW(), :phuongthucthanhtoan, :trangthaithanhtoan)";
            $cmdOrder = $db->prepare($sqlOrder);
            $cmdOrder->bindValue(':nguoidung_id', $nguoidung_id);
            $cmdOrder->bindValue(':diachi_id', $diachi_id);
            $cmdOrder->bindValue(':tongtien', $tongtien);
            $cmdOrder->bindValue(':phuongthucthanhtoan', $phuongthucthanhtoan);
            $cmdOrder->bindValue(':trangthaithanhtoan', $trangthaithanhtoan, PDO::PARAM_INT);
            $cmdOrder->execute();
            $donhang_id = $db->lastInsertId();

            // 3. Thêm chi tiết đơn hàng & trừ tồn kho an toàn, tăng lượt mua
            $sqlItem = "INSERT INTO donhangct (donhang_id, mathang_id, dongia, soluong, thanhtien) 
                        VALUES (:donhang_id, :mathang_id, :dongia, :soluong, :thanhtien)";
            $cmdItem = $db->prepare($sqlItem);

            $sqlStock = "UPDATE mathang 
                         SET soluongton = soluongton - :soluong, 
                             luotmua = luotmua + :soluong 
                         WHERE id = :id";
            $cmdStock = $db->prepare($sqlStock);

            foreach($giohang as $id => $item){
                $dongia = $item["giaban"];
                $soluong = $item["soluong"];
                $thanhtien = $item["thanhtien"];

                $cmdItem->bindValue(':donhang_id', $donhang_id);
                $cmdItem->bindValue(':mathang_id', $id);
                $cmdItem->bindValue(':dongia', $dongia);
                $cmdItem->bindValue(':soluong', $soluong);
                $cmdItem->bindValue(':thanhtien', $thanhtien);
                $cmdItem->execute();

                $cmdStock->bindValue(':soluong', $soluong);
                $cmdStock->bindValue(':id', $id);
                $cmdStock->execute();
            }

            $db->commit();
            return [
                'success' => true,
                'donhang_id' => $donhang_id
            ];
        }
        catch(PDOException $e){
            if($db->inTransaction()){
                $db->rollBack();
            }
            return [
                'success' => false,
                'message' => "Lỗi xử lý giao dịch: " . $e->getMessage()
            ];
        }
    }

    /**
     * Khách hàng hủy đơn hàng (CHỈ ĐƯỢC HỦY KHI TRẠNG THÁI = 0: Chờ xác nhận)
     * Tự động hoàn lại soluongton và giảm luotmua trong Transaction
     */
    public function huyDonHangKhachHang($donhang_id, $khachhang_id){
        $db = DATABASE::connect();
        try {
            $db->beginTransaction();

            // Kiểm tra quyền sở hữu và trạng thái đơn hàng
            $stmt = $db->prepare("SELECT id, trangthai FROM donhang WHERE id = :id AND nguoidung_id = :kh_id FOR UPDATE");
            $stmt->bindValue(':id', $donhang_id);
            $stmt->bindValue(':kh_id', $khachhang_id);
            $stmt->execute();
            $order = $stmt->fetch();

            if(!$order){
                $db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn này.'
                ];
            }

            if((int)$order["trangthai"] !== 0){
                $db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Đơn hàng này đã được xác nhận hoặc đang vận chuyển, không thể hủy trực tiếp. Vui lòng liên hệ Hotline 0833 609 329 để được hỗ trợ!'
                ];
            }

            // 1. Cập nhật trạng thái đơn thành 4 (Đã hủy)
            $stmtUpdate = $db->prepare("UPDATE donhang SET trangthai = 4 WHERE id = :id");
            $stmtUpdate->bindValue(':id', $donhang_id);
            $stmtUpdate->execute();

            // 2. Lấy danh sách sản phẩm trong đơn để hoàn lại tồn kho
            $stmtItems = $db->prepare("SELECT mathang_id, soluong FROM donhangct WHERE donhang_id = :id");
            $stmtItems->bindValue(':id', $donhang_id);
            $stmtItems->execute();
            $items = $stmtItems->fetchAll();

            $stmtRestock = $db->prepare("UPDATE mathang 
                                         SET soluongton = soluongton + :soluong, 
                                             luotmua = GREATEST(0, luotmua - :soluong) 
                                         WHERE id = :id");
            foreach($items as $it){
                $stmtRestock->bindValue(':soluong', (int)$it["soluong"]);
                $stmtRestock->bindValue(':id', $it["mathang_id"]);
                $stmtRestock->execute();
            }

            $db->commit();
            return [
                'success' => true,
                'message' => "Đã hủy đơn hàng #XAG-{$donhang_id} thành công! Số lượng sản phẩm đã được hoàn trả lại kho."
            ];
        }
        catch(PDOException $e){
            if($db->inTransaction()){
                $db->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Lỗi khi hủy đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cập nhật trạng thái thanh toán (0: Chưa thanh toán, 1: Đã thanh toán)
     */
    public function capNhatTrangThaiThanhToan($donhang_id, $trangthaithanhtoan = 1){
        $db = DATABASE::connect();
        try {
            $sql = "UPDATE donhang SET trangthaithanhtoan = :tt WHERE id = :id";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(':tt', $trangthaithanhtoan, PDO::PARAM_INT);
            $cmd->bindValue(':id', $donhang_id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>