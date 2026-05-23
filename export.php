<?php
session_start();
require_once 'db.php';
require_once 'guest_helpers.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die("Access denied");
}

$type = $_GET['type'] ?? 'all';
$filename = "Wedding_Guestlist_" . ucfirst($type) . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
// Add UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Column Headers
fputcsv($output, ['Name', 'Phone', 'Invitation Sent', 'Status', 'Invited People', 'Confirmed People', 'Dietary Info', 'Message']);

$query = "SELECT * FROM guests";
if ($type === 'haldi') {
    $query .= " WHERE invitation_days >= 3";
} elseif ($type === 'wedding') {
    $query .= " WHERE invitation_days >= 2";
}

$stmt = $pdo->query($query . " ORDER BY last_name_1 ASC, first_name_1 ASC");
while ($row = $stmt->fetch()) {
    $invitedList = build_guest_invited_attendees($row);
    $confirmedList = get_guest_confirmed_attendees($row);
    
    fputcsv($output, [
        build_guest_display_name($row),
        $row['phone_number'] ?? '',
        $row['invitation_sent'] ? 'Yes' : 'No',
        ucfirst($row['status']),
        implode(', ', $invitedList),
        implode(', ', $confirmedList ?: []),
        $row['dietary_info'] ?? '',
        $row['message'] ?? ''
    ]);
}

fclose($output);
exit;
