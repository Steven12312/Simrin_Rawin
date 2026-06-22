<?php
require_once 'db.php';
require_once 'guest_helpers.php';
$eventsConfig = require 'config.events.php';

$guest = null;
$guest_hash = isset($_GET['g']) ? strtolower(trim((string)$_GET['g'])) : null;

if ($guest_hash) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM guests WHERE LOWER(guest_hash) = ?");
        $stmt->execute([$guest_hash]);
        $guest = $stmt->fetch();
    } catch (\Exception $e) { $guest = null; }
}

$guest_link_invalid = $guest_hash && !$guest;
$guest_full_greeting = $guest ? build_guest_greeting($guest) : '';
$attendee_options = $guest ? build_guest_invited_attendees($guest) : [];

$invited_events_keys = $guest && $guest['invited_events'] ? json_decode($guest['invited_events'], true) : [];
$invited_events = [];
foreach ($invited_events_keys as $key) {
    if (isset($eventsConfig['events'][$key])) {
        $invited_events[$key] = $eventsConfig['events'][$key];
    }
}
$saved_rsvp_events = $guest && $guest['rsvp_status_events'] ? json_decode($guest['rsvp_status_events'], true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding of Simrin & Rawin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <meta property="og:title" content="Wedding of Simrin & Rawin">
    <meta property="og:description" content="You are cordially invited to celebrate our wedding! Click to view your personal invitation.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <style>
        .timeline-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out;
            background: #fff;
            margin-top: 10px;
            border-radius: 10px;
        }
        .program-item.active .timeline-details {
            max-height: 500px;
            padding: 15px;
            border: 1px solid #eee;
        }
        .timeline-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }
        .timeline-row:last-child { border-bottom: none; }
        .timeline-time { font-weight: 700; color: var(--primary); min-width: 80px; }
        .timeline-event { flex-grow: 1; text-align: left; padding-left: 15px; }
        .toggle-icon { transition: 0.3s; float: right; }
        .program-item.active .toggle-icon { transform: rotate(180deg); }
        .event-select-wrapper { margin-bottom: 20px; text-align: left; }
        .event-select-wrapper label { display: block; font-weight: 600; margin-bottom: 5px; color: var(--primary); }
    </style>
