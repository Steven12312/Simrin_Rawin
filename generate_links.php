<?php
// Script to generate guest hashes and links
// You can use this to generate the list for your WhatsApp messages

function generateHash($name) {
    return md5($name . microtime());
}

// Example data from your list
$guests = [
    [
        'salutation_1' => 'Mr.', 'first_name_1' => 'Manoj', 'last_name_1' => 'Tchanra',
        'salutation_2' => 'Mrs.', 'first_name_2' => 'Sapna', 'last_name_2' => 'Tchanra',
        'days' => 3, 'family' => ['Kind 1', 'Kind 2']
    ],
    [
        'salutation_1' => 'Ms.', 'first_name_1' => 'Jane', 'last_name_1' => 'Smith',
        'salutation_2' => '', 'first_name_2' => '', 'last_name_2' => '',
        'days' => 1, 'family' => []
    ],
];

echo "<h3>Guest Invitations Generator</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Name(s)</th><th>Days</th><th>Family</th><th>Link</th></tr>";

foreach ($guests as $g) {
    $hash = md5($g['first_name_1'] . $g['last_name_1'] . microtime());
    $baseUrl = "https://simanyashica.tchanra-digital.de/?g=";
    $link = $baseUrl . $hash;

    $displayName = $g['salutation_1'] . " " . $g['first_name_1'] . " " . $g['last_name_1'];
    if (!empty($g['first_name_2'])) {
        $displayName .= " & " . $g['salutation_2'] . " " . $g['first_name_2'] . " " . $g['last_name_2'];
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($displayName) . "</td>";
    echo "<td>" . $g['days'] . "</td>";
    echo "<td>" . implode(", ", $g['family']) . "</td>";
    echo "<td><a href='$link'>$link</a></td>";
    echo "</tr>";
}
echo "</table>";
?>
