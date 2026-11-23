<?php
/**
 * model/lienhe.php
 * Model Quản lý Liên hệ & Yêu cầu Tư vấn kỹ thuật LPT DRONE
 */

class LIENHE {
    private $id;
    private $nguoidung_id;
    private $hoten;
    private $sodienthoai;
    private $dongmay;
    private $noidung;
    private $trangthai;
    private $ghichu_admin;
    private $ngaygui;

    /**
     * Thêm yêu cầu liên hệ / tư vấn kỹ thuật mới
     */
    public function themlienhe($nguoidung_id, $hoten, $sodienthoai, $dongmay, $noidung) {
        $db = DATABASE::connect();
        try {
            $sql = "INSERT INTO lienhe (nguoidung_id, hoten, sodienthoai, dongmay, noidung, trangthai, ngaygui)
                    VALUES (:nguoidung_id, :hoten, :sodienthoai, :dongmay, :noidung, 0, NOW())";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(':nguoidung_id', !empty($nguoidung_id) ? (int)$nguoidung_id : null, !empty($nguoidung_id) ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $cmd->bindValue(':hoten', trim($hoten));
            $cmd->bindValue(':sodienthoai', trim($sodienthoai));
            $cmd->bindValue(':dongmay', !empty($dongmay) ? trim($dongmay) : null);
            $cmd->bindValue(':noidung', trim($noidung));
            return $cmd->execute();
        } catch (PDOException $e) {
            error_log("Lỗi thêm liên hệ: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách liên hệ của một người dùng (nếu cần tra cứu cá nhân)
     */
    public function laylienhetheonguoidung($nguoidung_id) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM lienhe WHERE nguoidung_id = :nguoidung_id ORDER BY ngaygui DESC";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(':nguoidung_id', (int)$nguoidung_id, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi lấy liên hệ theo người dùng: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số liên hệ đang chờ tư vấn
     */
    public function demlienhechoxuly() {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT COUNT(*) FROM lienhe WHERE trangthai = 0";
            $cmd = $db->prepare($sql);
            $cmd->execute();
            return (int)$cmd->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi đếm liên hệ chờ xử lý: " . $e->getMessage());
            return 0;
        }
    }
}
