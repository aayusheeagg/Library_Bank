// JavaScript Document
$(document).ready(function() {
	$('form.login').submit(function(e) {
		e.preventDefault();
		var id=$('input:text[name=loginid]').val();
		var passwd=$('input:password[name=passwd]').val();
		var detail={"action":"login", "id":id, "passwd":passwd};
		$.ajax({
			url:"ajax",
			type:"POST",
			data:detail,
			dataType:"json",
			success: function(data){
				$('span#msg').html(data.msg);
				if(data.success == true)
					location.reload();
			}, 
			error: function(error,status,errorThrown) {
				$('span#msg').html(status);
			}
		});
	});
	
	$('form.changePswd').submit(function(e) {
		e.preventDefault();
		var old_pswd=$('input:password[name=oldpswd]').val();
		var new_pswd=$('input:password[name=newpswd]').val();
		var conf_pswd=$('input:password[name=confpswd]').val();
		var msg="";
		if(new_pswd.length < 6)
			$('span#msg').html("Password should be atleast 6 characters");
		else if(new_pswd != conf_pswd)
			$('span#msg').html("Passwords Do not Match");
		else
		{
			$.ajax({
				url:"ajax",
				type:"POST",
				data:{"action":"changePswd", "old":old_pswd, "new":new_pswd},
				dataType:"json",
				success: function(data){
					$('span#msg').html(data.msg);
				},
				error: function(error,status,errorThrown){
					$('span#msg').html(status);
				}
			});
		}
		$(this)[0].reset();
	});

	$('.bookSwap').click(function() {
		$('table#books').each(function() {
			$(this).attr('style','display:none');
		});;
		$('table.'+$(this).attr('name')).attr('style','display:block');
	});
	
	$('input[name=searchbook]').keyup(function(e) {
	if(e.keyCode != 13){
		$('#searchlist').html('Searching...');
		$.post("ajax",{"action":"searchbook","key":$(this).val()},
			function(data,status){
				if(status=="success"){
					$('#searchlist').html(data);
					$('#searchlist li').bind('click',function(e) {
						$.post("ajax",{"action":"addchoice","bookid":$(this).attr('id')},
							function(data,success){
								if(status=="success"){
									$('#msg').html(data);
									refreshChoices();
									$('input[name=searchbook]').trigger('keyup');
								}
							}
						);
					});
				}
			}
		);
	}});
	
	$('.confirmchoice').click(function(e) {
		var conf = confirm("Are you sure? You wont be able to make any change later");
		if(conf){
			$.post("ajax",{"action":"confirmchoice"},
				function(data,success){
					alert(data);
					location.reload();
				}
			);
		}
	});

	$('td#update').click(function() {
		var ele = $(this).siblings('td:nth-child(2)');
		if($(this).text() == 'Edit'){
			ele.html("<input type='text' value = '" + ele.text() + "' size=30>");
			$(this).html('Save');
		}
		else if($(this).text() == 'Save'){
			ele.html(ele.children('input').val());
			$(this).html('Edit');
		}
	});

	$('form.feecollect input').keyup(function(e) {
	if(e.keyCode != 13){
		var regno=$(this).val();
		if(regno.length < 8){
			$('#feedetail').html("");
			return false;
		}
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"getfee", "regno":regno},
			dataType:"json",
			success: function(data){
				if(data.valid)
					$('#feedetail').html("<table><tr><th width='150px'>Name</th><td>"+data.name+"</td></tr>"+
									"<tr><th>Course</th><td>"+data.course+"</td></tr>"+
									"<tr><th>Branch</th><td>"+data.branch+"</td></tr>"+
									"<tr><th>Fees</th><td>"+data.fee+"</td></tr>"+
									"<tr><td colspan=2 align='center'><input type='submit' value='Confirm'></td></tr>");
				else
					$('#feedetail').html(data.msg);
			}, 
			error: function(error,status,errorThrown) {
				$('#feedetail').html("Error");
			}
		});
	}});

	$('form.feecollect').submit(function(e) {
		e.preventDefault();
		var regno=$('input:text[name=regno]').val();
		var d={"action":"confirmfee", "regno":regno};
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"confirmfee", "regno":regno},
			dataType:"json",
			success: function(data){
				if(data.success == 1)
					printReciept(data.info);
				$('#feedetail').html(data.msg);
				$('form.feecollect input').val('');
				$('form.feecollect input').focus();
			}, 
			error: function(error,status,errorThrown) {
				alert(JSON.stringify(error));
				$('#feedetail').html("Error");
			}
		});
	});

	$('form.rereceipt input').keyup(function(e) {
	if(e.keyCode != 13){
		var regno=$(this).val();
		if(regno.length < 8){
			$('#feedetail').html("");
			return false;
		}
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"rereceipt", "regno":regno},
			dataType:"json",
			success: function(data){
				if(data.valid)
					$('#feedetail').html("<table><tr><th width='150px'>Name</th><td>"+data.name+"</td></tr>"+
									"<tr><th>Course</th><td>"+data.course+"</td></tr>"+
									"<tr><th>Branch</th><td>"+data.branch+"</td></tr>"+
									"<tr><th>Fees</th><td>"+data.fee+"</td></tr>"+
									"<tr><td colspan=2 align='center'><input type='submit' value='Print'></td></tr>");
				else
					$('#feedetail').html(data.msg);
			}, 
			error: function(error,status,errorThrown) {
				$('#feedetail').html("Error");
			}
		});
	}});

	$('form.rereceipt').submit(function(e) {
		e.preventDefault();
		var regno=$('input:text[name=regno]').val();
		var d={"action":"reprint", "regno":regno};
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"reprint", "regno":regno},
			dataType:"json",
			success: function(data){
				if(data.success == 1)
					printReciept(data.info);
				$('#feedetail').html(data.msg);
				$('form.rereceipt input').val('');
				$('form.feecollect input').focus();
			}, 
			error: function(error,status,errorThrown) {
				alert(JSON.stringify(error));
				$('#feedetail').html("Error");
			}
		});
	});

	$('form.feecancel input').keyup(function(e) {
	if(e.keyCode != 13){
		var loginid=$(this).val();
		if(loginid.length < 5){
			$('#feedetail').html("");
			return false;
		}
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"getcancelfee", "loginid":loginid},
			dataType:"json",
			success: function(data){
				if(data.valid)
					$('#feedetail').html("<table><tr><th width='150px'>Name</th><td>"+data.name+"</td></tr>"+
									"<tr><th>Course</th><td>"+data.course+"</td></tr>"+
									"<tr><th>Branch</th><td>"+data.branch+"</td></tr>"+
									"<tr><th>Fees</th><td>"+data.fee+"</td></tr>"+
									"<tr><td colspan=2 align='center'><input type='submit' value='Confirm'></td></tr>");
				else
					$('#feedetail').html(data.msg);
			}, 
			error: function(error,status,errorThrown) {
				$('#feedetail').html("Error");
			}
		});
	}});

	$('form.feecancel').submit(function(e) {
		e.preventDefault();
		var loginid=$('form.feecancel input').val();
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"confirmcancelfee", "loginid":loginid},
			dataType:"json",
			success: function(data){
				$('#feedetail').html(data.msg);
				$('form.feecancel input').val('');
				$('form.feecancel input').focus();
			}, 
			error: function(error,status,errorThrown) {
				$('#feedetail').html("Error");
			}
		});
	});

	$('td#bookedit').click(function() {
		var ele = $(this);
		if($(this).text() == 'Edit'){
			$(this).siblings().each(function(index, element) {
				$(this).html("<input type='text' value = '" + $(this).text() + "'style='width:" + $(this).width() + "px'>");
			});
			ele.html('Save');
		}
		else if($(this).text() == 'Save'){
			var content = new Array(5);
			$(this).siblings().each(function(index, element) {
				content[index] = $(this).children('input').val();
			});
			content[4] = $(this).parent('tr').attr('id');
			$.ajax({
				url:"ajax",
				type:"POST",
				data:{"action":"bookedit", "bookno":content[4], "title":content[0], "author":content[1], "cat":content[2], "copies":content[3]},
				dataType:"json",
				success: function(data){
					if(data.success == 1){
						ele.siblings().each(function(index, element) {
							$(this).html($(this).children('input').val());
						});
						ele.html('Edit');
					}
					else
						alert('Error !!! Check Data');
				}, 
				error: function(error,status,errorThrown) {
					alert('Error!!! Retry');
				}
			});
		}
	});
	
	$('.bookadd').click(function() {
		var title = prompt("Title");
		var author = prompt("Author");
		var copies = prompt("Total Copies");
		var cat = prompt("Class No.");
		var ele = $(this);
		$.ajax({
			url:"ajax",
			type:"POST",
			data:{"action":"bookadd","bookno":$(this).attr('name'),"title":title,"author":author,"copies":copies,"cat":cat},
			dataType:"json",
			success: function(data){
				alert("Book Added");
				ele.attr('name',data.bookno);
			},
			error: function(error,status,errorThrown) {
				alert(JSON.stringify(error));
				alert('Error!!! Retry');
			}
		});
	});

	$('.booklist').click(function() {
		$.post("ajax",{"action":"booklist","prog":$(this).attr('name')},
			function(data,status){
				if(status=="success"){
					var popupWin = window.open('', '_blank', 'width=800,height=600');
					popupWin.document.open();
					popupWin.document.write("<html><title>TextBook Lending Bank | Central Library | MNNIT Allahabad</title>" +
						"<body onLoad='window.print()'>" + data + "</body></html>");
					popupWin.document.close();
				}
			}
		);
	});
	
	$('.stats').click(function() {
		var popupWin = window.open('', '_blank', 'width=800,height=600');
		popupWin.document.open();
		popupWin.document.write("<html><title>TextBook Lending Bank | Central Library | MNNIT Allahabad</title>"+
			"<body onLoad='window.print()'>" +
			"<p align='center'>"+
			"<b><font color='#000000' size='3'>Motilal Nehru National Institute of Technology<br>Deemed University - Allahabad, India.</font></b><br>"+
			"<b><font size='2' face='Georgia Ref'>Central Library</font></b><br><b>Text Book Lending Bank - 2014-15(Odd Sem)</b></p>"
			+ $('#print').html() + "</body></html>");
		popupWin.document.close();
	});
	
	$('input[name="prog"]').change(function(e) {
		$('tr.sem').css('display','');
		$('tr.bra').css('display','');
		$('tr.printbtn').css('display','');
		$('td.sem').css('display','none');
		$('td.bra').css('display','none');
		$('td#' + $(this).val()).css('display','');
	});
	
	$('.tokenlist').click(function() {
		var prog = $('input[name="prog"]:checked').val();
		var sem = "";
		if($('input[name="'+prog+'-sem"]:checked').length==0){
			alert("Select atleast 1 semester");
			return;
		}
		if($('input[name="'+prog+'-bra"]:checked').length==0){
			alert("Select atleast 1 branch");
			return;
		}
		$('input[name="'+prog+'-sem"]:checked').each(function() {
			sem = sem + $(this).val() + ',';
		});
		var bra = "";
		$('input[name="'+prog+'-bra"]:checked').each(function() {
			bra = bra + $(this).val() + ',';
		});
		$.post("ajax",{"action":"tokenlist","prog":prog, "sem":sem, "bra":bra},
			function(data,status){
				if(status=="success"){
					var popupWin = window.open('', '_blank', 'width=500,height=500');
					popupWin.document.open();
					popupWin.document.write("<html><title>TextBook Lending Bank | Central Library | MNNIT Allahabad</title>" +
						"<body onLoad='window.print()'>" + data + "</body></html>");
					popupWin.document.close();
				}
			}
		);
	});
	
	$('.tokens').click(function() {
		var prog = $('input[name="prog"]:checked').val();
		var sem = "";
		if($('input[name="'+prog+'-sem"]:checked').length==0){
			alert("Select atleast 1 semester");
			return;
		}
		if($('input[name="'+prog+'-bra"]:checked').length==0){
			alert("Select atleast 1 branch");
			return;
		}
		$('input[name="'+prog+'-sem"]:checked').each(function() {
			sem = sem + $(this).val() + ',';
		});
		var bra = "";
		$('input[name="'+prog+'-bra"]:checked').each(function() {
			bra = bra + $(this).val() + ',';
		});
		$.post("ajax",{"action":"tokens","prog":prog, "sem":sem, "bra":bra},
			function(data,status){
				if(status=="success"){
					var popupWin = window.open('', '_blank', 'width=500,height=500');
					popupWin.document.open();
					popupWin.document.write("<html><title>TextBook Lending Bank | Central Library | MNNIT Allahabad</title>" +
						"<body onLoad='window.print()'>" + data + "</body></html>");
					popupWin.document.close();
				}
			}
		);
	});
	
	$('.cancelprint').click(function() {
		var popupWin = window.open('', '_blank', 'width=800,height=600');
		popupWin.document.open();
		popupWin.document.write("<html><title>TextBook Lending Bank | Central Library | MNNIT Allahabad</title>"+
			"<body onLoad='window.print()'>" +
			"<p align='center'>"+
			"<b><font color='#000000' size='3'>Motilal Nehru National Institute of Technology<br>Deemed University - Allahabad, India.</font></b><br>"+
			"<b><font size='2' face='Georgia Ref'>Central Library</font></b><br><b>Text Book Lending Bank - 2014-15(old sem.)</b></p>"
			+ $('#printcancel').html() + "</body></html>");
		popupWin.document.close();
	});
});

