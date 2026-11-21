<?php include("../inc/top.php"); ?>

<!-- Form cập nhật thông tin ng dùng-->
  <div class="row" >
    <div class="col-12 col-md-10 m-auto">
      <div class="card p-5">
        <div class="card-header">          
          <h4 class="text-info text-center">H�' SƠ NGƯ�oI D�TNG</h4> 
        </div>
        <div class="card-body">
          <form method="post" enctype="multipart/form-data" action="../ktnguoidung/index.php">
          	<input type="hidden" name="txtid" value="<?php echo $_SESSION["nguoidung"]["id"]; ?>" >
          	<input type="hidden" name="txthinhanh" value="<?php echo $_SESSION["nguoidung"]["hinhanh"]; ?>" >
            <input type="hidden" name="action" value="xlhoso" >
            <div class="text-center">
              <img class="img-thumbnail" src="<?php if ($_SESSION["nguoidung"]["hinhanh"]==NULL) echo "../../images/users/user.png"; else echo "../../images/users/" . $_SESSION["nguoidung"]["hinhanh"]; ?>" alt="<?php echo $_SESSION["nguoidung"]["hoten"]; ?>" width="100px">
            </div>
            <input type="hidden" name="txtid" value="<?php echo $_SESSION["nguoidung"]["id"]; ?>">
            <div class="my-3">    
            <label class="form-label">Email</label>    
            <input class="form-control" type="email" name="txtemail" placeholder="Email" value="<?php echo $_SESSION["nguoidung"]["email"]; ?>" readonly>
            </div>
            <div class="my-3">    
            <label class="form-label">S�' �'i�?n thoại</label>    
            <input class="form-control" type="number" name="txtdienthoai" placeholder="S�' �'i�?n thoại" value="<?php echo $_SESSION["nguoidung"]["sodienthoai"]; ?>" required>
            </div>            
            <div class="my-3">
            <label class="form-label">Họ tên</label>
            <input class="form-control" type="text" name="txthoten" placeholder="Họ tên" value="<?php echo $_SESSION["nguoidung"]["hoten"]; ?>" required></div>
            <div class="my-3">
              <label class="form-label">Đ�.i hình �'ại di�?n</label>
              <input class="form-control" type="file" name="fhinh">
            </div>
            <div class="my-3 text-center">            
            <input class="btn btn-primary"  type="submit" value="Cập nhật">
            <input class="btn btn-warning"  type="reset" value="Không">
        	</div>
          </form>
        </div>
        
      </div>
    </div>
  </div>


<?php include("../inc/bottom.php"); ?>
