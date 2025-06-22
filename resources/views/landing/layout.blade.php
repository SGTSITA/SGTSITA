<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title>SGT - Sistema de gestión de transporte</title>
		<meta name="robots" content="noindex, follow">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
		<!-- CSS
			============================================ -->
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="{{asset('/landing/css/bootstrap.min.css')}}">
		<!-- Fontawesome -->
		<link rel="stylesheet" href="{{asset('/landing/css/fontawesome.css')}}">
		<!-- Flaticon -->
		<link rel="stylesheet" href="{{asset('/landing/css/flaticon.css')}}">
		<!-- Base Icons -->
		<link rel="stylesheet" href="{{asset('/landing/css/pbminfotech-base-icons.css')}}">
		<!-- Themify Icons -->
		<link rel="stylesheet" href="{{asset('/landing/css/themify-icons.css')}}">
		<!-- Slick -->
		<link rel="stylesheet" href="{{asset('/landing/css/swiper.min.css')}}">
		<!-- Magnific -->
		<link rel="stylesheet" href="{{asset('/landing/css/magnific-popup.css')}}">
		<!-- AOS -->
		<link rel="stylesheet" href="{{asset('/landing/css/aos.css')}}">
		<!-- Shortcode CSS -->
		<link rel="stylesheet" href="{{asset('/landing/css/shortcode.css')}}">
		<!-- Base CSS -->
		<link rel="stylesheet" href="{{asset('/landing/css/base.css')}}">
		<!-- Style CSS -->
		<link rel="stylesheet" href="{{asset('/landing/css/style.css')}}">
		<!-- Responsive CSS -->
		<link rel="stylesheet" href="{{asset('/landing/css/responsive.css')}}">
	</head>
	<body>

	<!-- page wrapper -->
	<div class="page-wrapper" id="page">

		<!-- Header Main Area -->
		<header class="site-header header-style-1">
			<div class="pbmit-header-overlay">
				<div class="pbmit-main-header-area">
					<div class="container-fluid">
						<div class="pbmit-header-content d-flex justify-content-between align-items-center">
							<div class="pbmit-logo-menuarea d-flex justify-content-between align-items-center">
								<div class="site-branding">
									<h1 class="site-title">
										<a href="/">
											<img class="logo-img" src="/assets/metronic/logo-blanco-sgt.png" alt="SGT">
										</a>
									</h1>
								</div>
								<div class="site-navigation">
									<nav class="main-menu navbar-expand-xl navbar-light">
										<div class="navbar-header">
											<!-- Toggle Button --> 
											<button class="navbar-toggler" type="button">
												<i class="pbmit-base-icon-menu-1"></i>
											</button>
										</div>
										<div class="pbmit-mobile-menu-bg"></div>
										<div class="collapse navbar-collapse clearfix show" id="pbmit-menu">
											<div class="pbmit-menu-wrap">
												<span class="closepanel">
													<svg class="qodef-svg--close qodef-m" xmlns="http://www.w3.org/2000/svg" width="20.163" height="20.163" viewBox="0 0 26.163 26.163">
														<rect width="36" height="1" transform="translate(0.707) rotate(45)"></rect>
														<rect width="36" height="1" transform="translate(0 25.456) rotate(-45)"></rect>
													</svg>
												</span>
												<ul class="navigation clearfix">
													<li >
														<a href="/">Inicio</a>
													</li>
													
													
													<li><a href="{{route('aviso-privacidad')}}">Aviso de privacidad</a></li>
												</ul>
											</div>
										</div>
									</nav>
								</div>
							</div>
							<div class="pbmit-right-box d-flex align-items-center">
								<!--div class="social-links-wrapper">
									<ul class="pbmit-social-links">
										<li class="pbmit-social-li pbmit-social-facebook">
											<a title="Facebook" href="#" target="_blank">
											<span><i class="pbmit-base-icon-facebook-f"></i></span>
											</a>
										</li>
										<li class="pbmit-social-li pbmit-social-twitter">
											<a title="Twitter" href="#" target="_blank">
											<span><i class="pbmit-base-icon-twitter-2"></i></span>
											</a>
										</li>
										<li class="pbmit-social-li pbmit-social-linkedin">
											<a title="LinkedIn" href="#" target="_blank">
											<span><i class="pbmit-base-icon-linkedin-in"></i></span>
											</a>
										</li>
										<li class="pbmit-social-li pbmit-social-instagram">
											<a title="Instagram" href="#" target="_blank">
											<span><i class="pbmit-base-icon-instagram"></i></span>
											</a>
										</li>
									</ul>
								</div>
								<div class="pbmit-header-search-btn">
									<a href="#" title="Search">
										<i class="pbmit-base-icon-search-1"></i>
									</a>
								</div-->
								<div class="pbmit-header-button2">
									<a class="pbmit-btn pbmit-btn-white" href="{{route('login')}}">
										<span class="pbmit-button-content-wrapper">
											<span class="pbmit-button-text">Iniciar sesión</span>
										</span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			@yield('slider')
		</header>
		<!-- Header Main Area End Here -->

		<!-- page content -->
		<div class="page-content">

			
