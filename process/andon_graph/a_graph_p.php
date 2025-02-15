<?php
include '../server_date_time.php';
include '../conn/andon_system.php';
include '../lib/emp_mgt.php';

$method = $_POST['method'];

if ($method == 'a_down_time') {
    $andon_line = $_POST['andon_line'];
    $shift = get_shift($server_time);

    $query = "SELECT department, machinename,
                SUM(Minute(TIMEDIFF(requestDateTime,startDateTime))) as Waiting_Time,
                SUM(Minute(TIMEDIFF(startDateTime,endDateTime))) as Fixing_Time,
                SUM(Minute(TIMEDIFF(requestDateTime,startDateTime))) + SUM(Minute(TIMEDIFF(startDateTime,endDateTime))) as Total_DT
                FROM tblhistory 
                WHERE line = '$andon_line' ";

    if ($shift == 'DS') {
        $query = $query . "AND requestDateTime BETWEEN ('$server_date_only 06:00:00') 
                            AND ('$server_date_only 17:59:59') GROUP By department,machinename";
    } else if ($shift == 'NS') {
        if ($server_time >= '06:00:00' && $server_time <= '23:59:59') {
            $query = $query . "AND requestDateTime BETWEEN ('$server_date_only 18:00:00') 
                                AND ('$server_date_only_tomorrow 05:59:59') GROUP By department,machinename";
        } else if ($server_time >= '00:00:00' && $server_time < '06:00:00') {
            $query = $query . "AND requestDateTime BETWEEN ('$server_date_only_yesterday 18:00:00') 
                                AND ('$server_date_only 05:59:59') GROUP By department,machinename";
        }
    }

    $stmt = $conn_andon->prepare($query);
    $stmt->execute();

    $data = [];
    foreach ($stmt->fetchALL(PDO::FETCH_ASSOC) as $row) {
        $data[] = $row;
    }
    // Fetch data from the second table (table2)
    echo json_encode($data);
}

if ($method == 'andon_detail') {
    $c = 0;
    $andon_line = $_POST['andon_line'];
    $shift = $_POST['shift'];

    $opt = $_POST['opt'];

    $andon_hourly_date = $_POST['server_date_only'];
    $andon_hourly_date_tomorrow = date('Y-m-d',(strtotime('+1 day',strtotime($andon_hourly_date))));

    $start_date = '';
    $end_date = '';
    $start_time_ds = ' 06:00:00';
    $end_time_ds = ' 17:59:59';
    $start_time_ns = ' 18:00:00';
    $end_time_ns = ' 05:59:59';

    if ($opt == 2) {
        if ($shift == 'DS') {
            $start_date = $andon_hourly_date . $start_time_ds;
            $end_date = $andon_hourly_date . $end_time_ds;
        } else if ($shift == 'NS') {
            $start_date = $andon_hourly_date . $start_time_ns;
            $end_date = $andon_hourly_date_tomorrow . $end_time_ns;
        }
    } else if ($shift == 'DS') {
        $start_date = $server_date_only . $start_time_ds;
        $end_date = $server_date_only . $end_time_ds;
    } else if ($shift == 'NS') {
        if ($server_time >= '06:00:00' && $server_time <= '23:59:59') {
            $start_date = $server_date_only . $start_time_ns;
            $end_date = $server_date_only_tomorrow . $end_time_ns;
        } else if ($server_time >= '00:00:00' && $server_time < '06:00:00') {
            $start_date = $server_date_only_yesterday . $start_time_ns;
            $end_date = $server_date_only . $end_time_ns;
        }
    }

    $query = "SELECT category, line, machineName, machineNo, process, problem, operatorName, 
                requestDateTime, waitingTime, startDateTime, endDateTime, fixInterval, 
                technicianName, department, counter_measure, serial_num, jigName, circuit_location, lotNumber, productNumber, 
                fixRemarks, backupRequestTime, backupComment, backupTechnicianName, backupRequestTime 
                FROM tblhistory 
                WHERE line = '$andon_line' 
                AND requestDateTime BETWEEN ('$start_date') AND ('$end_date')";

    $stmt = $conn_andon->prepare($query);
    $stmt->execute();

    foreach ($stmt->fetchAll() as $row) {
        $c++;
        echo '<tr>';
        echo '<td>' . $c . '</td>';
        echo '<td>' . $row['category'] . '</td>';
        echo '<td>' . $row['line'] . '</td>';
        echo '<td>' . $row['machineName'] . '</td>';
        echo '<td>' . $row['machineNo'] . '</td>';
        echo '<td>' . $row['process'] . '</td>';
        echo '<td>' . $row['problem'] . '</td>';
        echo '<td>' . $row['operatorName'] . '</td>';
        echo '<td>' . $row['requestDateTime'] . '</td>';
        echo '<td>' . $row['waitingTime'] . '</td>';
        echo '<td>' . $row['startDateTime'] . '</td>';
        echo '<td>' . $row['endDateTime'] . '</td>';
        echo '<td>' . $row['fixInterval'] . '</td>';
        echo '<td>' . $row['technicianName'] . '</td>';
        echo '<td>' . $row['department'] . '</td>';
        echo '<td>' . $row['counter_measure'] . '</td>';
        echo '<td>' . $row['serial_num'] . '</td>';
        echo '<td>' . $row['jigName'] . '</td>';
        echo '<td>' . $row['circuit_location'] . '</td>';
        echo '<td>' . $row['lotNumber'] . '</td>';
        echo '<td>' . $row['productNumber'] . '</td>';
        echo '<td>' . $row['fixRemarks'] . '</td>';
        echo '<td>' . $row['backupRequestTime'] . '</td>';
        echo '<td>' . $row['backupComment'] . '</td>';
        echo '<td>' . $row['backupTechnicianName'] . '</td>';
        echo '<td>' . $row['backupRequestTime'] . '</td>';
        echo '</tr>';
    }
}

$conn_andon = NULL;
