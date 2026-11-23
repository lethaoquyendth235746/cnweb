<?php
class KHACHHANG{
	
	// Thêm khách hàng mới, trả về khóa của dòng mới thêm
	public function themkhachhang($email,$sodt,$hoten){
		$db = DATABASE::connect();
		try{
			$sql = "INSERT INTO nguoidung(email,matkhau,sodienthoai,hoten,loai,trangthai) VALUES(:email,:matkhau,:sodt,:hoten,3,1)";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':email',$email);
			$cmd->bindValue(':matkhau',password_hash($sodt, PASSWORD_BCRYPT));
			$cmd->bindValue(':sodt',$sodt);
			$cmd->bindValue(':hoten',$hoten);			
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

    // Đăng ký khách hàng mới đầy đủ (băm mật khẩu BCRYPT, hỗ trợ địa chỉ mặc định)
    public function dangkyKhachHang($hoten, $email, $sodt, $matkhau, $diachi = null){
        $db = DATABASE::connect();
        try{
            $db->beginTransaction();

            $sql = "INSERT INTO nguoidung(email, matkhau, sodienthoai, hoten, loai, trangthai) 
                    VALUES(:email, :matkhau, :sodt, :hoten, 3, 1)";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(':email', $email);
            $cmd->bindValue(':matkhau', password_hash($matkhau, PASSWORD_BCRYPT));
            $cmd->bindValue(':sodt', $sodt);
            $cmd->bindValue(':hoten', $hoten);
            $cmd->execute();
            $userId = (int)$db->lastInsertId();

            if(!empty($diachi)){
                $sqlDc = "INSERT INTO diachi(nguoidung_id, diachi, macdinh) VALUES(:uid, :dc, 1)";
                $cmdDc = $db->prepare($sqlDc);
                $cmdDc->bindValue(':uid', $userId);
                $cmdDc->bindValue(':dc', $diachi);
                $cmdDc->execute();
            }

            $db->commit();
            return $userId;
        }
        catch(PDOException $e){
            if($db->inTransaction()){
                $db->rollBack();
            }
            return false;
        }
    }

    // Kiểm tra email đã được đăng ký hay chưa
    public function kiemtraEmailTonTai($email){
        $db = DATABASE::connect();
        try{
            $sql = "SELECT COUNT(*) FROM nguoidung WHERE email = :email";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(':email', $email);
            $cmd->execute();
            return ((int)$cmd->fetchColumn()) > 0;
        }
        catch(PDOException $e){
            return false;
        }
    }

    // Kiểm tra đăng nhập hỗ trợ cả BCRYPT mới và MD5/plaintext cũ
	public function kiemtrakhachhanghople($email,$matkhau){
		$db = DATABASE::connect();
		try{
			$sql = "SELECT * FROM nguoidung WHERE email=:email AND trangthai=1 AND loai=3";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(":email", $email);
			$cmd->execute();
			$user = $cmd->fetch();
			if(!$user){
                return false;
            }

            $stored = $user["matkhau"];

            // 1. Kiểm tra BCRYPT chuẩn
            if(password_verify($matkhau, $stored)){
                return true;
            }

            // 2. Kiểm tra MD5 cũ và tự động nâng cấp sang BCRYPT
            if(md5($matkhau) === $stored){
                $newHash = password_hash($matkhau, PASSWORD_BCRYPT);
                $upd = $db->prepare("UPDATE nguoidung SET matkhau = :mk WHERE id = :id");
                $upd->execute([':mk' => $newHash, ':id' => $user['id']]);
                return true;
            }

            // 3. Kiểm tra plaintext cũ (nếu có)
            if($matkhau === $stored){
                $newHash = password_hash($matkhau, PASSWORD_BCRYPT);
                $upd = $db->prepare("UPDATE nguoidung SET matkhau = :mk WHERE id = :id");
                $upd->execute([':mk' => $newHash, ':id' => $user['id']]);
                return true;
            }

			return false;			
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}
	
	// lấy thông tin người dùng có $email
	public function laythongtinkhachhang($email){
		$db = DATABASE::connect();
		try{
			$sql = "SELECT * FROM nguoidung WHERE email=:email AND loai=3";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(":email", $email);
			$cmd->execute();
			$ketqua = $cmd->fetch();
			$cmd->closeCursor();
			return $ketqua;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

}
?>
