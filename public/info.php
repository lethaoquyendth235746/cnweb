<?php include("inc/top.php"); ?>

<br><br>
<div class="container">  
<div class="row"> 
    <h4 class="text-info">Thông tin khách hàng</h4> 
    <?php if(isset($_SESSION["khachhang"])){ $i = 1; ?> 
    <p class="text-danger fw-bold"><?php echo $_SESSION["khachhang"]["hoten"]; ?></p>
	<h4 class="text-info">Danh sách đơn hàng</h4>
    <table class="table table-hover">
        <tr><th>STT</th><th>Mã đơn hàng</th><th>Ngày đặt</th><th>Số tiền</th><th>Chi tiết đơn hàng</th></tr>
        <?php foreach($donhang as $d){ ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><a href="index.php?action=thongtin&dhid=<?php echo $d["id"]; ?>">DH00000<?php echo $d["id"]; ?></a></td>
            <td><?php echo date("d/m/Y H:i:s", strtotime($d["ngay"])); ?></td>
            <td class="text-end fw-bold"><?php echo number_format($d["tongtien"]); ?> đ</td>
            <td><a href="index.php?action=thongtin&dhid=<?php echo $d["id"]; ?>">Xem chi tiết</a></td>
        </tr>
        <?php if(isset($donhangct) && ($d["id"] == $donhangct[0]["donhang_id"])){ ?>
            <tr><td></td><td colspan="4">
                <table class="table table-bordered">
                    <tr class="text-center"><th>Mặt hàng</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr>
                    <?php foreach($donhangct as $dhct){ ?>
                    <tr class="align-middle">
                        <td><a class="text-decoration-none" href="index.php?action=detail&id=<?php echo $dhct["id"]; ?>"><img class="img-thumbnail" width="50" src="../<?php echo $dhct["hinhanh"]; ?>"> <?php echo $dhct["tenmathang"]; ?></a></td>
                        <td class="text-end"><?php echo number_format($dhct["dongia"]); ?> đ</td>
                        <td class="text-end"><?php echo $dhct["soluong"]; ?></td>
                        <td class="text-end"><?php echo number_format($dhct["thanhtien"]); ?> đ</td>
                    </tr>
                    <?php } ?>
                    </table>
            </td></tr>
        <?php }} ?>
    </table>
    <?php 
    }
    else{
    ?>
    <p>Bạn chưa đăng nhập. Vui lòng <a href="index.php?action=dangnhap">đăng nhập</a> để xem danh sách đơn hàng.</p>

    <?php
    }
    ?>
</div>
</div>

<?php include("inc/bottom.php"); ?>