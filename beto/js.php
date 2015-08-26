<?php 
	header('content-type: application/javascript');
	if (!isset($_GET['key'])) {
		return;
	}
	$key = $_GET['key'];
?>
function Ajax(client_key,method,callback) {

	var xmlhttp;
	if (window.XMLHttpRequest) {
	    xmlhttp = new XMLHttpRequest();
	} else {
	    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange = function () {
		var response;

		if (xmlhttp.readyState == 4) {
			if (xmlhttp.status == 200) {
				//alert(xmlhttp.responseText);
				try {
					response = JSON.parse(xmlhttp.responseText);
					if (response.error.code == 0) {
						if (typeof callback !== 'undefined') {
							//alert(xmlhttp.responseText);
							callback(response);
						}
					} else {
						alert(response.error.reason);
					}
				} catch (error) {
					alert('Bad server response');
				}
			} else {
				alert('Server not found');
			}
		}
	}

    xmlhttp.open("GET","http://localhost/betometer/api.php?method="+method+"&client_key="+client_key+"&nocache="+(new Date().getTime()),true);
    xmlhttp.send();
}



function Initialise(scheme) {
	if (!scheme.error) {
		var div = document.getElementById('chroma_betometer');
		if (div) {
			var view_name = div.getAttribute('data-style');
			if (view_name) {
				Ajax('<?=$key;?>','Compose'+view_name+'View',Initialise);
			}
		} else {
			return;
		}
	} else {
		InjectElements(scheme);
		window[scheme.view.trigger_function]();
	}
}

function InjectElements(scheme) {
	var style = document.createElement('style');
	style.innerHTML = scheme.view.css;
	document.head.appendChild(style);

	var script = document.createElement('script');
	script.innerHTML = scheme.view.js;
	document.head.appendChild(script);

	var div = document.getElementById('chroma_betometer');
	div.innerHTML = scheme.view.html;
}

window.addEventListener('load', Initialise, false);