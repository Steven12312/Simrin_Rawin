<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM guests WHERE first_name_2 IS NOT NULL AND first_name_2 != ''");
$guests = $stmt->fetchAll();

foreach ($guests as $g) {
    // If they have two people
    $sal1 = $g['salutation_1'] ?: '';
    $sal2 = $g['salutation_2'] ?: '';
    $f1 = trim($g['first_name_1']);
    $f2 = trim($g['first_name_2']);
    $l1 = trim($g['last_name_1']);
    $l2 = trim($g['last_name_2']);
    
    $newSal = trim($sal1 . ' & ' . $sal2, ' &');
    
    // Attempt to merge elegantly
    $newFirst = '';
    $newLast = '';
    
    if ($l1 === $l2 || $l2 === '') {
        $newFirst = $f1 . ' & ' . $f2;
        $newLast = $l1;
    } else {
        $newFirst = $f1 . ' ' . $l1 . ' & ' . $f2;
        $newLast = $l2;
    }

    $update = $pdo->prepare("UPDATE guests SET salutation_1 = ?, first_name_1 = ?, last_name_1 = ?, salutation_2 = NULL, first_name_2 = NULL, last_name_2 = NULL WHERE id = ?");
    $update->execute([$newSal, $newFirst, $newLast, $g['id']]);
    echo "Migrated Guest ID {$g['id']}: $newSal $newFirst $newLast\n";
}

// Clean up anyone else who just had salutation_2 but no first_name_2
$pdo->query("UPDATE guests SET salutation_2 = NULL, first_name_2 = NULL, last_name_2 = NULL");
echo "Migration complete.\n";
