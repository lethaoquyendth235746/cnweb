<?php
class DONGMAY {
    private $id;
    private $tendongmay;

    public function getid(){
        return $this->id;
    }

    public function setid($value){
        $this->id = $value;
    }

    public function gettendongmay(){
        return $this->tendongmay;
    }

    public function settendongmay($value){
        $this->tendongmay = $value;
    }

    // Lấy danh sách tất cả dòng máy
    public function laydongmay(){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM dongmay ORDER BY tendongmay ASC";
            $cmd = $dbcon->prepare($sql);
            $cmd->execute();
            return $cmd->fetchAll();
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Lấy dòng máy kèm số lượng sản phẩm liên kết
    public function laydongmaykemmathang(){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT dm.id, dm.tendongmay, COUNT(m.id) AS tong_san_pham
                    FROM dongmay dm
                    LEFT JOIN mathang m ON (m.dongmay = dm.tendongmay OR m.dongmay LIKE CONCAT('%', dm.tendongmay, '%'))
                    GROUP BY dm.id, dm.tendongmay
                    ORDER BY dm.tendongmay ASC";
            $cmd = $dbcon->prepare($sql);
            $cmd->execute();
            return $cmd->fetchAll();
        }
        catch(PDOException $e){
            $error_message = $e->getMessage();
            echo "<p>Lỗi truy vấn: $error_message</p>";
            exit();
        }
    }

    // Lấy dòng máy theo id
    public function laydongmaytheoid($id){
        $dbcon = DATABASE::connect();
        try{
            $sql = "SELECT * FROM dongmay WHERE id = :id";
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
}
?>