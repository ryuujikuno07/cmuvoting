<?php

function insert_candidate($data){
	if ($stmt = $mysqli->prepare('INSERT INTO election_transactionlog(CollegeID,CollegeCode,CollegeName,CollegeDean,InActive) VALUES(null,?,?,?,?)')){
		$stmt->bind_param("ssss", $data);
		$stmt->execute();

		print json_encode(array('success' =>true,'msg' =>'Record successfully saved'));
	}else{
		print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));
	}
}

function update_candidate($prog_id,$data){
	if ($stmt = $mysqli->prepare('UPDATE election_transactionlog SET CollegeCode=?,CollegeName=?,CollegeDean=?,InActive=? WHERE CollegeID=$prog_id')){
		$stmt->bind_param("ssss", $data);
		$stmt->execute();
		print json_encode(array('success' =>true,'msg' =>'Record successfully updated'));
	}else{
		print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));
	}
}

function delete_candidate($prog_id){
	if($stmt = $mysqli->prepare("DELETE FROM election_transactionlog WHERE CollegeID =?")){
		$stmt->bind_param("s", $prog_id);
		$stmt->execute();
		$stmt->close();
		print json_encode(array('success' =>true,'msg' =>'Record successfully deleted'));
	}else{
		print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));
	}
}

function archieve_candidate($prog_id){
	if($stmt = $mysqli->prepare("UPDATE election_transactionlog SET InActive=1 WHERE CollegeID =?")){
		$stmt->bind_param("s", $prog_id);
		$stmt->execute();
		$stmt->close();
		print json_encode(array('success' =>true,'msg' =>'Record successfully archieve'));
	}else{
		print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));
	}
}

function select_all_candidate(){
	$query ="SELECT * FROM election_transactionlog WHERE InActive <> 1;";
	$result = $mysqli->query($query);
	$data = array();
	while($row = $result->fetch_array(MYSQLI_ASSOC)){
		array_push($data2 ,$row);
	}
	print json_encode(array('success' =>true,'colleges' =>$data));
}

function get_candidate($candidate){
	$query ="SELECT * FROM election_transactionlog WHERE CollegeID=$college_id AND InActive <> 1;";
	$result = $mysqli->query($query);
	if($row = $result->fetch_array(MYSQLI_ASSOC)){
		print json_encode(array('success' =>true,'college' =>$row));
	}else{
		print json_encode(array('success' =>false,'msg' =>"No record found!"));
	}
}

function search_candidate($value){
	$query ="SELECT * FROM election_transactionlog WHERE ((CollegeCode LIKE % $value %) OR (CollegeName LIKE % $value %) AND InActive <> 1;";
	$result = $mysqli->query($query);
	while($row = $result->fetch_array(MYSQLI_ASSOC)){
		array_push($data2 ,$row);
	}
	print json_encode(array('success' =>true,'colleges' =>$data));
}

?>                       
