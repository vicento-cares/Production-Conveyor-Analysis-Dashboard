<?php 
session_name("pcad");
session_start();

include '../conn/pcad.php';

$method = $_POST['method'];

// ST Masterlist

function count_st_list($search_arr, $conn_pcad) {
	$query = "SELECT count(id) AS total FROM m_st WHERE";
	if (!empty($search_arr['parts_name'])) {
		$query = $query . " parts_name LIKE '".$search_arr['parts_name']."%'";
	} else {
		$query = $query . " parts_name != ''";
	}
	if (!empty($search_arr['st'])) {
		$query = $query . " AND st LIKE '".$search_arr['st']."%'";
	}

	$stmt = $conn_pcad->prepare($query);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		foreach($stmt->fetchALL() as $j){
			$total = $j['total'];
		}
	}else{
		$total = 0;
	}
	return $total;
}

if ($method == 'count_st_list') {
	$parts_name = addslashes($_POST['parts_name']);
	$st = addslashes($_POST['st']);
	
	$search_arr = array(
		"parts_name" => $parts_name,
		"st" => $st
	);

	echo count_st_list($search_arr, $conn_pcad);
}

if ($method == 'st_list_last_page') {
	$parts_name = addslashes($_POST['parts_name']);
	$st = addslashes($_POST['st']);

	$search_arr = array(
		"parts_name" => $parts_name,
		"st" => $st
	);

	$results_per_page = 20;

	$number_of_result = intval(count_st_list($search_arr, $conn_pcad));

	//determine the total number of pages available  
	$number_of_page = ceil($number_of_result / $results_per_page);

	echo $number_of_page;

}

if ($method == 'st_list') {
	$parts_name = addslashes($_POST['parts_name']);
	$st = addslashes($_POST['st']);

	$current_page = intval($_POST['current_page']);
	$c = 0;

	$results_per_page = 20;

	//determine the sql LIMIT starting number for the results on the displaying page
	$page_first_result = ($current_page-1) * $results_per_page;

	$c = $page_first_result;

	$query = "SELECT id, parts_name, st, date_updated FROM m_st WHERE";
	if (!empty($parts_name)) {
		$query = $query . " parts_name LIKE '".$parts_name."%'";
	} else {
		$query = $query . " parts_name != ''";
	}
	if (!empty($st)) {
		$query = $query . " AND st LIKE '$st%'";
	}

	$query = $query . " LIMIT ".$page_first_result.", ".$results_per_page;
	
	$stmt = $conn_pcad->prepare($query);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		foreach($stmt->fetchALL() as $j){
			$c++;
			echo '<tr>';
			echo '<td><p class="mb-0"><label class="mb-0"><input type="checkbox" class="singleCheck" value="'.$j['id'].'" onclick="get_checked_length()" /><span></span></label></p></td>';
			echo '<td style="cursor:pointer;" class="modal-trigger" data-toggle="modal" data-target="#update_st" onclick="get_st_details(&quot;'.$j['id'].'~!~'.$j['parts_name'].'~!~'.$j['st'].'&quot;)">'.$c.'</td>';
			echo '<td>'.$j['parts_name'].'</td>';
			echo '<td>'.$j['st'].'</td>';
			echo '<td>'.$j['date_updated'].'</td>';
			echo '</tr>';
		}
	}else{
		echo '<tr>';
		echo '<td colspan="5" style="text-align:center; color:red;">No Result !!!</td>';
		echo '</tr>';
	}
}

if ($method == 'get_employee_data') {
	$parts_name = addslashes($_POST['parts_name']);
	$response_arr = array();
	$st = '';

	$query = "SELECT parts_name, st FROM m_st WHERE parts_name = '$parts_name'";
	$stmt = $conn_pcad->prepare($query);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		foreach($stmt->fetchALL() as $j){
			$parts_name = $j['parts_name'];
			$st = $j['st'];
		}
		$message = 'success';
	} else {
		$message = 'Not Found';
	}

	$response_arr = array(
		'parts_name' => $parts_name,
		'st' => $st,
		'message' => $message
	);

	//header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response_arr, JSON_FORCE_OBJECT);
}

if ($method == 'add_st') {
	$st = addslashes(trim($_POST['st']));
	$parts_name = addslashes(trim($_POST['parts_name']));

	$check = "SELECT id FROM m_st WHERE parts_name = '$parts_name'";
	$stmt = $conn_pcad->prepare($check);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		echo 'Already Exist';
	}else{
		$stmt = NULL;

		$query = "INSERT INTO m_st (`parts_name`, `st`, `updated_by_no`, `updated_by`) VALUES ('$parts_name','$st','".$_SESSION['emp_no']."','".$_SESSION['full_name']."')";

		$stmt = $conn_pcad->prepare($query);
		if ($stmt->execute()) {
			echo 'success';
		}else{
			echo 'error';
		}
	}
}

if ($method == 'update_st') {
	$id = $_POST['id'];
	$parts_name = addslashes(trim($_POST['parts_name']));
	$st = addslashes(trim($_POST['st']));

	$check = "SELECT id FROM m_st WHERE parts_name = '$parts_name'";
	$stmt = $conn_pcad->prepare($check);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$query = "UPDATE m_st SET st = '$st', updated_by_no = '".$_SESSION['emp_no']."', updated_by = '".$_SESSION['full_name']."' WHERE id = '$id'";

		$stmt = $conn_pcad->prepare($query);
		if ($stmt->execute()) {
			echo 'success';
		}else{
			echo 'error';
		}
	}else{
		$query = "UPDATE m_st SET parts_name = '$parts_name', st = '$st', updated_by_no = '".$_SESSION['emp_no']."', updated_by = '".$_SESSION['full_name']."' WHERE id = '$id'";

		$stmt = $conn_pcad->prepare($query);
		if ($stmt->execute()) {
			echo 'success';
		}else{
			echo 'error';
		}
	}
}

if ($method == 'delete_st') {
	$id = $_POST['id'];

	$query = "DELETE FROM m_st WHERE id = '$id'";
	$stmt = $conn_pcad->prepare($query);
	if ($stmt->execute()) {
		echo 'success';
	}else{
		echo 'error';
	}
}

if ($method == 'delete_st_selected') {
	$id_arr = [];
	$id_arr = $_POST['id_arr'];
	$count = 0;

	foreach ($id_arr as $id) {
		$query = "DELETE FROM m_st WHERE id='$id'";
		$stmt = $conn_pcad->prepare($query);
		if (!$stmt->execute()) {
			$count++;
		}
	}

	if ($count == 0) {
		echo 'success';
	} else {
		echo "error";
	}
}

$conn_pcad = NULL;
?>