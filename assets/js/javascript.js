$(function() {

    $('#login-form-link').click(function(e) {
		$("#login-form").delay(100).fadeIn(100);
 		$("#register-form").fadeOut(100);
		$('#register-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
	$('#register-form-link').click(function(e) {
		$("#register-form").delay(100).fadeIn(100);
 		$("#login-form").fadeOut(100);
		$('#login-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});

});
let button=document.getElementById("login-submit")
button.addEventListener("click",login())
console.log(button)
async function login(){
	let value ;
	let mobile =document.getElementById('mobile').value
	let password =document.getElementById('password').value
	await axios({
    method: 'post',
    url: 'http://localhost/game/login.php',
    data: {
     mobile:mobile,
	 password:password
    }
  }).then((res)=>{
  
	value= res
	
	console.log(res)
  });
  return value;
  
}
