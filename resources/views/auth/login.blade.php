<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Login</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet">
	<link href="{{ asset('assets/css/all.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet"> <!-- Assuming you have login-specific styles -->
	<style>
		/* Example style for .bg */
		.bg {
			background-size: cover;
			background-position: center;
		}
	</style>
</head>
<body>

	<div class="d-lg-flex half">
		<div class="bg order-1 order-md-2" style="background-image: url('{{ asset('assets/images/login_right.png') }}');"></div>
		<div class="contents order-2 order-md-1">

			<div class="container">
				<div class="row align-items-center justify-content-center">
					<div class="col-md-7 text-center">
						<img src="{{ asset('assets/images/meezotech_logo.png') }}" alt="Logo" width="400">
						<br><br><br>
						<h5>Login to <strong>Fleet Management System</strong></h5>

						<form method="POST" action="{{ route('login') }}">
							@csrf
							<div class="form-group first text-left">
								<label for="email">Email</label>
								<input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
									placeholder="your-email@gmail.com" id="email" value="{{ old('email') }}" required autofocus>
								@error('email')
									<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
								@enderror
							</div>

							<div class="form-group last mb-3 text-left">
								<label for="password">Password</label>
								<input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
									placeholder="Your Password" id="password" required>
								@error('password')
									<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
								@enderror
							</div>

							<div class="d-flex mb-5 align-items-center">
								<label class="control control--checkbox mb-0"><span class="caption">Remember me</span>
									<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
									<div style="background: #cc292b;" class="control__indicator"></div>
								</label>
								<span class="ml-auto">
									{{-- <a href="{{ route('password.request') }}" class="forgot-pass">Forgot Password?</a> --}}
								</span>
							</div>

							<button type="submit" style="background-color: #cc292b; border-color: #cc292b; height: 54px; padding-left: 30px; padding-right: 30px; display: flex; align-items: center; justify-content: center; color: white;" class="btn btn-block btn-primary">
								Log In
							</button>
						</form>

					</div>
				</div>
			</div>

		</div>
	</div>

	<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
