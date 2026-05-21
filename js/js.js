function search(page){
	str = $("input[name='search']").val();
	if (str == "") {
		document.getElementById("td_details").innerHTML = "";
		return;
	} else {	
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("div_search").innerHTML = xmlhttp.responseText;
			}else{
			}
		};
		xmlhttp.open("GET","ajax/ajaxowner.php?search="+str,true);
		xmlhttp.send();
	}	
}


function modal(str, form){
	if (str == "") {
		document.getElementById("ajax").innerHTML = "";
		return;
	} else { 
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("ajax").innerHTML = xmlhttp.responseText;
			}
		};
		xmlhttp.open("GET","ajax/ajaxowner.php?modal&val="+str+"&form="+form,true);
		xmlhttp.send();
		if(form == 'b_mechanical' || form == 'b_electronics'){
			$('#ajax').removeClass('modal-md');
			$('#ajax').addClass('modal-lg');
		}else{			
			$('#ajax').removeClass('modal-lg');
			$('#ajax').addClass('modal-md');
		}
		$("#modaltrig").click();
	}
}


//oldies
function edit(str,form){
	if (str == "") {
		document.getElementById("ajax").innerHTML = "";
		return;
	} else { 
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("ajax").innerHTML = xmlhttp.responseText;
			}
		};
		xmlhttp.open("GET","ajax/ajaxowner.php?edit&val="+str+"&form="+form,true);
		xmlhttp.send();
		$("#modaltrig").click();
	}
}


function addx(form){
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			document.getElementById("ajax").innerHTML = xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","ajax/ajaxowner.php?add&form="+form,true);
	xmlhttp.send();
	$("#modaltrig").click();
}

function engr(val){
	if(val == ""){
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				if(val.includes("other") === true){
					document.getElementById(val).innerHTML = xmlhttp.responseText;
				}else{
					document.getElementById("engr_ajax").innerHTML = xmlhttp.responseText;
				}
				
			}
		};
		xmlhttp.open("GET","ajax/ajaxowner.php?engrx&val="+val,true);
		xmlhttp.send();
	}
}


function indexing(form,view){
	if(form != ""){
		if(view == "" || view <= 0){
			view = 1;
		}
		year = $("select[name='year'").val();
		if (window.XMLHttpRequest) {
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		} else {
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("tablex").innerHTML = xmlhttp.responseText;
				$('[data-toggle="tooltip"]').tooltip();
			}
		};
		xmlhttp.open("GET","ajax/ajaxowner.php?indexing="+form+"&view="+view+"&year="+year,true);
		xmlhttp.send();
	}
}

