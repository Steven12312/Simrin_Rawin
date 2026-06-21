<?php
require_once '../db.php';
require_once '../guest_helpers.php';
$eventsConfig = require '../config.events.php';

header('Content-Type: application/json');

function rsvp_response_messages(string $lang): array
{
    if ($lang === 'de') {
        return [
            'invalid_method' => 'Methode nicht erlaubt.',
            'invalid_data' => 'Ungueltige Daten.',
            'invalid_status' => 'Bitte waehlen Sie eine gueltige Rueckmeldung aus.',
            'guest_not_found' => 'Gast nicht gefunden.',
            'missing_attendees' => 'Bitte waehlen Sie mindestens eine teilnehmende Person aus.',
            'success_accepted' => 'Vielen Dank fuer Ihre Zusage. Wir freuen uns darauf, mit Ihnen zu feiern.',
            'success_declined' => 'Vielen Dank fuer Ihre Rueckmeldung. Schade, dass Sie nicht dabei sein koennen.',
            'db_error' => 'Datenbankfehler. Bitte versuchen Sie es spaeter erneut.',
        ];
    }

    return [
        'invalid_method' => 'Method not allowed.',
        'invalid_data' => 'Invalid data.',
        'invalid_status' => 'Please choose a valid RSVP status for all events.',
        'guest_not_found' => 'Guest not found.',
        'missing_attendees' => 'Please select at least one attendee.',
        'success_accepted' => 'Thank you for your RSVP. We look forward to celebrating with you.',
        'success_declined' => 'Thank you for letting us know. We are sorry you cannot make it.',
        'db_error' => 'Database error. Please try again later.',
    ];
}

function respond_json(bool $success, string $message, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(false, 'Method not allowed.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$lang = isset($input['lang']) && $input['lang'] === 'de' ? 'de' : 'en';
$messages = rsvp_response_messages($lang);

if (!$input || empty($input['guest_hash'])) {
    respond_json(false, $messages['invalid_data'], 400);
}

$guest_hash = normalize_guest_value($input['guest_hash']);
$message = normalize_guest_value($input['message'] ?? '');
$rsvp_events = $input['rsvp_events'] ?? [];

if (!is_array($rsvp_events)) {
    respond_json(false, $messages['invalid_status'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE guest_hash = ?");
    $stmt->execute([$guest_hash]);
    $guest = $stmt->fetch();

    if (!$guest) {
        respond_json(false, $messages['guest_not_found'], 404);
    }

    // Verify all submitted events are valid for this guest
    $guest_invited_events = $guest['invited_events'] ? json_decode($guest['invited_events'], true) : [];
    
    $any_accepted = false;
    $all_declined = true;
    $valid_rsvp_data = [];

    foreach ($rsvp_events as $event_key => $status) {
        if (in_array($event_key, $guest_invited_events) && in_array($status, ['accepted', 'declined'])) {
            $valid_rsvp_data[$event_key] = $status;
            if ($status === 'accepted') {
                $any_accepted = true;
                $all_declined = false;
            }
        }
    }

    $overall_status = 'pending';
    if (!empty($valid_rsvp_data)) {
        if ($all_declined) {
            $overall_status = 'declined';
        } else {
            // They accepted at least one
            $overall_status = 'accepted'; 
        }
    }

    $attendingMembers = [];
    if ($any_accepted) {
        $submittedMembers = is_array($input['attending_members'] ?? null) ? $input['attending_members'] : [];
        $attendingMembers = filter_guest_attending_members($guest, $submittedMembers);

        if (count($attendingMembers) === 0) {
            respond_json(false, $messages['missing_attendees'], 400);
        }
    }

    $attendingJson = json_encode($attendingMembers, JSON_UNESCAPED_UNICODE);
    $rsvpEventsJson = json_encode($valid_rsvp_data, JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("UPDATE guests SET status = ?, rsvp_status_events = ?, message = ?, attending_members = ?, attending_members_version = 2, updated_at = CURRENT_TIMESTAMP WHERE guest_hash = ?");
    $stmt->execute([$overall_status, $rsvpEventsJson, $message, $attendingJson, $guest_hash]);

    // Construct an email body
    $to = 'Simrin.rawin@example.com'; // TODO: Update to real email if needed
    $subject = 'Neue RSVP Rückmeldung: ' . ($guest['first_name_1'] . ' ' . $guest['last_name_1']);
    $attendingList = $attendingMembers ? implode(', ', $attendingMembers) : 'Keine';
    $guestFullName = build_guest_display_name($guest);

    $email_body = "Hallo,\n\nein Gast hat auf die Hochzeitseinladung geantwortet!\n\n";
    $email_body .= "Name: " . $guestFullName . "\n";
    
    $email_body .= "Event-Zusagen:\n";
    foreach ($valid_rsvp_data as $key => $s) {
        $eventName = isset($eventsConfig['events'][$key]) ? $eventsConfig['events'][$key]['title'] : $key;
        $label = $s === 'accepted' ? 'ZUGESAGT' : 'ABGESAGT';
        $email_body .= "- " . $eventName . ": " . $label . "\n";
    }
    
    $email_body .= "\nTeilnehmende Gaeste: " . $attendingList . "\n";
    if ($message) {
        $email_body .= "Nachricht / Hinweise: " . $message . "\n\n";
    }
    $email_body .= "\nDu kannst alle Details im Admin-Panel einsehen.";

    $mailHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $headers = "From: Wedding RSVP <no-reply@" . $mailHost . ">\r\n";
    $headers .= "Reply-To: " . $to . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($to, $subject, $email_body, $headers);

    $successMessage = $any_accepted ? $messages['success_accepted'] : $messages['success_declined'];
    respond_json(true, $successMessage);
} catch (\Exception $e) {
    error_log($e->getMessage());
    respond_json(false, $messages['db_error'], 500);
}