function printReciept(info){
	var popupWin = window.open('', '_blank', 'width=800,height=800');
	popupWin.document.open();
	popupWin.document.write("<html><body onLoad='window.print()'>" + 
		"<table class='receipt' width='800' align='left'>"+
		"<tr>"+
			"<td width='733'><p align='center'>"+
			"<b><font color='#000000' size='3'>Motilal Nehru National Institute of Technology<br>Deemed University - Allahabad, India."+
			"</font></b><br>"+
			"<b><font size='2' face='Georgia Ref'>Central Library</font></b><br><b>Text Book Lending Bank : 2014-15(ODD Sem)</b></p>"+
			"<table width='100%' style='font:12px'>"+
			"<tr><th width='20%'>Receipt. No:</th>"+
				"<td width='30%'>" + info.rec_no + "</td>"+
				"<th width='19%' align='left'>Date</th>"+
				"<td width='30%'>" + info.date + "</td></tr>"+
			"<tr><td height='26'><b>Login Id:</b></td>"+
				"<td>" + info.loginid + "</td>"+
				"<td height='25'><b>Password:</b></td>"+
				"<td><strong>" + info.password + "</strong>&nbsp;</td>"+
			"<tr><td height='26'><b>Name:</b></td>"+
				"<td>" + info.name +"</td>"+
				"<th align='left'>Registration No:</th>"+
				"<td>" + info.regno + "</td></tr>"+
			"<tr><td height='25'><b>Course:</b></td>"+
				"<td>" + info.course + "</td>"+
				"<td align='left'><b>Branch:</b></td>"+
				"<td>" + info.branch + "</td></tr>"+
			"<tr><td height='24'><b>Amount:</b></td>"+
				"<td>Rs. " + info.amount + "/-</td>"+
				"<td>&nbsp;</td>"+
				"<td><p align='center'><b>" + info.collected_by + "</b></p></td></tr>"+
			"</table>"+
			"<p align='center'><small>Login to http://172.31.57.51 for filling the Online Choice Filling and use the Password given above.</small></p>"+
		"<center><strong>In case of any discrepancy, Contact :<br/></strong></center>"+
		"<table style='width:80%;margin:auto'><tr><td>Aayushee Aggrawal</td><td>Neeraj Kumar</td></tr>"+
						"<tr><td>MCA 2nd Yr. </td><td>B.Tech Final Yr. CS </td></tr>"+
						"<tr><td>aayusheeagg35@gmail.com</td><td>neerajarya08243@gmail.com</td></tr></table>"+
		"</html>");
	popupWin.document.close(); 
}

function refreshChoices(){
	$.post("ajax",{"action":"getchoices"},
		function(data,status){
			if(status=="success"){
				$('#choicelist').html(data);
				$('#choicelist').sortable().bind('sortupdate', function() {
					var data={"action":"updatechoice"};
					$('#choicelist li').each(function(index, element) {
						data[index]=$(this).attr('id');
					});
					$.post("ajax",data,
						function(data,success){
							$('#msg').html(data);
						}
					);
				});
				$('ul#choicelist li img#removeimg').bind('click',function(e) {
					$.post("ajax",{"action":"removechoice","bookid":$(this).parent('li').attr('id')},
						function(data,success){
							if(status=="success"){
								$('#msg').html(data);
								refreshChoices();
								$('input[name=searchbook]').trigger('keyup');
							}
						}
					);
				});
			}
		}
	);
}