</head>
<body>

    <div class="lang-switch">
        <span class="lang-btn active" onclick="setLang('en')">EN</span>
        <span style="opacity: 0.2">|</span>
        <span class="lang-btn" onclick="setLang('de')">DE</span>
    </div>

    <header class="hero">
        <div class="hero-image-wrapper"><div class="hero-image"></div></div>
        <div class="container" style="position: relative; z-index: 5; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding: 60px 20px;">
            <div style="margin-bottom: 40px; opacity: 0.8; font-size: 0.9rem; color: #333; text-align: center;">
                <span data-i18n="scroll_text">Scroll for details</span><br>
                <span style="display: inline-block; animation: bounce 2s infinite; font-size: 1.5rem;">↓</span>
            </div>
        </div>
    </header>

    <div class="container">

        <!-- Invitation Section -->
        <section id="invitation">
            <div class="invitation-card fade-in">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 110px; height: 110px; margin: -70px auto 0; background: white; border-radius: 50%; border: 3px solid var(--secondary); box-shadow: 0 10px 20px rgba(0,0,0,0.1); overflow: hidden;">
                        <img src="images/guru_nanak.png?v=1" alt="Religious Icon" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    </div>
                </div>                
                <?php if ($guest): ?>
                <div style="margin-bottom: 40px; text-align: center;">
                    <p style="font-size: 1rem; color: #666; margin-bottom: 5px;">Dear</p>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--primary); word-wrap: break-word;">
                        <?php echo htmlspecialchars($guest_full_greeting); ?>
                    </div>
                    <div class="ornament" style="margin: 20px auto;"></div>
                </div>
                <?php endif; ?>
                
                <div class="invitation-header" style="line-height: 1.8; margin-bottom: 40px; text-transform: none; letter-spacing: 1px; font-size: 1.1rem; color: #555;">
                    <div data-i18n="blessings" style="font-style: italic; color: var(--secondary); margin-bottom: 15px;">
                        With the blessings of our Grandparents
                    </div>
                    
                    <div data-i18n="parents_invite" style="margin-top: 25px; font-weight: 400;">
                        request the honour of your presence at the wedding celebration of our children
                    </div>
                </div>
                
                <h2 class="serif" style="font-size: clamp(2.5rem, 8vw, 4rem); color: var(--primary); margin: 20px 0; line-height: 1.1;">
                    Simrin<br>
                    <span style="font-size: 0.5em; font-family: 'Outfit'; display: block; margin: 10px 0;">&amp;</span>
                    Rawin<br>
                </h2>
                
                <div class="ornament"></div>
                
                <div class="rsvp-guest-name" style="margin: 30px 0; font-size: 1.4rem; color: #444; line-height: 1.6;">
                    <?php if ($guest): ?>
                        <span data-i18n="generic_invite_text">We are looking forward to celebrating with you!</span>
                    <?php else: ?>
                        <span data-i18n="invalid_link_title" style="color: var(--primary); font-weight: 700; display: block; margin-bottom: 10px;">Invitation link not found.</span>
                        <span data-i18n="invalid_link_text" style="font-size: 1rem; color: #666;">Please open the personal link from your message to RSVP.</span>
                    <?php endif; ?>
                </div>

                <div class="program-grid">
                    <h3 class="serif" data-i18n="program_title" style="font-size: 2.2rem; margin-bottom: 30px; text-align: center;">Wedding Schedule</h3>
                    
                    <?php if (empty($invited_events)): ?>
                        <p>No events assigned yet.</p>
                    <?php else: ?>
                        <?php $first = true; foreach ($invited_events as $event): ?>
                        <div class="program-item <?php echo $first ? 'active' : ''; ?>" onclick="toggleTimeline(this)">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="text-align: left;">
                                    <div style="font-weight: 800; color: var(--secondary); font-size: 1.2rem;"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div style="font-size: 0.85rem; color: #666; margin-top: 2px;"><?php echo htmlspecialchars($event['date']); ?></div>
                                    <div style="font-size: 0.8rem; color: #888;"><?php echo htmlspecialchars($event['location_name']); ?></div>
                                </div>
                                <div class="toggle-icon">▼</div>
                            </div>
                            <div class="timeline-details" onclick="event.stopPropagation();">
                                <?php if (!empty($event['schedule'])): ?>
                                    <?php foreach ($event['schedule'] as $time => $desc): ?>
                                    <div class="timeline-row">
                                        <span class="timeline-time"><?php echo htmlspecialchars($time); ?></span>
                                        <span class="timeline-event"><?php echo htmlspecialchars($desc); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <div style="margin-top: 15px; font-size: 0.85rem; color: #555;">
                                    <strong>Address:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($event['address'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php $first = false; endforeach; ?>
                    <?php endif; ?>
                </div>
                </div>

                <!-- RSVP Section -->
                <div class="rsvp-form-container" style="margin-top: 40px;">
                    <h3 class="serif" data-i18n="rsvp_title" style="margin-bottom: 10px;">Your RSVP</h3>
                    <p data-i18n="rsvp_deadline" style="color: var(--secondary); font-weight: 600; margin-bottom: 30px; font-size: 0.95rem; letter-spacing: 0.5px;">Please RSVP by July 25th - we look forward to your response!</p>
                    
                    <?php if ($guest): ?>
                        <?php if ($guest['status'] === 'pending' || $guest['status'] === 'declined' || $guest['status'] === 'partial'): ?>
                        <form id="rsvpForm">
                            <input type="hidden" name="guest_hash" value="<?php echo htmlspecialchars($guest_hash); ?>">
                            
                            <?php foreach ($invited_events as $key => $event): ?>
                            <div class="event-select-wrapper" style="background: #fdfaf7; padding: 15px; border-radius: 10px; border: 1px solid #decba4; margin-bottom: 15px;">
                                <label><?php echo htmlspecialchars($event['title']); ?> (<?php echo htmlspecialchars($event['date']); ?>)</label>
                                <select name="rsvp_events[<?php echo $key; ?>]" class="event-rsvp-select" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #eee; font-family: inherit; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 1rem center / 1rem;" required>
                                    <option value="" data-i18n="opt_choose">Please choose...</option>
                                    <option value="accepted" data-i18n="opt_yes">Yes, I/we will attend</option>
                                    <option value="declined" data-i18n="opt_no">No, cannot make it</option>
                                </select>
                            </div>
                            <?php endforeach; ?>

                            <div id="attendee-section" style="display: none; border-top: 1px solid #eee; padding-top: 30px; margin-bottom: 30px; text-align: left;">
                                <label data-i18n="attendees_label" style="display: block; margin-bottom: 15px;">Please confirm who will attend.</label>
                                <?php foreach ($attendee_options as $member): ?>
                                <div style="display: flex; align-items: center; margin-bottom: 12px; background: #f9f9f9; padding: 12px; border-radius: 10px;">
                                    <input type="checkbox" name="attending_members[]" value="<?php echo htmlspecialchars($member); ?>" checked style="width: auto; margin-right: 15px; transform: scale(1.2);">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($member); ?></span>
                                </div>
                                <?php endforeach; ?>
                                <p data-i18n="attendees_hint" style="font-size: 0.9rem; color: #666; margin-top: 10px;">You can uncheck anyone who cannot attend.</p>
                            </div>

                            <button type="submit" class="btn-luxury" data-i18n="btn_send">Send RSVP</button>
                        </form>
                        <?php else: ?>
                        <div style="background: #fdfaf7; border: 1px solid var(--secondary); border-radius: 20px; padding: 30px; color: #444; border-style: dashed;">
                            <h4 class="serif" style="color: var(--primary); margin-bottom: 15px; font-size: 1.4rem;" data-i18n="rsvp_thanks_title">Thank you!</h4>
                            <p data-i18n="rsvp_already_submitted" style="font-weight: 600; margin-bottom: 10px;">Your response has been saved.</p>
                            
                            <?php foreach ($invited_events as $key => $event): ?>
                                <div style="background: #fff; padding: 10px; margin-bottom: 5px; border-radius: 10px; font-size: 0.95rem; border: 1px solid #eee; text-align: left;">
                                    <strong><?php echo htmlspecialchars($event['title']); ?>:</strong> 
                                    <?php echo isset($saved_rsvp_events[$key]) && $saved_rsvp_events[$key] === 'accepted' ? '✓ Accepted' : '✗ Declined'; ?>
                                </div>
                            <?php endforeach; ?>

                            <p style="margin-top: 20px; font-size: 0.9rem; color: #666;" data-i18n="rsvp_locked_hint">Changes are no longer possible here. If you need to update something, please contact us personally.</p>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 24px; color: #555;">
                        <p data-i18n="<?php echo $guest_link_invalid ? 'invalid_link_text' : 'missing_link_text'; ?>">
                            <?php echo $guest_link_invalid
                                ? 'Please open the personal link from your message to RSVP.'
                                : 'Please use your personal invitation link to send an RSVP.'; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    <div id="formResponse" style="margin-top: 20px; text-align: center; display: none; padding: 20px; border-radius: 15px;"></div>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section id="story">
            <div class="story-card fade-in">
                <div style="width: 100%; margin-bottom: 30px; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <img src="images/story.jpg" alt="Simrin and Rawin" style="width: 100%; height: auto; display: block;">
                </div>
                <div>
                    <h2 class="serif" data-i18n="story_title" style="font-size: 2.2rem; margin-bottom: 20px;">Special Message from Simrin & Rawin</h2>
                    <p style="color: #666; font-size: 1.1rem;" data-i18n="story_text">Join us on our journey to our big day. We can't wait to celebrate this special moment with our family and closest friends.</p>
                </div>
            </div>
            <div style="margin-top: 40px; text-align: center;">
                <div style="width: 100%; max-width: 600px; margin: 0 auto; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <img src="images/hugging.jpg" alt="Simrin and Rawin" style="width: 100%; height: auto; display: block;">
                </div>
            </div>
        </section>
    </div>

    <footer style="padding: 100px 0; text-align: center; background: #000; color: #fff; margin-top: 100px;">
        <div class="container">
            <h2 class="serif" style="font-size: 2.5rem; letter-spacing: 2px;">Simrin & Rawin</h2>
            <div class="ornament"></div>
            <p style="margin-top: 20px; opacity: 0.3; letter-spacing: 5px;">#SIMRINANDRAWIN</p>
            <p style="margin-top: 40px; font-size: 0.7rem; opacity: 0.4;">
                <a href="legal.php" style="color: #fff; text-decoration: none;" data-i18n="legal_link">Impressum & Datenschutz</a>
            </p>
        </div>
    </footer>

    <script>
        const i18n = {
            en: {
                hero_sub: "We are getting married!",
                blessings: "With the blessings of our Grandparents",
                parents_invite: "request the honour of your presence at the wedding celebration of our children",
                invite_prefix: "Dear",
                invite_text: "cordially invite you to the wedding celebration of our children.",
                generic_invite_text: "We look forward to celebrating with you!",
                invalid_link_title: "Invitation link not found.",
                invalid_link_text: "Please open the personal link from your message to RSVP.",
                missing_link_text: "Please use your personal invitation link to send an RSVP.",
                program_title: "Wedding Schedule",
                rsvp_title: "Your RSVP",
                rsvp_deadline: "Please RSVP by July 25th, 2026 – we look forward to your response!",
                rsvp_label: "Will you attend?",
                opt_choose: "Please choose...",
                opt_yes: "Yes, I/we will be there!",
                opt_no: "No, unfortunately I/we cannot make it.",
                attendees_label: "Please confirm who will attend.",
                attendees_hint: "You can uncheck anyone who cannot attend.",
                btn_send: "Send RSVP",
                rsvp_status_required: "Please choose whether you will attend for each event.",
                rsvp_attendee_required: "Please select at least one person for the RSVP.",
                rsvp_error: "Error sending RSVP. Please try again.",
                story_title: "Special Message from Simrin & Rawin",
                story_text: "Join us on our journey to our big day. We can't wait to celebrate this special moment with our family and closest friends.",
                scroll_text: "Scroll for details",
                location_title: "The Venue",
                location_btn: "Open in Navigation",
                rsvp_thanks_title: "Thank you!",
                rsvp_already_submitted: "Your response has been saved.",
                rsvp_locked_hint: "Changes are no longer possible here. If you need to update something, please contact us personally.",
                legal_link: "Legal Notice & Privacy"
            },
            de: {
                hero_sub: "Wir heiraten!",
                blessings: "Mit dem Segen unserer Großeltern",
                parents_invite: "bitten um die Ehre Ihrer Anwesenheit bei der Hochzeitsfeier unserer Kinder",
                invite_text: "laden Sie herzlich zur Hochzeitsfeier unserer Kinder ein.",
                generic_invite_text: "Wir freuen uns darauf, mit euch zu feiern!",
                invalid_link_title: "Einladungslink nicht gefunden.",
                invalid_link_text: "Bitte nutzen Sie den persoenlichen Link aus Ihrer Nachricht fuer die Rueckmeldung.",
                missing_link_text: "Bitte nutzen Sie Ihren persoenlichen Einladungslink fuer die Rueckmeldung.",
                program_title: "Hochzeits-Ablauf",
                rsvp_title: "Ihre Rückmeldung",
                rsvp_deadline: "Wir bitten um Ihre Rückmeldung bis zum 25. Juli 2026 – wir freuen uns sehr auf Ihre Zusage!",
                rsvp_label: "Kommen Sie?",
                opt_choose: "Bitte wählen...",
                opt_yes: "Ja, wir kommen sehr gerne!",
                opt_no: "Leider können wir nicht teilnehmen.",
                attendees_label: "Bitte bestaetigen Sie, wer teilnehmen wird.",
                attendees_hint: "Sie koennen Personen abwaehlen, die nicht teilnehmen koennen.",
                btn_send: "Zusage senden",
                rsvp_status_required: "Bitte waehlen Sie fuer jedes Event eine Rueckmeldung aus.",
                rsvp_attendee_required: "Bitte waehlen Sie mindestens eine teilnehmende Person aus.",
                rsvp_error: "Die Rueckmeldung konnte nicht gesendet werden. Bitte versuchen Sie es erneut.",
                story_title: "Special Message from Simrin & Rawin",
                story_text: "Begleitet uns auf dem Weg zu unserem großen Tag. Wir können es kaum erwarten, diesen Moment mit euch zu feiern.",
                scroll_text: "Für Details nach unten wischen",
                location_title: "Der Veranstaltungsort",
                location_btn: "Navigation starten",
                rsvp_thanks_title: "Vielen Dank!",
                rsvp_already_submitted: "Deine Rückmeldung wurde erfolgreich gespeichert.",
                rsvp_locked_hint: "Änderungen sind hier nicht mehr möglich. Falls du etwas korrigieren möchtest, kontaktiere uns bitte persönlich.",
                legal_link: "Impressum & Datenschutz"
            }
        };

        function setLang(lang) {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (i18n[lang][key]) el.innerHTML = i18n[lang][key];
            });
            document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.toggle('active', btn.textContent.toLowerCase() === lang));
            localStorage.setItem('wedding_lang', lang);
        }

        function toggleTimeline(el) {
            const isActive = el.classList.contains('active');
            document.querySelectorAll('.program-item').forEach(item => item.classList.remove('active'));
            if (!isActive) el.classList.add('active');
        }

        const savedLang = localStorage.getItem('wedding_lang') || 'en';
        setLang(savedLang);

        const rsvpForm = document.getElementById('rsvpForm');
        const formResponse = document.getElementById('formResponse');
        const attendeeSection = document.getElementById('attendee-section');
        const submitButton = rsvpForm ? rsvpForm.querySelector('button[type="submit"]') : null;
        
        const eventSelects = document.querySelectorAll('.event-rsvp-select');

        function updateAttendeeSection() {
            if (!attendeeSection || eventSelects.length === 0) return;
            let anyAccepted = false;
            eventSelects.forEach(sel => {
                if (sel.value === 'accepted') anyAccepted = true;
            });
            attendeeSection.style.display = anyAccepted ? 'block' : 'none';
        }

        eventSelects.forEach(sel => {
            sel.addEventListener('change', updateAttendeeSection);
        });
        updateAttendeeSection();

        function getActiveLang() {
            return localStorage.getItem('wedding_lang') || 'en';
        }

        function setFormMessage(message, isSuccess) {
            formResponse.style.display = 'block';
            formResponse.textContent = message;
            formResponse.style.backgroundColor = isSuccess ? '#d4edda' : '#f8d7da';
            formResponse.style.color = isSuccess ? '#155724' : '#721c24';
        }

        if (rsvpForm) {
            rsvpForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const lang = getActiveLang();
                const formData = new FormData(this);
                
                let rsvpEvents = {};
                let allFilled = true;
                let anyAccepted = false;
                
                eventSelects.forEach(sel => {
                    const eventKey = sel.name.replace('rsvp_events[', '').replace(']', '');
                    const val = sel.value;
                    if (!val) allFilled = false;
                    if (val === 'accepted') anyAccepted = true;
                    rsvpEvents[eventKey] = val;
                });

                if (!allFilled) {
                    setFormMessage(i18n[lang].rsvp_status_required, false);
                    return;
                }

                const data = {
                    guest_hash: formData.get('guest_hash'),
                    rsvp_events: rsvpEvents,
                    attending_members: formData.getAll('attending_members[]'),
                    lang
                };

                if (anyAccepted && data.attending_members.length === 0) {
                    setFormMessage(i18n[lang].rsvp_attendee_required, false);
                    return;
                }

                if (submitButton) submitButton.disabled = true;

                try {
                    const response = await fetch('api/rsvp.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    setFormMessage(result.message || i18n[lang].rsvp_error, result.success);
                    if (result.success) {
                        rsvpForm.style.display = 'none';
                    }
                } catch (error) {
                    setFormMessage(i18n[lang].rsvp_error, false);
                } finally {
                    if (submitButton) submitButton.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
