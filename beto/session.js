var timeout_id;
var session_timeout_seconds = 60;

function InitialiseSessionHandling() {
	// clear the session timeout if input fields get the focus
	for (index in field_types=['textarea','input']) {
		var fields = document.getElementsByTagName(field_types[index]);
		for (var field_index=0; field_index<fields.length; field_index++) {
			fields[field_index].addEventListener("focus", ClearSessionTimeout);
		}		
	}

	timeout_id = setTimeout(SessionTimeout, session_timeout_seconds*1000);
}

function ClearSessionTimeout() {
	clearTimeout(timeout_id);
	timeout_id = setTimeout(SessionTimeout, session_timeout_seconds*1000);
}

function SessionTimeout() {
	if (document.getElementById('session_timeout')) {
		document.getElementById('session_timeout').submit();
	}
}