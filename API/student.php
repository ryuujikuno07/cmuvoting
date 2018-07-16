<?phpfunction insert_student($data){	require 'connection.php';	{		if ($stmt = $mysqli->prepare('INSERT INTO person_table(StudentID,first_name,middle_name,last_name,CourseID,TermID,Password) VALUES(?,?,?,?,?,?,?)')){			$stmt->bind_param("sssssss", $data[0]['Student_ID'],$data[0]['Student_FirstName'],$data[0]['Student_MiddleInitial'],$data[0]['Student_LastName'],$data[0]['Student_ProgID'],$data[0]['Student_TermID'],$data[0]['Student_Password']);			$stmt->execute();			$stmt->close();			print json_encode(array('success' =>true,'msg' =>'Record successfully saved'));		}else{			print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));		}	}}function update_student($student_id,$data){	require 'connection.php';	{		if ($stmt = $mysqli->prepare('UPDATE person_table SET StudentID=?,  first_name=?,middle_name=?,last_name=?,CourseID=?,TermID=?,Password=? WHERE StudentID= "'.$student_id.'" ')){			$stmt->bind_param("sssssss",$data[0]['Student_ID'],$data[0]['Student_FirstName'],$data[0]['Student_MiddleInitial'],$data[0]['Student_LastName'],$data[0]['Student_ProgID'],$data[0]['Student_TermID'],$data[0]['Student_Password']);			$stmt->execute();			$stmt->close();			print json_encode(array('success' =>true,'msg' =>'Record successfully updated'));		}else{			print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));		}	}}function checkactivate($StudentID,$TermID) {	require 'connection.php';	{		$query = "SELECT * FROM election_ballots WHERE StudentID ='$StudentID' AND TermID='$TermID' LIMIT 1";		if ($result = $mysqli->query($query)) {		    if($row = $result->fetch_array(MYSQLI_ASSOC)){		    	print json_encode(array('success'=>true,'msg'=>'Sorry You Voted Already!'));		    }else{		    	if($stmt = $mysqli->prepare("UPDATE person_table S INNER JOIN student_numbering N ON N.person_id = S.person_id SET S.Password = 1  WHERE N.student_number =? AND S.TermID=?;")){			$stmt->bind_param("ss", $StudentID,$TermID);			$stmt->execute();			$stmt->close();			print json_encode(array('success' =>true,'msg' =>'Student Successfully ACTIVATED.'));		}else{			print json_encode(array('success' =>false,'msg' =>'ERROR: Student NOT Successfully ACTIVATED', $mysqli->error));		}		    }		}else{			print json_encode(array('success'=>false,'msg'=>''));		}	}}function deactivate_student($student_id,$TermID){	require 'connection.php';	{		if($stmt = $mysqli->prepare("UPDATE person_table S INNER JOIN student_numbering N ON N.person_id = S.person_id SET S.Password = '0'  WHERE N.student_number=? AND S.TermID=?")){			$stmt->bind_param("ss", $student_id,$TermID);			$stmt->execute();			$stmt->close();			print json_encode(array('success' =>true,'msg' =>'Student successfully DEACTIVATED.'));		}else{			print json_encode(array('success' =>false,'msg' =>"Error message: %s\n", $mysqli->error));		}	}}function select_all_student($TermID,$page){	require 'connection.php';	{		$limit = 10;		$adjacent = 3;		if($page==1){		   $start = 0;		}else{		  $start = ($page-1)*$limit;		}		$query1="SELECT S.person_id,CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,S.last_name,S.first_name,S.middle_name,S.Password,				(SELECT course_code FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,				(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,				(SELECT curriculum_id FROM student_curriculum WHERE student_number = N.student_number LIMIT 1) AS CourseID,K.curriculum_id,				(SELECT student_number  FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id,				(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID				FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id INNER JOIN department_table E ON D.department_id = E.department_id WHERE  S.TermID='$TermID' ORDER BY Fullname;";		$result1 = $mysqli->query($query1);		$rows = $result1->num_rows;		$query="SELECT S.person_id,CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,S.last_name,S.first_name,S.middle_name,S.Password,				(SELECT course_code FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,				(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,				(SELECT curriculum_id FROM student_curriculum WHERE student_number = N.student_number LIMIT 1) AS CourseID,K.curriculum_id,				(SELECT student_number  FROM student_numbering  WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id,				(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID				FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on N.student_number = K.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id INNER JOIN department_table E ON D.department_id = E.department_id WHERE  S.TermID='$TermID' ORDER BY Fullname LIMIT $start, $limit;";		$mysqli->set_charset("utf8");		$result = $mysqli->query($query);		$data = array();		while($row = $result->fetch_array(MYSQLI_ASSOC)){			array_push($data ,$row);		}		$paging = pagination($limit,$adjacent,$rows,$page);		print json_encode(array('success' =>true,'students' =>$data,'pagination'=>$paging));	}}function get_student($student_id,$TermID){	require 'connection.php';	{		$query ="SELECT *		(SELECT student_number FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id		FROM person_table S INNER JOIN student_numbering N ON S.person_id = N.person_id WHERE N.student_number='$student_id' AND S.TermID='$TermID';";		$result = $mysqli->query($query);		if($row = $result->fetch_array(MYSQLI_ASSOC)){			print json_encode(array('success' =>true,'student' =>$row));		}else{			print json_encode(array('success' =>false,'msg' =>"No record found!"));		}	}}function search_student($value,$TermID,$page){	require 'connection.php';	{		$limit = 10;		$adjacent = 3;		if($page==1){		   $start = 0;		}else{		  $start = ($page-1)*$limit;		}		$query1="SELECT S.person_id,CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,S.last_name,S.first_name,S.middle_name,S.Password,				(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,				(SELECT student_number  FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id,				(SELECT course_code FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS CourseCode,				(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,				(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID				FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN course_table P ON C.course_id = P.course_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id WHERE S.first_name LIKE '%$value%' OR S.last_name LIKE '%$value%' OR P.course_code LIKE '%$value%' OR N.student_number LIKE '%$value%' AND  S.TermID='$TermID';";		$result1 = $mysqli->query($query1);		$rows = $result1->num_rows;		$query="SELECT S.person_id,CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,S.last_name,S.first_name,S.middle_name,S.Password,				(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,				(SELECT course_code FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS CourseCode,				(SELECT student_number  FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id,				(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,				(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID				FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN course_table P ON C.course_id = P.course_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id WHERE S.first_name LIKE '%$value%' OR S.last_name LIKE '%$value%' OR P.course_code LIKE '%$value%' OR N.student_number LIKE '%$value%' AND  S.TermID='$TermID' LIMIT $start, $limit;";		$result = $mysqli->query($query);		$data = array();		while($row = $result->fetch_array(MYSQLI_ASSOC)){			array_push($data ,$row);		}		$paging = pagination($limit,$adjacent,$rows,$page);		print json_encode(array('success' =>true,'students' =>$data,'pagination'=>$paging));	}}function login_voter($student_id,$Password){	require 'connection.php';	{		$query ="SELECT * FROM person_table S INNER JOIN student_numbering N ON S.person_id = N.student_number WHERE N.student_number=$student_id AND TermID=$TermID;";		$result = $mysqli->query($query);		if($row = $result->fetch_array(MYSQLI_ASSOC)){			print json_encode(array('success' =>true,'student' =>$row));		}else{			print json_encode(array('success' =>false,'msg' =>"No record found!"));		}	}}function generate_password(){	require 'connection.php';	{		$query ="SELECT * FROM person_table";		$result = $mysqli->query($query);		$count =0;		while($row = $result->fetch_array(MYSQLI_ASSOC)){			$sql = 'UPDATE person_table SET TermID=(SELECT TermID FROM election_configuration WHERE IsActive=1) WHERE person_id BETWEEN 1 AND 10000 = "'.$row['person_id'].'" ;';			if ($stmt = $mysqli->query($sql)){				$count = $count + 1;			}		}		if($count > 0){			print json_encode(array('success' =>true,'student' =>$count ,'msg' =>'Successfully Activated password for All students'));		}else{			print json_encode(array('success' =>false,'msg' =>"An unknown error occur while generating password"));		}	}}function generate_password2($TermID){	require 'connection.php';	{		$query ="SELECT * FROM person_table WHERE TermID=$TermID;";		$result = $mysqli->query($query);		$count =0;		while($row = $result->fetch_array(MYSQLI_ASSOC)){			$sql = 'UPDATE person_table SET Password=0 WHERE person_id BETWEEN 1 AND 10000 = "'.$row['person_id'].'" AND TermID='.$TermID.' ;';			if ($stmt = $mysqli->query($sql)){				$count = $count + 1;			}		}		if($count > 0){			print json_encode(array('success' =>true,'student' =>$count ,'msg' =>'Successfully Deactivated password for All students'));		}else{			print json_encode(array('success' =>false,'msg' =>"An unknown error occur while generating password"));		}	}}function update_password($student_id,$termID,$password){	require 'connection.php';	{		if ($stmt = $mysqli->prepare('UPDATE person_table SET Password="$password" WHERE person_id= "'.$student_id.'" AND TermID="'.$termID.'"')){			$stmt->bind_param("s",$password);			$stmt->execute();			return true;		}else{			return false;		}	}}function Print_Student($TermID,$page){	require 'connection.php';	{			$limit = 10000;			$adjacent = 3;			if($page==1){			   $start = 0;			}else{			  $start = ($page-1)*$limit;			}			$query1="SELECT S.person_id,CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,S.last_name,S.first_name,S.middle_name,					(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,					(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,					(SELECT curriculum_id FROM student_curriculum WHERE student_number = N.student_number LIMIT 1) AS CourseID,K.curriculum_id,					(SELECT student_number  FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id,					(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID					FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id WHERE S.TermID='$TermID' ORDER BY Fullname;";			$result1 = $mysqli->query($query1);			$rows = $result1->num_rows;			$query="SELECT S.person_id,CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,S.last_name,S.first_name,S.middle_name,S.Password,					(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,					(SELECT curriculum_id FROM student_curriculum WHERE student_number = N.student_number LIMIT 1) AS CourseID,K.curriculum_id,					(SELECT student_number  FROM student_numbering  WHERE person_id=S.person_id LIMIT 1) AS StudentID,S.person_id,					(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,					(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID					FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on N.student_number = K.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id WHERE S.TermID='$TermID' ORDER BY Fullname LIMIT $start, $limit;";			$mysqli->set_charset("utf8");			$result = $mysqli->query($query);			$data = array();			while($row = $result->fetch_array(MYSQLI_ASSOC)){				array_push($data ,$row);			}			$paging = pagination($limit,$adjacent,$rows,$page);			print json_encode(array('success' =>true,'students' =>$data,'pagination'=>$paging));		}	}function checkStudent($Fullname){	require 'connection.php';	{		$query ="Select * from person_table Where CONCAT(last_name,', ',first_name,' ',middle_name) LIKE '%$Fullname%';";		if($result = $mysqli->query($query)){			if($result->num_rows > 0){					print json_encode(array('success' =>true ,'msg' =>'Warning: Fullname Already Exist.'));			}else{					print json_encode(array('success' =>false));			}		}else{			print json_encode(array('success' =>false));		}	}}function fetch_student($TermID){	require 'connection.php';	{		$query="SELECT S.person_id,		(SELECT student_number FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,		CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,				(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,				(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,				(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID				FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN course_table P ON C.course_id = P.course_id INNER JOIN department_curriculum D ON K.curriculum_id = D.curriculum_id WHERE S.TermID='$TermID' ;";		$mysqli->set_charset("utf8");		$result = $mysqli->query($query);		$data = array();		while($row = $result->fetch_array(MYSQLI_ASSOC)){			array_push($data ,$row);		}		print json_encode(array('success' =>true,'students' =>$data));	}}function import_student($data){	require 'connection.php';	{		$stmt = $mysqli->prepare('INSERT INTO person_table(StudentID,first_name,middle_name,last_name,CourseID,TermID) VALUES(?,?,?,?,?,?,?)');		$mysqli->query("START TRANSACTION");		foreach ($data as $key) {				$stmt->bind_param("sssssss", $key['Student_ID'],$key['Student_FirstName'],$key['Student_MiddleInitial'],$key['Student_LastName'],$key['Student_ProgID'],$key['Student_TermID']);				$stmt->execute();		}		$stmt->close();		$mysqli->query("COMMIT");		print json_encode(array('success' =>true,'msg' =>'Record successfully saved'));		// $query= array();		// foreach ( $data as $row )    // {    //     $query[] = '("'.$row['Student_ID'].'","'.$row['Student_FirstName'].'","'.$row['Student_LastName'].'","'.$row['Student_MiddleInitial'].'","'.$row['Student_ProgID'].'","'.$row['Student_TermID'].'")';    // }		// if($mysqli->query('INSERT INTO person_table(StudentID,FirstName,LastName,MiddleName,CourseID,TermID) VALUES ' .implode(',',$query))){		// 		print json_encode(array('success' =>true,'msg' =>'Record successfully saved'));		// }	}}function getSelect2Student(){	require 'connection.php';	{		$query="SELECT S.person_id,		(SELECT student_number FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,		CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,		(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id,		(SELECT department_description FROM department_table WHERE department_id=(SELECT department_id FROM department_curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS College,D.department_id,		(SELECT CONCAT(ElectionName,' (',SchoolYear,')')  FROM election_configuration WHERE TermID =S.TermID LIMIT 1) AS ElectionTerm,S.TermID		FROM person_table S INNER JOIN student_numbering N ON N.person_id=S.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN course_table P ON C.course_id = P.course_id INNER JOIN department_curriculum D ON D.curriculum_id = K.curriculum_id;";		$mysqli->set_charset("utf8");		$result = $mysqli->query($query);		$data = array();		while($row = $result->fetch_array(MYSQLI_ASSOC)){			array_push($data ,$row);		}		print json_encode(array('success' =>true,'students' =>$data));		file_put_contents('student.json', json_encode($data));	}}function search_student2($value,$TermID){	require 'connection.php';	{		$query1="SELECT S.person_id,		(SELECT student_number  FROM student_numbering WHERE person_id=S.person_id LIMIT 1) AS StudentID,		CONCAT(IFNULL(S.last_name,''),', ',IFNULL(S.first_name,''),' ',IFNULL(S.middle_name,''),'.') AS Fullname,		(SELECT course_description FROM course_table WHERE course_id=(SELECT course_id FROM curriculum WHERE curriculum_id = K.curriculum_id) LIMIT 1) AS Course,C.course_id		FROM person_table S INNER JOIN student_numbering N ON S.person_id = N.person_id INNER JOIN student_curriculum K on K.student_number = N.student_number INNER JOIN student_status T on N.student_number = T.student_number AND T.enrolled_status = 0 INNER JOIN curriculum C ON K.curriculum_id = C.curriculum_id INNER JOIN course_table P ON C.course_id = P.course_id WHERE S.first_name LIKE '%$value%' OR S.middle_name LIKE '%$value%' OR S.last_name LIKE '%$value%' OR N.student_number LIKE '%$value%' AND  S.TermID='$TermID';";		$result = $mysqli->query($query1);		$data = array();		while($row = $result->fetch_array(MYSQLI_ASSOC)){			array_push($data ,$row);		}		print json_encode(array('students' =>$data));	}}function checkStudentID($StudentID,$TermID){	require 'connection.php';	{		$query ="SELECT * FROM person_table S INNER JOIN student_numbering N ON S.person_id = N.person_id Where N.student_number ='$StudentID' AND S.TermID ='$TermID';";		if($result = $mysqli->query($query)){			if($result->num_rows > 0){					print json_encode(array('success' =>true,'msg' =>'Warning: Student ID Already Exist.'));			}else{				print json_encode(array('success' =>false));			}		}else{			print json_encode(array('success' =>false));		}	}}?>