<?php

include '../server_date_time.php';
require '../conn/pcad.php';
require '../conn/ircs.php';
require '../conn/emp_mgt.php';
require '../lib/ircs.php';
include '../lib/st.php';
include '../lib/emp_mgt.php';
include '../lib/main.php';

$method = $_GET['method'];

// PCAD

// Accounting Efficiency
// http://172.25.112.131/pcad/process/pcad/pcad_p.php?method=get_accounting_efficiency
if ($method == 'get_accounting_efficiency') {
    // Working Time X Manpower Declaration
    $day = '2024-02-02';
	$shift = 'DS';
	// $line_no = '2132';
    $line_no = '7119';
    // $day = $_GET['day'];
	// $shift = $_GET['shift'];
	// $line_no = $_GET['line_no'];
    
    // Total ST Per Line Declaration
    // $registlinename = 'DAIHATSU_30';
    $registlinename = 'SUBARU_08';
    $shift_group = 'A';
    // $registlinename = $_GET['registlinename'];
    // $shift_group = $_GET['shift_group'];
    $ircs_line_data_arr = get_ircs_line_data($registlinename, $conn_pcad);
    $final_process = $ircs_line_data_arr['final_process'];
    $ip = $ircs_line_data_arr['ip'];

    $search_arr = array(
        'day' => $day,
		'shift' => $shift,
        'shift_group' => $shift_group,
        'dept' => "",
        'section' => "",
		'line_no' => $line_no,
        'registlinename' => $registlinename,
		'final_process' => $final_process,
    	'ip' => $ip,
        'server_date_only' => $server_date_only,
        'server_date_only_yesterday' => $server_date_only_yesterday,
        'server_date_only_tomorrow' => $server_date_only_tomorrow,
        'server_time' => $server_time
    );

    $wt_x_mp_arr = get_wt_x_mp_arr($search_arr, $server_time, $conn_emp_mgt);
    $wt_x_mp = $wt_x_mp_arr['wt_x_mp'];
    $total_st_per_line = get_total_st_per_line($search_arr, $conn_ircs, $conn_pcad);
    $accounting_efficiency = compute_accounting_efficiency($total_st_per_line, $wt_x_mp);

    // echo var_dump($search_arr);
    // echo var_dump($wt_x_mp_arr);

    // $response_arr = array(
    //     'total_st_per_line' => $total_st_per_line,
	// 	'wt_x_mp' => $wt_x_mp,
    //     'accounting_efficiency' => $accounting_efficiency
    // );

    // echo var_dump($response_arr);

    echo $accounting_efficiency;
}

// Yield
// http://172.25.112.131/pcad/process/pcad/pcad_p.php?method=get_yield
if ($method == 'get_yield') {
    $input_ng = 0;
    $shift = 'DS';
    // $input_ng = $_GET['input_ng'];
    // $shift = $_GET['shift'];
    $registlinename = 'SUBARU_08';
    $shift_group = 'A';
    // $registlinename = $_GET['registlinename'];
    // $shift_group = $_GET['shift_group'];
    $ircs_line_data_arr = get_ircs_line_data($registlinename, $conn_pcad);
    $final_process = $ircs_line_data_arr['final_process'];
    $ip = $ircs_line_data_arr['ip'];

    $search_arr = array(
		'shift' => $shift,
        'shift_group' => $shift_group,
        'registlinename' => $registlinename,
		'final_process' => $final_process,
    	'ip' => $ip,
        'server_date_only' => $server_date_only,
        'server_date_only_yesterday' => $server_date_only_yesterday,
        'server_date_only_tomorrow' => $server_date_only_tomorrow,
        'server_time' => $server_time
    );

    $qa_output = count_output($search_arr, $conn_ircs);
    $yield = compute_yield($qa_output, $input_ng);

    // echo $qa_output;
    echo $yield;
}

// PPM
// http://172.25.112.131/pcad/process/pcad/pcad_p.php?method=get_ppm
if ($method == 'get_ppm') {
    $ng = 0;
    $shift = 'DS';
    // $input_ng = $_GET['input_ng'];
    // $shift = $_GET['shift'];
    $registlinename = 'SUBARU_08';
    $shift_group = 'A';
    // $registlinename = $_GET['registlinename'];
    // $shift_group = $_GET['shift_group'];
    $ircs_line_data_arr = get_ircs_line_data($registlinename, $conn_pcad);
    $final_process = $ircs_line_data_arr['final_process'];
    $ip = $ircs_line_data_arr['ip'];

    $search_arr = array(
		'shift' => $shift,
        'shift_group' => $shift_group,
        'registlinename' => $registlinename,
		'final_process' => $final_process,
    	'ip' => $ip,
        'server_date_only' => $server_date_only,
        'server_date_only_yesterday' => $server_date_only_yesterday,
        'server_date_only_tomorrow' => $server_date_only_tomorrow,
        'server_time' => $server_time
    );

    $output = count_output($search_arr, $conn_ircs);
    $ppm = compute_ppm($ng, $output);

    // echo $output;
    echo $ppm;
}

// Hourly Output
// http://172.25.112.131/pcad/process/pcad/pcad_p.php?method=get_hourly_output
if ($method == 'get_hourly_output') {
    $plan = 0;
    $working_time = 450;
    // $plan = $_GET['plan'];
    // $working_time = $_GET['working_time'];

    $hourly_output = compute_hourly_output($plan, $working_time);
    echo $hourly_output;
}

// Conveyor Speed

if ($method == 'get_conveyor_speed') {
    $taktime = 27000;
    // $taktime = $_GET['taktime'];

    $conveyor_speed = compute_converyor_speed($taktime);
    echo $conveyor_speed;
}
?>