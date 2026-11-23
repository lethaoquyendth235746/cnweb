<?php
class NGUOIDUNG{
	private $id;
    private $email;
    private $sodienthoai;
    private $matkhau;
    private $hoten;
    private $trangthai;
    private $loai;
    private $hinhanh;    

    public function getid(){ return $this->id; }
    public function setid($value){ $this->id = $value; }
    public function getemail(){ return $this->email; }
    public function setemail($value){ $this->email = $value; }
    public function getsodienthoai(){ return $this->sodienthoai; }
    public function setsodienthoai($value){ $this->sodienthoai = $value; }
    public function getmatkhau(){ return $this->matkhau; }
    public function setmatkhau($value){ $this->matkhau = $value; }
    public function gethoten(){ return $this->hoten; }
    public function sethoten($value){ $this->hoten = $value; }
    public function gettrangthai(){ return $this->trangthai; }
    public function settrangthai($value){ $this->trangthai = $value; }
    public function getloai(){ return $this->loai; }
    public function setloai($value){ $this->loai = $value; }
    public function gethinhanh(){ return $this->hinhanh; }
    public function sethinhanh($value){ $this->hinhanh = $value; }
    
	
	public function kiemtranguoidunghople($email,$matkhau){
		$db = DATABASE::connect();
		try{
			$sql = "SELECT * FROM nguoidung WHERE email=:email AND matkhau=:matkhau AND trangthai=1";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(":email", $email);
			$cmd->bindValue(":matkhau", md5($matkhau));
			$cmd->execute();
			$valid = ($cmd->rowCount () == 1);
			$cmd->closeCursor ();
			return $valid;			
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}
	
	// lấy thông tin người dùng có $email
	public function laythongtinnguoidung($email){
		$db = DATABASE::connect();
		try{
			$sql = "SELECT * FROM nguoidung WHERE email=:email";
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

	
	// lấy tất cả ng dùng
	public function laydanhsachnguoidung(){
		$db = DATABASE::connect();
		try{
			$sql = "SELECT * FROM nguoidung";
			$cmd = $db->prepare($sql);			
			$cmd->execute();
			$ketqua = $cmd->fetchAll();			
			return $ketqua;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

	// Thêm nd mới, trả về khóa của dòng mới thêm
	public function themnguoidung($nguoidung){
		$db = DATABASE::connect();
		try{
			$sql = "INSERT INTO nguoidung(email,matkhau,sodienthoai,hoten,loai) VALUES(:email,:matkhau,:sodt,:hoten,:loai)";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':email',$nguoidung->email);
			$cmd->bindValue(':matkhau',md5($nguoidung->matkhau));
			$cmd->bindValue(':sodt',$nguoidung->sodienthoai);
			$cmd->bindValue(':hoten',$nguoidung->hoten);
			$cmd->bindValue(':loai',$nguoidung->loai);
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

	// Cập nhật thông tin ng dùng (tham số: id, họ tên, số đt, email, ảnh đại diện )
	public function capnhatnguoidung($nguoidung){
		$db = DATABASE::connect();
		try{
			$sql = "UPDATE nguoidung set hoten=:hoten, email=:email, sodienthoai=:sodt, hinhanh=:hinhanh where id=:id";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':id',$nguoidung->id);
			$cmd->bindValue(':email',$nguoidung->email);
			$cmd->bindValue(':sodt',$nguoidung->sodienthoai);
			$cmd->bindValue(':hoten',$nguoidung->hoten);
			$cmd->bindValue(':hinhanh',$nguoidung->hinhanh);
			$ketqua = $cmd->execute();            
            return $ketqua;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

	// Đổi mật khẩu (tham số: email, mật khẩu)
	public function doimatkhau($nguoidung){
		$db = DATABASE::connect();
		try{
			$sql = "UPDATE nguoidung set matkhau=:matkhau where email=:email";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':email',$nguoidung->email);
			$cmd->bindValue(':matkhau',md5($nguoidung->matkhau));
			$ketqua = $cmd->execute();            
            return $ketqua;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

	// Đổi quyền (tham số: email, loại người dùng)
	// loại người dùng: 1 - quản trị, 2 - nhân viên. Không cần nâng cấp quyền đối với loại người dùng 3 - khách hàng
	public function doiloainguoidung($nguoidung){
		$db = DATABASE::connect();
		try{
			$sql = "UPDATE nguoidung set loai=:loai where email=:email";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':email',$nguoidung->email);
			$cmd->bindValue(':loai',$nguoidung->loai);
			$ketqua = $cmd->execute();            
            return $ketqua;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

	// Đổi trạng thái (tham số: id, trạng thái)
	// trạng thái: 0 khóa, 1 kích hoạt)		
	public function doitrangthai($id,$trangthai){
		$db = DATABASE::connect();
		try{
			$sql = "UPDATE nguoidung set trangthai=:trangthai where id=:id";
			$cmd = $db->prepare($sql);
			$cmd->bindValue(':id',$id);
			$cmd->bindValue(':trangthai',$trangthai);
			$ketqua = $cmd->execute();            
            return $ketqua;
		}
		catch(PDOException $e){
			$error_message=$e->getMessage();
			echo "<p>Lỗi truy vấn: $error_message</p>";
			exit();
		}
	}

    /**
     * Phương thức xác thực đăng nhập chuẩn dùng chung (Khách hàng & Quản trị)
     * Kiểm tra trạng thái hoạt động (trangthai == 1) và vai trò được phép (allowedRoles)
     * Hỗ trợ Bcrypt và tương thích ngược MD5 / Plaintext (tự động nâng cấp lên Bcrypt)
     *
     * @param string $email
     * @param string $matkhau
     * @param array $allowedRoles Danh sách quyền được phép đăng nhập, ví dụ [3] cho khách, [1, 2] cho admin
     * @return array ['success' => bool, 'user' => ?array, 'message' => string]
     */
    public function authenticate(string $email, string $matkhau, array $allowedRoles = [1, 2, 3]): array {
        $email = trim($email);
        $matkhau = trim($matkhau);

        if ($email === '' || $matkhau === '') {
            return [
                'success' => false,
                'user'    => null,
                'message' => 'Vui lòng nhập đầy đủ Email và Mật khẩu.'
            ];
        }

        $db = DATABASE::connect();
        try {
            $stmt = $db->prepare("SELECT * FROM nguoidung WHERE email = :email LIMIT 1");
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 1. Kiểm tra tài khoản tồn tại (thông báo chung để tránh lộ thông tin)
            if (!$user) {
                return [
                    'success' => false,
                    'user'    => null,
                    'message' => 'Email hoặc mật khẩu không chính xác.'
                ];
            }

            // 2. Kiểm tra trạng thái hoạt động
            if ((int)$user['trangthai'] !== 1) {
                return [
                    'success' => false,
                    'user'    => null,
                    'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'
                ];
            }

            // 3. Kiểm tra vai trò truy cập
            if (!in_array((int)$user['loai'], $allowedRoles, true)) {
                return [
                    'success' => false,
                    'user'    => null,
                    'message' => 'Tài khoản không có quyền truy cập khu vực này.'
                ];
            }

            // 4. Xác thực mật khẩu
            $storedHash = (string)($user['matkhau'] ?? '');
            $isPasswordValid = false;

            if (password_verify($matkhau, $storedHash)) {
                $isPasswordValid = true;
            } elseif (md5($matkhau) === $storedHash || $matkhau === $storedHash) {
                // Tự động nâng cấp mật khẩu sang BCRYPT ngay lập tức
                $isPasswordValid = true;
                $newHash = password_hash($matkhau, PASSWORD_BCRYPT);
                $upd = $db->prepare("UPDATE nguoidung SET matkhau = :newHash WHERE id = :id");
                $upd->execute([':newHash' => $newHash, ':id' => (int)$user['id']]);
            }

            if (!$isPasswordValid) {
                return [
                    'success' => false,
                    'user'    => null,
                    'message' => 'Email hoặc mật khẩu không chính xác.'
                ];
            }

            // 5. Xóa mật khẩu khỏi mảng trả về trước khi lưu vào Session
            unset($user['matkhau']);

            return [
                'success' => true,
                'user'    => $user,
                'message' => 'Đăng nhập thành công.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'user'    => null,
                'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin người dùng theo ID (không kèm mật khẩu)
     */
    public function layNguoidungTheoId(int $id): ?array {
        $db = DATABASE::connect();
        try {
            $stmt = $db->prepare("SELECT id, email, sodienthoai, hoten, trangthai, loai, hinhanh FROM nguoidung WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Đổi mật khẩu tài khoản
     */
    public function doiMatKhauKhachHang(int $id, string $matkhauCu, string $matkhauMoi): array {
        if (strlen($matkhauMoi) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu mới phải có tối thiểu 6 ký tự.'];
        }

        $db = DATABASE::connect();
        try {
            $stmt = $db->prepare("SELECT id, matkhau FROM nguoidung WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'message' => 'Không tìm thấy thông tin tài khoản.'];
            }

            $stored = (string)$user['matkhau'];
            $valid = password_verify($matkhauCu, $stored) || (md5($matkhauCu) === $stored) || ($matkhauCu === $stored);

            if (!$valid) {
                return ['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.'];
            }

            $newHash = password_hash($matkhauMoi, PASSWORD_BCRYPT);
            $upd = $db->prepare("UPDATE nguoidung SET matkhau = :newHash WHERE id = :id");
            $upd->execute([':newHash' => $newHash, ':id' => $id]);

            return ['success' => true, 'message' => 'Đổi mật khẩu thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()];
        }
    }

    /**
     * Cập nhật thông tin hồ sơ khách hàng & địa chỉ mặc định
     */
    public function capNhatHoSoKhachHang(int $id, string $hoten, string $sodienthoai, ?string $diachi = null): bool {
        $db = DATABASE::connect();
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE nguoidung SET hoten = :hoten, sodienthoai = :sodt WHERE id = :id");
            $stmt->bindValue(':hoten', $hoten);
            $stmt->bindValue(':sodt', $sodienthoai);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($diachi)) {
                // Kiểm tra đã có địa chỉ mặc định chưa
                $stmtChk = $db->prepare("SELECT id FROM diachi WHERE nguoidung_id = :uid AND macdinh = 1 LIMIT 1");
                $stmtChk->bindValue(':uid', $id, PDO::PARAM_INT);
                $stmtChk->execute();
                $dc = $stmtChk->fetch(PDO::FETCH_ASSOC);

                if ($dc) {
                    $stmtUpd = $db->prepare("UPDATE diachi SET diachi = :dc WHERE id = :id");
                    $stmtUpd->bindValue(':dc', $diachi);
                    $stmtUpd->bindValue(':id', $dc['id']);
                    $stmtUpd->execute();
                } else {
                    $stmtIns = $db->prepare("INSERT INTO diachi (nguoidung_id, diachi, macdinh) VALUES (:uid, :dc, 1)");
                    $stmtIns->bindValue(':uid', $id, PDO::PARAM_INT);
                    $stmtIns->bindValue(':dc', $diachi);
                    $stmtIns->execute();
                }
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
}
?>