@yield('page-content')


		</div>
		<!-- page content End -->

		<!-- footer -->
        <footer class="site-footer pbmit-bg-color-secondary">
			<div class="pbmit-footer-big-area-wrapper">
				
				<div class="pbmit-footer-widget-area">
					<div class="container">
						<div class="row">
							<div class="pbmit-footer-widget-col-1 col-md-4">
								<aside class="widget">
									<div class="pbmit-footer-logo">
										<img src="/landing/images/logo-blanco-sgt (1).png" class="img-fluid" alt="">
									</div><br>
									<ul class="pbmit-social-links">
										<li class="pbmit-social-li pbmit-social-facebook">
											<a title="Facebook" href="#" target="_blank">
												<span><i class="pbmit-base-icon-facebook-f"></i></span>
											</a>
										</li>
										<li class="pbmit-social-li pbmit-social-twitter">
											<a title="Twitter" href="#" target="_blank">
												<span><i class="pbmit-base-icon-twitter-2"></i></span>
											</a>
										</li>
										<li class="pbmit-social-li pbmit-social-linkedin">
											<a title="LinkedIn" href="#" target="_blank">
												<span><i class="pbmit-base-icon-linkedin-in"></i></span>
											</a>
										</li>
										<li class="pbmit-social-li pbmit-social-instagram">
											<a title="Instagram" href="#" target="_blank">
												<span><i class="pbmit-base-icon-instagram"></i></span>
											</a>
										</li>
									</ul>
								</aside>
							</div>
							<div class="pbmit-footer-widget-col-2 col-md-4">
								<aside class="widget">
									<h2 class="widget-title">Llamenos</h2>
									<div class="pbmit-contact-widget-lines">
										<div class="pbmit-contact-widget-line pbmit-base-icon-phone">+52 56 1068 5796</div>
										<div class="pbmit-contact-widget-line pbmit-base-icon-email">sgt.gologipro@gmail.com</div>
									</div>
								</aside>
							</div>
							
						</div>
					</div>
				</div>
				<div class="pbmit-footer-text-area">
					<div class="container">
						<div class="pbmit-footer-text-inner">
							<div class="row">
								<div class="col-md-12">
									<div class="pbmit-footer-copyright-text-area">
										Copyright © 2025 <a href="#">Sistema de Gestión de Transporte</a>, Todos los derechos reservados.
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
        </footer>
		<!-- footer End -->

	</div>
	<!-- page wrapper End -->

	<!-- Search Box Start Here -->
	<div class="pbmit-search-overlay">
		<div class="pbmit-icon-close">
			<svg class="qodef-svg--close qodef-m" xmlns="http://www.w3.org/2000/svg" width="28.163" height="28.163" viewBox="0 0 26.163 26.163">
				<rect width="36" height="1" transform="translate(0.707) rotate(45)"></rect>
				<rect width="36" height="1" transform="translate(0 25.456) rotate(-45)"></rect>
			</svg>
		</div>
		<div class="pbmit-search-outer"> 
			<form class="pbmit-site-searchform">
				<input type="search" class="form-control field searchform-s" name="s" placeholder="Search …">
				<button type="submit"></button>
			</form>
		</div>
	</div>
	<!-- Search Box End Here -->

	<!-- Scroll To Top -->
	<div class="pbmit-backtotop">
		<div class="pbmit-arrow">
			<i class="pbmit-base-icon-plane"></i>
		</div>
		<div class="pbmit-hover-arrow">
			<i class="pbmit-base-icon-plane"></i>
		</div>
	</div>
	<!-- Scroll To Top End -->
	
	<!-- JS
		============================================ -->
	<!-- jQuery JS -->
	<script src="/landing/js/jquery.min.js"></script>
	<!-- Popper JS -->
	<script src="/landing/js/popper.min.js"></script>
	<!-- Bootstrap JS -->
	<script src="/landing/js/bootstrap.min.js"></script>
	<!-- jquery Waypoints JS -->
	<script src="/landing/js/jquery.waypoints.min.js"></script>
	<!-- jquery Appear JS -->
	<script src="/landing/js/jquery.appear.js"></script>
	<!-- Numinate JS -->
	<script src="/landing/js/numinate.min.js"></script>
	<!-- Slick JS -->
	<script src="/landing/js/swiper.min.js"></script>
	<!-- Magnific JS -->
	<script src="/landing/js/jquery.magnific-popup.min.js"></script>
	<!-- Circle Progress JS -->
	<script src="/landing/js/circle-progress.js"></script> 
	<!-- countdown JS -->
	<script src="/landing/js/jquery.countdown.min.js"></script> 
	<!-- AOS -->
	<script src="/landing/js/aos.js"></script>
	<!-- GSAP -->
	<script src='/landing/js/gsap.js'></script>
	<!-- Scroll Trigger -->
	<script src='/landing/js/ScrollTrigger.js'></script>
	<!-- Split Text -->
	<script src='/landing/js/SplitText.js'></script>
	<!-- Theia Sticky Sidebar JS -->
	<script src='/landing/js/theia-sticky-sidebar.js'></script>
	<!-- GSAP Animation -->
	<script src='/landing/js/gsap-animation.js'></script>
	<!-- Form Validator -->
	<script src="/landing/js/jquery-validate/jquery.validate.min.js"></script>
	<!-- Scripts JS -->
	<script src="/landing/js/scripts.js"></script>

	</body>
</html>