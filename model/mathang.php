<?php
class MATHANG{
    // khai báo các thuộc tính
    private $id;
    private $tenmathang;
    private $mota;
    private $giagoc;
    private $giaban;
    private $soluongton;
    private $hinhanh;
    private $danhmucid;
    private $luotxem;
    private $luotmua;

    public function getid(){ return $this->id; }
    public function setid($value){ $this->id = $value; }
    public function gettenmathang(){ return $this->tenmathang; }
    public function settenmathang($value){ $this->tenmathang = $value; }
    public function getmota(){ return $this->mota; }
    public function setmota($value){ $this->mota = $value; }
    public function getgiagoc(){ return $this->giagoc; }
    public function setgiagoc($value){ $this->giagoc = $value; }
    public function getgiaban(){ return $this->giaban; }
    public function setgiaban($value){ $this->giaban = $value; }
    public function getsoluongton(){ return $this->soluongton; }
    public function setsoluongton($value){ $this->soluongton = $value; }
    public function gethinhanh(){ return $this->hinhanh; }
    public function sethinhanh($value){ $this->hinhanh = $value; }
    public function getdanhmuc_id(){ return $this->danhmuc_id; }
    public function setdanhmuc_id($value){ $this->danhmuc_id = $value; }
    public function getluotxem(){ return $this->luotxem; }
    public function setluotxem($value){ $this->luotxem = $value; }
    public function getluotmua(){ return $this->luotmua; }
    public function setluotmua($value){ $this->luotmua = $value; }


