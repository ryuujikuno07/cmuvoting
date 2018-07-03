function isAPIAvailable(){return window.File&&window.FileReader&&window.FileList&&window.Blob?!0:(document.writeln("The HTML5 APIs used in this form are only available in the following browsers:<br />"),document.writeln(" - Google Chrome: 13.0 or later<br />"),document.writeln(" - Mozilla Firefox: 6.0 or later<br />"),document.writeln(" - Internet Explorer: Not supported (partial support expected in 10.0)<br />"),document.writeln(" - Safari: Not supported<br />"),document.writeln(" - Opera: Not supported"),!1)}function handleFileSelect(e){var t=e.target.files,o=t[0];$("#list").empty();var n="";n+='<span style="font-weight:bold;">'+escape(o.name)+"</span><br />\n",n+=" - FileType: "+(o.type||"n/a")+"<br />\n",n+=" - FileSize: "+o.size+" bytes<br />\n",n+=" - LastModified: "+(o.lastModifiedDate?o.lastModifiedDate.toLocaleDateString():"n/a")+"<br />\n",printTable(o),$("#list").append(n)}function printTable(e){var t=new FileReader;t.readAsText(e),$("#CSVTable").html(""),t.onload=function(e){var t=e.target.result,o=$.csv.toArrays(t),n="";for(var r in o){n+="<tr>\r\n";for(var i in o[r])n+="<td>"+o[r][i]+"</td>\r\n";n+="</tr>\r\n"}$("#CSVTable").html(n)},t.onerror=function(){alert("Unable to read "+e.fileName)}}function select2_config(){var e=sessionStorage.getItem("ipaddress");$.ajax({url:"http://"+e+"/cmuvoting/API/index.php",async:!1,type:"POST",crossDomain:!0,dataType:"json",data:{command:"select2_config"},success:function(e){var t=e;$("#cboElectionTerm").empty();for(var o=0;o<t.configs.length;o++){var n=t.configs,r='<option id="'+n[o].TermID+'" value="'+n[o].TermID+'">'+n[o].Config+"</option>";$("#cboElectionTerm").append(r)}},error:function(e){console.log("Error:"),console.log(e.responseText),console.log(e.message)}})}function import_file(){if(null==$("#cboElectionTerm").val())return void alert("WARNING: Unable to import student data. Election Term is required.");$("#processing-modal").modal("show");var e=new Array;$("#CSVTable tbody tr").not(":first").each(function(){var t=$(this).children("td");student={Student_ID:t.eq(0).text(),Student_FirstName:t.eq(1).text(),Student_LastName:t.eq(2).text(),Student_MiddleInitial:t.eq(3).text(),Student_Gender:t.eq(4).text(),Student_ProgID:t.eq(5).text(),Student_TermID:$("#cboElectionTerm").val()},e.push(student)}),console.log(e);var t=sessionStorage.getItem("ipaddress");$.post("http://"+t+"/cmuvoting/API/index.php",{command:"import_student",data:e},function(e){var t=e;console.log(t),1==t.success&&(alert(t.msg),console.log(t.msg),$("#processing-modal").modal("hide"),window.location.href="student-list.php")})}$(document).ready(function(){$("#CSVTable tbody > tr").remove(),isAPIAvailable()&&$("#file_import").on("change",handleFileSelect),select2_config()});