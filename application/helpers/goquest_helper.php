<?php 

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hr',
        'i' => 'min',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function formate_date_sql($date) {
    if (empty($date))
        return '';
    else {
        return date('Y-m-d', strtotime($date));
    }
}

function formate_date_web($date) {
    $dateC = date('Y-m-d', strtotime($date));
    if (empty($dateC) || $dateC == '1970-01-01' || $dateC == '0000-00-00')
        return '';
    else {
        return date('d M y', strtotime($date));
    }
}

function formate_datetime_web($date) {
    $datecheck = date('Y-m-d', strtotime($date));
    if (empty($datecheck) || $datecheck == '1970-01-01' || $datecheck == '0000-00-00')
        return '';
    else {
        return date('d-m-Y h:i A', strtotime($date));
    }
}
