<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="shortcut icon" href="../../images/logo.png" type="image/x-icon" />
	
	<link rel="preconnect" href="https://fonts.gstatic.com">
	
	<title>Đăng nhập Quản trị - LPT DRONE</title>

	<link href="../inc/css/app.css" rel="stylesheet">
	<script src="../inc/js/app.js"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>

<body style="background: #111315;">
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="text-center mt-4 mb-3">
							<img src="../../images/logo.png" alt="LPT DRONE" class="img-fluid mb-2" style="max-height: 85px; border-radius: 10px; box-shadow: 0 4px 15px rgba(217, 4, 41, 0.3);">
							<h1 class="h2 text-white fw-bold">LPT <span style="color: #D90429;">DRONE</span></h1>
							<p class="text-secondary">
								Cổng Quản Trị Hệ Thống Linh Kiện XAG
							</p>
						</div>

						<div class="card shadow-lg" style="border-radius: 14px; border: 1px solid rgba(255,255,255,0.1);">
							<div class="card-body">
								<div class="m-sm-3">
									<form action="index.php" method="post">
										<div class="mb-3">
											<label class="form-label fw-semibold">Email Quản trị viên</label>
											<input class="form-control form-control-lg" type="email" name="txtemail" placeholder="admin@xag.vn" required />
										</div>
										<div class="mb-3">
											<label class="form-label fw-semibold">Mật khẩu</label>
											<input class="form-control form-control-lg" type="password" name="txtmatkhau" placeholder="Nhập mật khẩu" required />
										</div>
										<div class="d-grid gap-2 my-3">
											<input type="hidden" name="action" value="xldangnhap">
											<button type="submit" class="btn btn-lg fw-bold text-white" style="background-color: #D90429; border: none;">Đăng nhập hệ thống</button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="text-center text-secondary small mt-3">
							Tài khoản Admin: <code>admin@xag.vn</code> | Pass: <code>123456</code><br>
							<a href="../../public/index.php" class="text-danger fw-semibold mt-2 d-inline-block">&larr; Quay lại trang bán hàng khách</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

</body>

</html>