    // Lấy danh sách
    public function laymathang(){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM mathang ORDER BY id DESC";
            $cmd = $dbcon->prepare($sql);
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
	// Lấy danh sách mặt hàng thuộc 1 danh mục
    public function laymathangtheodanhmuc($danhmuc_id){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM mathang WHERE danhmuc_id=:madm" ;
            $cmd = $dbcon->prepare($sql);
			$cmd->bindValue(":madm",$danhmuc_id);
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

    // Lấy mặt hàng theo id
    public function laymathangtheoid($id){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM mathang WHERE id=:id";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":id", $id);
            $cmd->execute();
            $result = $cmd->fetch();             
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }
    // Cập nhật lượt xem
    public function tangluotxem($id){
        $dbcon = DATABASE::connect();
        try{
            $sql = "UPDATE mathang SET luotxem=luotxem+1 WHERE id=:id";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":id", $id);
            $result = $cmd->execute();            
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }
    // Lấy mặt hàng xem nhiều
    public function laymathangxemnhieu(){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM mathang ORDER BY luotxem DESC LIMIT 3";
            $cmd = $dbcon->prepare($sql);
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

    // Thêm mới
    public function themmathang($mathang){
        $dbcon = DATABASE::connect();
        try{
            $sql = "INSERT INTO mathang(tenmathang,mota,giagoc,giaban,soluongton,danhmuc_id,hinhanh,luotxem,luotmua) 
                VALUES(:tenmathang,:mota,:giagoc,:giaban,:soluongton,:danhmuc_id,:hinhanh,0,0)";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":tenmathang", $mathang->tenmathang);
            $cmd->bindValue(":mota", $mathang->mota);
            $cmd->bindValue(":giagoc", $mathang->giagoc);
            $cmd->bindValue(":giaban", $mathang->giaban);
            $cmd->bindValue(":soluongton", $mathang->soluongton);
            $cmd->bindValue(":danhmuc_id", $mathang->danhmuc_id);
            $cmd->bindValue(":hinhanh", $mathang->hinhanh);
            $result = $cmd->execute();            
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Xóa 
    public function xoamathang($mathang){
        $dbcon = DATABASE::connect();
        try{
            $sql = "DELETE FROM mathang WHERE id=:id";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":id", $mathang->id);
            $result = $cmd->execute();            
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Cập nhật 
    public function suamathang($mathang){
        $dbcon = DATABASE::connect();
        try{
            $sql = "UPDATE mathang SET tenmathang=:tenmathang,
                                        mota=:mota,
                                        giagoc=:giagoc,
                                        giaban=:giaban,
                                        soluongton=:soluongton,
                                        danhmuc_id=:danhmuc_id,
                                        hinhanh=:hinhanh,
                                        luotxem=:luotxem,
                                        luotmua=:luotmua
                                        WHERE id=:id";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":tenmathang", $mathang->tenmathang);
            $cmd->bindValue(":mota", $mathang->mota);
            $cmd->bindValue(":giagoc", $mathang->giagoc);
            $cmd->bindValue(":giaban", $mathang->giaban);
            $cmd->bindValue(":soluongton", $mathang->soluongton);
            $cmd->bindValue(":danhmuc_id", $mathang->danhmuc_id);
            $cmd->bindValue(":hinhanh", $mathang->hinhanh);
            $cmd->bindValue(":luotxem", $mathang->luotxem);
            $cmd->bindValue(":luotmua", $mathang->luotmua);
            $cmd->bindValue(":id", $mathang->id);
            $result = $cmd->execute();            
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }
    // Cập nhật số lượng tồn
    public function capnhatsoluong($id, $soluong){
        $dbcon = DATABASE::connect();
        try{
            $sql = "UPDATE mathang SET soluongton=soluongton - :soluong WHERE id=:id";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":soluong", $soluong);
            $cmd->bindValue(":id", $id);
            $result = $cmd->execute();            
            return $result;
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Tìm kiếm mặt hàng theo từ khóa
    public function timkiemmathang($tukhoa, $sort = null){
        $dbcon = DATABASE::connect();
        try{
            $order = "ORDER BY id DESC";
            if($sort == "gia_tang") $order = "ORDER BY giaban ASC";
            elseif($sort == "gia_giam") $order = "ORDER BY giaban DESC";

            $sql = "SELECT * FROM mathang 
                    WHERE tenmathang LIKE :tukhoa 
                       OR mota LIKE :tukhoa 
                       OR dongmay LIKE :tukhoa 
                    $order";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":tukhoa", "%" . $tukhoa . "%");
            $cmd->execute();
            return $cmd->fetchAll();
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Lấy mặt hàng theo dòng máy (P100, P100 Pro, P40, V40, R150)
    public function laymathangtheodongmay($dongmay, $sort = null){
        $dbcon = DATABASE::connect();
        try{
            $order = "ORDER BY id DESC";
            if($sort == "gia_tang") $order = "ORDER BY giaban ASC";
            elseif($sort == "gia_giam") $order = "ORDER BY giaban DESC";

            $sql = "SELECT * FROM mathang 
                    WHERE dongmay LIKE :dongmay 
                       OR tenmathang LIKE :dongmay 
                    $order";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":dongmay", "%" . $dongmay . "%");
            $cmd->execute();
            return $cmd->fetchAll();
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Lấy mặt hàng theo danh mục và sắp xếp giá
    public function laymathangtheodanhmucvasapxep($danhmuc_id, $sort = null){
        $dbcon = DATABASE::connect();
        try{
            $order = "ORDER BY id DESC";
            if($sort == "gia_tang") $order = "ORDER BY giaban ASC";
            elseif($sort == "gia_giam") $order = "ORDER BY giaban DESC";

            $sql = "SELECT * FROM mathang WHERE danhmuc_id=:madm $order";
            $cmd = $dbcon->prepare($sql);
            $cmd->bindValue(":madm", $danhmuc_id);
            $cmd->execute();
            return $cmd->fetchAll();
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Bộ lọc đa năng chuẩn E-commerce Shopify (Danh mục + Dòng máy + Khoảng giá + Sắp xếp + Tìm kiếm)
    public function locmathang($danhmuc_id = null, $dongmay = null, $mucgia = null, $sort = null, $tukhoa = null){
        $dbcon = DATABASE::connect();
        try {
            $sql = "SELECT * FROM mathang WHERE 1=1";
            $params = [];

            if(!empty($danhmuc_id) && $danhmuc_id > 0) {
                $sql .= " AND danhmuc_id = :dmid";
                $params[":dmid"] = $danhmuc_id;
            }

            if(!empty($dongmay)) {
                $sql .= " AND (dongmay LIKE :dongmay OR tenmathang LIKE :dongmay)";
                $params[":dongmay"] = "%" . $dongmay . "%";
            }

            if(!empty($tukhoa)) {
                $sql .= " AND (tenmathang LIKE :tukhoa OR mota LIKE :tukhoa OR dongmay LIKE :tukhoa)";
                $params[":tukhoa"] = "%" . $tukhoa . "%";
            }

            if(!empty($mucgia)) {
                if($mucgia == "duoi_2tr") {
                    $sql .= " AND giaban < 2000000";
                } elseif($mucgia == "2tr_5tr") {
                    $sql .= " AND giaban BETWEEN 2000000 AND 5000000";
                } elseif($mucgia == "5tr_10tr") {
                    $sql .= " AND giaban BETWEEN 5000000 AND 10000000";
                } elseif($mucgia == "tren_10tr") {
                    $sql .= " AND giaban > 10000000";
                }
            }

            if($sort == "gia_tang") {
                $sql .= " ORDER BY giaban ASC";
            } elseif($sort == "gia_giam") {
                $sql .= " ORDER BY giaban DESC";
            } elseif($sort == "banchay") {
                $sql .= " ORDER BY luotmua DESC, luotxem DESC";
            } else {
                $sql .= " ORDER BY id DESC";
            }

            $cmd = $dbcon->prepare($sql);
            foreach($params as $key => $val) {
                $cmd->bindValue($key, $val);
            }
            $cmd->execute();
            return $cmd->fetchAll();
        } catch(PDOException $e) {
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

}
?>
