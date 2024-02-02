<?php

// ST (PCAD) Functions
// Must Require Database Config "../conn/pcad.php" before using this functions
// Must Require Database Config "../conn/ircs.php" before using this functions

// ST Data
function get_st_data($parts_name, $conn_pcad) {
	$parts_name = addslashes($parts_name);
	$st = 0;
	$response_arr = array();
	if (!empty($parts_name)) {
		$query = "SELECT parts_name, st FROM m_st WHERE parts_name LIKE '$parts_name%'";
		$stmt = $conn_pcad->prepare($query);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			foreach($stmt->fetchALL() as $row){
				$parts_name = $row['parts_name'];
				$st = floatval($row['st']);
			}
		}
	}
	$response_arr = array(
		'parts_name' => $parts_name,
		'st' => $st
	);
	return $response_arr;
}

// Total ST Per Line Computation
function get_total_st_per_line($search_arr, $conn_ircs, $conn_pcad) {
    // $registlinename = addslashes($search_arr['registlinename']);
    // $final_process = addslashes($search_arr['final_process']);
    // $ip = addslashes($search_arr['ip']);
    $st_per_product = 0;
    $st_per_product_arr = array();
    
    $query = "SELECT PARTSNAME, COUNT(REGISTLINENAME) AS OUTPUT 
            FROM T_PRODUCTWK WHERE REGISTLINENAME = 'SUBARU_08' 
            AND REGISTDATETIME BETWEEN TO_DATE('2024-01-31 05:59:00', 'yyyy-MM-dd HH24:MI:SS') 
            AND TO_DATE('2024-02-01 05:59:59', 'yyyy-MM-dd HH24:MI:SS') 
            AND INSPECTION3IPADDRESS = '172.25.167.226'
            GROUP BY PARTSNAME, REGISTLINENAME";
    // $query = "SELECT PARTSNAME, COUNT(REGISTLINENAME) AS OUTPUT 
    //         FROM T_PRODUCTWK WHERE REGISTLINENAME = '$registlinename' 
    //         AND REGISTDATETIME BETWEEN TO_DATE('2024-01-31 05:59:00', 'yyyy-MM-dd HH24:MI:SS') 
    //         AND TO_DATE('2024-02-01 05:59:59', 'yyyy-MM-dd HH24:MI:SS')";
    // if ($final_process == 'QA') {
    //     $query = $query . "AND INSPECTION4IPADDRESS = '$ip'";
    // } else {
    //     $query = $query . "AND INSPECTION3IPADDRESS = '$ip'";
    // }
    // $query = $query . "GROUP BY PARTSNAME, REGISTLINENAME";

    $stmt = oci_parse($conn_ircs, $query);
	oci_execute($stmt);
	while ($row = oci_fetch_object($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
        $parts_name = $row->PARTSNAME;
        $output = floatval($row->OUTPUT);

        $st_data_arr = get_st_data($parts_name, $conn_pcad);
        $st = floatval($st_data_arr['st']);

        // ST Per Product Formula
        $st_per_product = $output * $st;

        array_push($st_per_product_arr, $st_per_product);
    }

    // Total ST Per Line Computation
    $total_st_per_line = array_sum($st_per_product_arr);

    return $total_st_per_line;
}
?>