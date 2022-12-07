$(function () {
	$('#login-form-link').click(function (e) {
		$("#login-form").delay(100).fadeIn(100);
		$("#register-form").fadeOut(100);
		$('#register-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
	$('#register-form-link').click(function (e) {
		$("#register-form").delay(100).fadeIn(100);
		$("#login-form").fadeOut(100);
		$('#login-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
});

async function login(e) {
	e.preventDefault();
	let value;
	let mobile = document.getElementById('mobile').value
	let password = document.getElementById('password').value
	await axios({
		method: 'post',
		url: 'http://localhost/game/login.php',
		data: {
			mobile: mobile,
			password: password
		}
	}).then((res) => {
		value = res
		console.log(res);
	});
	return value;
}

async function register(e) {
	e.preventDefault();
	let value;
	let name = document.getElementById('name').value
	let mobile = document.getElementById('register_mobile').value
	let password = document.getElementById('register_password').value
	await axios({
		method: 'post',
		url: 'http://localhost/game/register.php',
		data: {
			name: name,
			mobile: mobile,
			password: password
		}
	}).then((res) => {
		value = res
		console.log(res);
	});
	return value;
}
