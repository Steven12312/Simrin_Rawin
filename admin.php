<?php
session_start();
require_once 'db.php';
require_once 'guest_helpers.php';

$authError = null;
$userCount = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$setupMode = $userCount === 0;
$legacyDefaultHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$legacyDefaultUser = null;
$forcePasswordReset = false;

if (!$setupMode) {
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1");
    $stmt->execute(['admin']);
    $legacyDefaultUser = $stmt->fetch();
    $forcePasswordReset = $legacyDefaultUser && hash_equals($legacyDefaultHash, $legacyDefaultUser['password_hash']);
}

if ($setupMode && isset($_POST['setup_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        $authError = "Please choose a username with at least 3 characters.";
    } elseif (strlen($password) < 12) {
        $authError = "Please use a password with at least 12 characters.";
    } elseif ($password !== $confirmPassword) {
        $authError = "The passwords do not match.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php?setup=1");
        exit;
    }
}

if ($forcePasswordReset && isset($_POST['reset_default_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        $authError = "Please choose a username with at least 3 characters.";
    } elseif (strlen($password) < 12) {
        $authError = "Please use a password with at least 12 characters.";
    } elseif ($password !== $confirmPassword) {
        $authError = "The passwords do not match.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $legacyDefaultUser['id']]);
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php?password_reset=1");
        exit;
    }
}

if (!$setupMode && !$forcePasswordReset && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $authError = "Invalid credentials";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if ($setupMode || $forcePasswordReset || !isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Login - Engagement</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body { display: flex; align-items: center; justify-content: center; height: 100vh; background: #f0f2f5; }
            .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
            input { margin-bottom: 20px; }
            .helper-text { font-size: 0.9rem; color: #666; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <?php if ($setupMode): ?>
            <h2 class="serif">Create First Admin</h2>
            <p class="helper-text">Set up the first admin account before you share access to this dashboard.</p>
            <?php elseif ($forcePasswordReset): ?>
            <h2 class="serif">Security Update Required</h2>
            <p class="helper-text">The old default admin password is still active. Please replace it before continuing.</p>
            <?php else: ?>
            <h2 class="serif">Admin Login</h2>
            <?php endif; ?>
            <?php if ($authError): ?>
            <p style="color: red;"><?php echo htmlspecialchars($authError); ?></p>
            <?php endif; ?>
            <?php if ($setupMode): ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" minlength="3" required>
                <input type="password" name="password" placeholder="Password (min. 12 characters)" minlength="12" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" minlength="12" required>
                <button type="submit" name="setup_admin" class="btn-luxury">Create Admin Account</button>
            </form>
            <?php elseif ($forcePasswordReset): ?>
            <form method="POST">
                <input type="text" name="username" placeholder="New Username" minlength="3" required>
                <input type="password" name="password" placeholder="New Password (min. 12 characters)" minlength="12" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" minlength="12" required>
                <button type="submit" name="reset_default_admin" class="btn-luxury">Update Admin Login</button>
            </form>
            <?php else: ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn-luxury">Login</button>
            </form>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}


// One-time data migration for Person 2
try {
    $stmt_mig = $pdo->query("SELECT id, salutation_1, first_name_1, last_name_1, salutation_2, first_name_2, last_name_2 FROM guests WHERE first_name_2 IS NOT NULL AND first_name_2 != ''");
    $guestsToMigrate = $stmt_mig->fetchAll();
    if (count($guestsToMigrate) > 0) {
        foreach ($guestsToMigrate as $g_mig) {
            $sal1 = $g_mig['salutation_1'] ?: '';
            $sal2 = $g_mig['salutation_2'] ?: '';
            $f1 = trim($g_mig['first_name_1'] ?? '');
            $f2 = trim($g_mig['first_name_2'] ?? '');
            $l1 = trim($g_mig['last_name_1'] ?? '');
            $l2 = trim($g_mig['last_name_2'] ?? '');
            $newSal = trim($sal1 . ' & ' . $sal2, ' &');
            $newFirst = ($l1 === $l2 || $l2 === '') ? ($f1 . ' & ' . $f2) : ($f1 . ' ' . $l1 . ' & ' . $f2);
            $newLast = ($l1 === $l2 || $l2 === '') ? $l1 : $l2;
            
            $pdo->prepare("UPDATE guests SET salutation_1 = ?, first_name_1 = ?, last_name_1 = ?, salutation_2 = NULL, first_name_2 = NULL, last_name_2 = NULL WHERE id = ?")
                ->execute([$newSal, $newFirst, $newLast, $g_mig['id']]);
        }
    }
    $pdo->query("UPDATE guests SET salutation_2 = NULL, first_name_2 = NULL, last_name_2 = NULL");
} catch (Exception $e) {}

// Toggle Invitation Sent Status
if (isset($_GET['toggle_sent'])) {
    $id = (int)$_GET['toggle_sent'];
    $stmt = $pdo->prepare("UPDATE guests SET invitation_sent = 1 - invitation_sent WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Add Guest Logic
if (isset($_POST['add_guest'])) {
    $hash = bin2hex(random_bytes(16));
    $familyRaw = $_POST['family_members'] ?? '';
    $familyMembers = decode_guest_list($familyRaw);
    $familyMembersJson = !empty($familyMembers) ? json_encode($familyMembers, JSON_UNESCAPED_UNICODE) : null;
    $with_family = isset($_POST['with_family']) ? (int)$_POST['with_family'] : 0;
    

    $phone = normalize_guest_value($_POST['phone_number'] ?? '');

    $status = $_POST['status'] ?? 'pending';

    $stmt = $pdo->prepare("INSERT INTO guests (guest_hash, salutation_1, first_name_1, last_name_1, salutation_2, first_name_2, last_name_2, phone_number, invitation_days, family_members, with_family, status) 
                           VALUES (?, ?, ?, ?, NULL, NULL, NULL, ?, 1, ?, ?, ?)");
    $stmt->execute([
        $hash,
        normalize_guest_value($_POST['salutation_1'] ?? ''),
        normalize_guest_value($_POST['first_name_1'] ?? ''),
        normalize_guest_value($_POST['last_name_1'] ?? ''),
        $phone,
        $familyMembersJson,
        $with_family,
        $status
    ]);
    header("Location: admin.php?success=1");
    exit;
}

// Update Guest Logic
if (isset($_POST['update_guest'])) {
    $guestId = (int)$_POST['guest_id'];
    $familyRaw = $_POST['family_members'] ?? '';
    $familyMembers = decode_guest_list($familyRaw);
    $familyMembersJson = !empty($familyMembers) ? json_encode($familyMembers, JSON_UNESCAPED_UNICODE) : null;
    $with_family = isset($_POST['with_family']) ? (int)$_POST['with_family'] : 0;
    
    $phone = normalize_guest_value($_POST['phone_number'] ?? '');

    $status = $_POST['status'] ?? 'pending';

    $stmt = $pdo->prepare("UPDATE guests SET salutation_1 = ?, first_name_1 = ?, last_name_1 = ?, salutation_2 = NULL, first_name_2 = NULL, last_name_2 = NULL, phone_number = ?, invitation_days = 1, family_members = ?, with_family = ?, status = ? WHERE id = ?");
    $stmt->execute([
        normalize_guest_value($_POST['salutation_1'] ?? ''),
        normalize_guest_value($_POST['first_name_1'] ?? ''),
        normalize_guest_value($_POST['last_name_1'] ?? ''),
        $phone,
        $familyMembersJson,
        $with_family,
        $status,
        $guestId
    ]);
    header("Location: admin.php?updated=1");
    exit;
}

// Delete Guest
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM guests WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit;
}

// Load Guest for Editing
$editingGuest = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editingGuest = $stmt->fetch();
}

// Statistics Calculation in PHP
$all_guests_for_stats = $pdo->query("SELECT * FROM guests")->fetchAll();

$ts = [
    'total_invited' => 0,
    'total_accepted' => 0,
    'total_declined' => 0,
    'total_pending' => 0,
    'total_invitations_sent' => 0,
    'total_invitations' => 0
];

// Re-fetch all to count
$all_guests = $pdo->query("SELECT * FROM guests")->fetchAll();
$ts['total_invitations'] = count($all_guests);
foreach ($all_guests as $g) {
    if ($g['invitation_sent']) $ts['total_invitations_sent']++;
}

foreach ($all_guests_for_stats as $g) {
    $ppl_in_invite = count_guest_invited_people($g);

    $ts['total_invited'] += $ppl_in_invite;

    if ($g['status'] === 'accepted') {
        $ppl_accepted = count_guest_confirmed_people($g);
        $ts['total_accepted'] += $ppl_accepted;
    } elseif ($g['status'] === 'declined') {
        $ts['total_declined'] += $ppl_in_invite;
    } else {
        $ts['total_pending'] += $ppl_in_invite;
    }
}

// Guest List with Filter
$query = "SELECT * FROM guests";
$params = [];

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$guests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Engagement</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #8e1c1c; --secondary: #b8860b; }
        body { background: #f8f9fa; padding: 20px; font-family: 'Outfit', sans-serif; }
        .admin-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid var(--secondary); }
        .stat-card h4 { margin: 0; font-size: 0.9rem; color: #666; text-transform: uppercase; }
        .stat-card .value { font-size: 1.8rem; font-weight: 700; color: var(--primary); margin: 5px 0; }
        
        .admin-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .guest-row { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; }
        .guest-row:last-child { border-bottom: none; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
        .status-pending { background: #eee; color: #666; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-declined { background: #f8d7da; color: #721c24; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .copy-btn { background: #eee; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 0.8rem; }
        .copy-btn:hover { background: #ddd; }
    </style>
</head>
<body>

    <div class="container">
        <header class="admin-nav">
            <h1 class="serif" style="margin: 0; font-size: 2rem;">Admin Dashboard</h1>
            <a href="?logout=1" style="color: grey;">Logout</a>
        </header>
        <?php if (isset($_GET['setup'])): ?>
        <p style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;">Admin account created successfully.</p>
        <?php endif; ?>
        <?php if (isset($_GET['password_reset'])): ?>
        <p style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px;">Default admin credentials have been replaced successfully.</p>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Persons</h4>
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <div class="value"><?php echo $ts['total_accepted']; ?></div>
                    <div style="color: #666; font-size: 0.9rem;">/ <?php echo $ts['total_invited']; ?> Total</div>
                </div>
                <small style="color: green;">✔ <?php echo $ts['total_accepted']; ?> Accepted</small><br>
                <small style="color: red;">✖ <?php echo $ts['total_declined']; ?> Declined</small><br>
                <small style="color: grey;">⌛ <?php echo $ts['total_pending']; ?> Pending</small>
            </div>
            <div class="stat-card" style="border-left-color: #28a745;">
                <h4>Invitations Sent</h4>
                <div class="value"><?php echo $ts['total_invitations_sent']; ?> / <?php echo $ts['total_invitations']; ?></div>
                <small>Sent Invitations</small>
            </div>
        </div>

        <!-- Add/Edit Guest -->
        <div class="admin-card" id="guest-form">
            <h3 class="serif" style="margin-bottom: 20px;"><?php echo $editingGuest ? 'Edit Guest' : 'Add New Guest Invitation'; ?></h3>
            <?php if (isset($_GET['success'])) echo "<p style='background: #d4edda; color: #155724; padding: 10px; border-radius: 10px;'>Guest added successfully!</p>"; ?>
            <?php if (isset($_GET['updated'])) echo "<p style='background: #d4edda; color: #155724; padding: 10px; border-radius: 10px;'>Guest updated successfully!</p>"; ?>
            
            <form method="POST" action="admin.php#guest-form">
                <?php if ($editingGuest): ?>
                <input type="hidden" name="guest_id" value="<?php echo $editingGuest['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div style="grid-column: 1 / -1; margin-bottom: 10px;">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Select Title (Anrede auswählen)</label>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; background: #fff; padding: 15px; border-radius: 10px; border: 1px solid #ddd;">
                            <?php 
                            $opts = ["Mrs. & Mr.", "Ms. & Mr.", "Mr.", "Mrs.", "Ms.", "Mr. & Mr.", "Mrs. & Mrs.", "Family"];
                            foreach($opts as $opt) {
                                $checked = ($editingGuest && $editingGuest['salutation_1'] == $opt) ? 'checked' : '';
                                echo "<label style='cursor: pointer; display: flex; align-items: center; gap: 5px;'><input type='radio' name='salutation_1' value=\"$opt\" $checked> $opt</label>";
                            }
                            ?>
                            <label style='cursor: pointer; display: flex; align-items: center; gap: 5px;'><input type='radio' name='salutation_1' value="" <?php echo (!$editingGuest || empty($editingGuest['salutation_1'])) ? 'checked' : ''; ?>> None</label>
                        </div>
                    </div>
                    <div>
                        <label>First Name</label>
                        <input type="text" name="first_name_1" placeholder="e.g. Steven" required value="<?php echo htmlspecialchars($editingGuest['first_name_1'] ?? ''); ?>">
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="text" name="last_name_1" placeholder="e.g. Tchanra" value="<?php echo htmlspecialchars($editingGuest['last_name_1'] ?? ''); ?>">
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <label>WhatsApp Phone Number (with country code, e.g. 491761234567)</label>
                    <input type="text" name="phone_number" placeholder="49176..." value="<?php echo htmlspecialchars($editingGuest['phone_number'] ?? ''); ?>">
                </div>
                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr; gap: 20px; align-items: end;">

                    <div>
                        <label>RSVP Status (Manual Override)</label>
                        <select name="status">
                            <option value="pending" <?php echo ($editingGuest && $editingGuest['status'] == 'pending') ? 'selected' : ''; ?>>Pending (Offen)</option>
                            <option value="accepted" <?php echo ($editingGuest && $editingGuest['status'] == 'accepted') ? 'selected' : ''; ?>>Accepted (Zugesagt)</option>
                            <option value="declined" <?php echo ($editingGuest && $editingGuest['status'] == 'declined') ? 'selected' : ''; ?>>Declined (Abgesagt)</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <label>Additional Family Members (Comma separated)</label>
                        <input type="text" name="family_members" placeholder="e.g. John Doe, Jane Doe" value="<?php 
                            if ($editingGuest && $editingGuest['family_members']) {
                                $decoded = json_decode($editingGuest['family_members'], true);
                                echo htmlspecialchars(is_array($decoded) ? implode(', ', $decoded) : '');
                            }
                        ?>">
                    </div>
                </div>
                <div style="margin-top: 20px; display: flex; align-items: center; gap: 20px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="with_family" value="1" style="width: auto; margin-right: 10px;" <?php echo ($editingGuest && $editingGuest['with_family']) ? 'checked' : ''; ?>>
                        Include "with Family" suffix
                    </label>
                    <div style="flex-grow: 1;"></div>
                    <?php if ($editingGuest): ?>
                    <a href="admin.php" class="btn-luxury" style="background: #ccc; border-color: #ccc; text-decoration: none; padding: 12px 25px;">Cancel</a>
                    <button type="submit" name="update_guest" class="btn-luxury">Update Guest</button>
                    <?php else: ?>
                    <button type="submit" name="add_guest" class="btn-luxury">Add Guest Invitation</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- General Invitation Share -->
        <div class="admin-card" style="display: flex; justify-content: space-between; align-items: center; background: #f0f7ff; border-left-color: #007bff; margin-bottom: 30px;">
            <div>
                <h3 class="serif" style="margin: 0;">General Invitation (Group Share)</h3>
                <p style="margin: 5px 0 0; font-size: 0.9rem; color: #666;">Share the non-personalized invitation with the full schedule.</p>
            </div>
            <div>
                <?php 
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                    $base = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), '/');
                    $gen_link = $base . "/index.php";
                    $gen_share_text = "We cordially invite you to the engagement celebration of our children Saymen with Disha.\n\nYou can find all details and the program here:\n$gen_link\n\nWe look forward to celebrating with you!\n\nRajinder Singh & Dimple Kapoor";
                ?>
                <button class="btn-luxury" style="background: #007bff; border-color: #007bff;" onclick='shareCardImg("card_general.php", <?php echo json_encode($gen_share_text); ?>, "general")'>📱 Share General Card (WhatsApp)</button>
            </div>
        </div>

        <!-- Excel Export -->
        <div class="admin-card" style="display: flex; justify-content: space-between; align-items: center; background: #fdfaf7; border-left-color: var(--primary); margin-bottom: 30px;">
            <div>
                <h3 class="serif" style="margin: 0;">Excel/CSV Export</h3>
                <p style="margin: 5px 0 0; font-size: 0.9rem; color: #666;">Download guest lists for each event.</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="export.php?type=all" class="btn-luxury" style="padding: 8px 15px; font-size: 0.8rem; background: #444; border-color: #444; text-decoration: none;">Export Guest List</a>
            </div>
        </div>

        <!-- Guest List -->
        <div class="admin-card">
            <h3 class="serif">Guest List</h3>
            <div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="guestSearch" placeholder="Search by name, phone or status..." onkeyup="filterGuests()" style="padding: 12px; border-radius: 10px; border: 1px solid #ddd; width: 100%; max-width: 400px; font-family: 'Outfit', sans-serif;">
                

            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            
                            <th>Sent</th>
                            <th>Status</th>
                            <th>WhatsApp Share</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $g): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars(build_guest_display_name($g)); ?>
                                <?php if ($g['status'] === 'accepted'): ?>
                                <?php $confirmedAttendees = get_guest_confirmed_attendees($g); ?>
                                <div style="font-size: 0.85rem; color: #666; margin-top: 6px;">
                                    Attending: <?php echo htmlspecialchars($confirmedAttendees ? implode(', ', $confirmedAttendees) : 'No one selected'); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <a href="?toggle_sent=<?php echo $g['id']; ?>" class="btn-luxury" style="padding: 5px 10px; font-size: 0.7rem; background: <?php echo $g['invitation_sent'] ? '#28a745' : '#6c757d'; ?>; border-color: <?php echo $g['invitation_sent'] ? '#28a745' : '#6c757d'; ?>; text-decoration: none;">
                                    <?php echo $g['invitation_sent'] ? 'Yes' : 'No'; ?>
                                </a>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $g['status']; ?>">
                                    <?php echo ucfirst($g['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                                    $base = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), '/');
                                    $link = $base . "/index.php?g=" . $g['guest_hash']; 
                                    $card_url = $base . "/card.php?g=" . $g['guest_hash'];
                                    
                                    $greeting_en = build_smart_greeting($g, 'en');
                                    $share_text_en = "$greeting_en,\n\nWe cordially invite you to the engagement celebration of our children Saymen with Disha.\n\nYou can find all details and the RSVP here:\n$link\n\nWe look forward to celebrating with you!\n\nRajinder Singh & Dimple Kapoor";
                                    
                                    $phone = $g['phone_number'] ?? '';
                                ?>
                                <div style="display: flex; gap: 8px; flex-direction: column;">
                                    <button class="btn-luxury" id="share-btn-<?php echo $g['guest_hash']; ?>" style="padding: 10px; font-size: 0.8rem;" onclick='shareCardImg("card.php?g=<?php echo $g['guest_hash']; ?>", <?php echo json_encode($share_text_en); ?>, "<?php echo $g['guest_hash']; ?>")'>📱 Share Card + Text (WhatsApp)</button>
                                    
                                    <?php if ($phone): ?>
                                    <button class="btn-luxury" style="padding: 10px; font-size: 0.8rem; background: #25D366; border-color: #25D366;" onclick='openWhatsApp(<?php echo json_encode($phone); ?>, <?php echo json_encode($share_text_en); ?>)'>💬 Direct WA (Text Only)</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $g['id']; ?>#guest-form" style="color: #007bff; font-weight: 700; margin-right: 15px;">Edit</a>
                                <a href="card.php?g=<?php echo $g['guest_hash']; ?>" target="_blank" style="color: var(--secondary); font-weight: 700; margin-right: 15px;">Card</a>
                                <a href="?delete=<?php echo $g['id']; ?>" onclick="return confirm('Delete this guest?')" style="color: var(--primary); font-size: 0.8rem;">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filterGuests() {
            const input = document.getElementById("guestSearch");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll("table tbody tr");

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert("WhatsApp message copied to clipboard!");
            });
        }

        function openWhatsApp(phone, text) {
            const encodedText = encodeURIComponent(text);
            const url = `https://wa.me/${phone}?text=${encodedText}`;
            window.open(url, '_blank');
        }

        let captureFrame = null;
        function shareCardImg(url, shareText, btnId) {
            const btn = document.getElementById(`share-btn-${btnId}`) || event.currentTarget;
            const oldText = btn.textContent;
            btn.textContent = "Loading...";

            // Create or update hidden iframe
            if (!captureFrame) {
                captureFrame = document.createElement('iframe');
                captureFrame.style.position = 'fixed';
                captureFrame.style.left = '-10000px';
                captureFrame.style.top = '-10000px';
                captureFrame.style.width = '500px';
                captureFrame.style.height = '1000px';
                captureFrame.id = 'capture-frame';
                document.body.appendChild(captureFrame);
            }

            captureFrame.src = url;

            // Listen for completion
            const handler = async function(event) {
                if (event.data.type === 'card_captured') {
                    window.removeEventListener('message', handler);
                    const dataUrl = event.data.image;
                    
                    try {
                        const blob = await (await fetch(dataUrl)).blob();
                        const file = new File([blob], 'Invitation.jpg', { type: 'image/jpeg' });

                        // CRITICAL: Copy text to clipboard first as a backup
                        try {
                            await navigator.clipboard.writeText(shareText);
                        } catch (clipErr) {
                            console.warn("Could not copy to clipboard automatically.");
                        }

                        if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                files: [file],
                                title: 'Engagement Invitation',
                                text: shareText
                            });
                        } else {
                            // Fallback to clipboard for image if share is not available
                            const item = new ClipboardItem({ "image/png": blob });
                            await navigator.clipboard.write([item]);
                            alert("Invitation Card & Text copied! \n\n1. Open WhatsApp.\n2. Paste the picture.\n3. Paste the personalized text.");
                        }
                    } catch (err) {
                        console.error(err);
                        alert("Could not share image automatically. Please open the Card page and download it manually.");
                    } finally {
                        btn.textContent = oldText;
                    }
                }
            };
            
            window.addEventListener('message', handler);
            
            // Wait for iframe to load, then tell it to capture
            captureFrame.onload = () => {
                setTimeout(() => {
                    captureFrame.contentWindow.postMessage('capture_card', '*');
                }, 500); // Give cards a moment to render images/fonts
            };
        }
    </script>
    <style>
        /* Small styling for the loader */
        .btn-luxury:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</body>
</html>
