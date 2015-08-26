function Initialise() {
	//alert('Initialise');

	InitialiseSessionHandling();
}

function Validate() {
	//alert('Validate()');
	return true;
}

function ValidateLogin() {
	//alert('ValidateLogin');

	if (document.getElementById('username').value=='') {
		document.getElementById('username').focus();
		alert('Username can not be blank')
		return false;
	}
	if (document.getElementById('password').value=='') {
		document.getElementById('username').focus();
		alert('Password can not be blank');
		return false;
	}
	return true;
}

window.addEventListener('load', Initialise, false);