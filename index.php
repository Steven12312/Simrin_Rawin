<?php
require_once 'db.php';
require_once 'guest_helpers.php';

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
$invitation_days = $guest ? (int)$guest['invitation_days'] : 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engagement of Saymen & Disha</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <!-- Open Graph / WhatsApp Preview -->
    <meta property="og:title" content="Engagement of Saymen & Disha">
    <meta property="og:description" content="You are cordially invited to celebrate our wedding! Click to view your personal invitation.">
    <meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>/images/story_cartoon.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
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
            <!-- Scroll Indicator -->
            <div style="margin-bottom: 40px; opacity: 0.8; font-size: 0.9rem; color: white; text-align: center; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                <span data-i18n="scroll_text">Scroll for details</span><br>
                <span style="display: inline-block; animation: bounce 2s infinite; font-size: 1.5rem;">↓</span>
            </div>
        </div>
    </header>

    <div class="container">

        <!-- Invitation Section -->
        <section id="invitation">
            <div class="invitation-card fade-in">
                
                <!-- Sai Baba & Ganesh now at the top of the content card -->
                <div style="display: flex; justify-content: center; margin-top: -80px; margin-bottom: 30px;">
                    <div class="religious-photo-card" style="width: 120px; height: 120px; background: white; border-radius: 50%; padding: 10px; border: 3px solid var(--secondary); box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                        <img src="images/sai_ganesh.png" alt="Sai Baba & Ganesh" style="width: 100%; border-radius: 50%;">
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
                        With the blessings of our Grandparents<br>
                        <strong>[Grandparents Placeholder]</strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; text-align: center; margin-top: 25px; font-weight: 400; font-size: 0.95rem; gap: 15px;">
                        <div style="flex: 1;">
                            <div style="font-size: 0.75rem; letter-spacing: 1px; color: var(--secondary); text-transform: uppercase; margin-bottom: 5px;">Bride's Parents</div>
                            <strong>Mr. Hitesh & Mrs. Harsha Arenja</strong>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.75rem; letter-spacing: 1px; color: var(--secondary); text-transform: uppercase; margin-bottom: 5px;">Groom's Parents</div>
                            <strong>Mr. Rajinder Singh & Mrs. Dimple Kapoor</strong>
                        </div>
                    </div>

                    <div data-i18n="parents_invite" style="margin-top: 25px; font-weight: 400;">
                        request the honour of your presence at the engagement celebration of our children
                    </div>
                </div>
                
                <h2 class="serif" style="font-size: clamp(2.5rem, 8vw, 4rem); color: var(--primary); margin: 20px 0; line-height: 1.1;">
                    Saymen<br>
                    <span style="font-size: 0.5em; font-family: 'Outfit'; display: block; margin: 10px 0;">&amp;</span>
                    Disha<br>
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
                    
                    <?php if ($invitation_days >= 3): ?>
                    <!-- Haldi -->
                    <div class="program-item" onclick="toggleTimeline(this)">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="text-align: left;">
                                <div style="font-size: 0.8rem; letter-spacing: 2px; color: var(--primary); font-weight: 700; margin-bottom: 5px; opacity: 0.8;">23. JUN 2026</div>
                                <div style="font-weight: 800; color: var(--secondary); font-size: 1.2rem;" data-i18n="tag1_title">Haldi & Sagan Ceremony</div>
                            </div>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="timeline-details">
                            <div class="timeline-row">
                                <span class="timeline-time">02:00 PM</span>
                                <span class="timeline-event" data-i18n="t1_e1">Welcome of Guests</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">03:00 PM</span>
                                <span class="timeline-event" data-i18n="t1_e2">Haldi Ceremony</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">05:00 PM</span>
                                <span class="timeline-event" data-i18n="t1_e3">Nashta</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">06:00 PM</span>
                                <span class="timeline-event" data-i18n="t1_e4">Sagan Ceremony</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">09:00 PM</span>
                                <span class="timeline-event" data-i18n="t1_e5">Dinner</span>
                            </div>
                            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #eee; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-align: left;">
                                <span data-i18n="dress_code_t1">Dress Code: Pink or Purple (Optional)</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($invitation_days >= 2): ?>
                    <!-- Wedding -->
                    <div class="program-item" onclick="toggleTimeline(this)">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="text-align: left;">
                                <div style="font-size: 0.8rem; letter-spacing: 2px; color: var(--primary); font-weight: 700; margin-bottom: 5px; opacity: 0.8;">24. JUN 2026</div>
                                <div style="font-weight: 800; color: var(--secondary); font-size: 1.2rem;" data-i18n="tag2_title">Wedding Ceremony</div>
                            </div>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="timeline-details">
                            <div class="timeline-row">
                                <span class="timeline-time">12:00 PM</span>
                                <span class="timeline-event" data-i18n="t2_e1">Welcome of Guests</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">01:00 PM</span>
                                <span class="timeline-event" data-i18n="t2_e2">Nashta</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">02:00 PM</span>
                                <span class="timeline-event" data-i18n="t2_e3">Baraat</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Reception -->
                    <div class="program-item" onclick="toggleTimeline(this)">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="text-align: left;">
                                <div style="font-size: 0.8rem; letter-spacing: 2px; color: var(--primary); font-weight: 700; margin-bottom: 5px; opacity: 0.8;">25. JUN 2026</div>
                                <div style="font-weight: 800; color: var(--secondary); font-size: 1.2rem;" data-i18n="tag3_title">Reception</div>
                            </div>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="timeline-details">
                            <div class="timeline-row">
                                <span class="timeline-time">04:00 PM</span>
                                <span class="timeline-event" data-i18n="t3_e1">Welcome of Guests</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">05:00 PM</span>
                                <span class="timeline-event" data-i18n="t3_e2">Light Music and Cocktails</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">06:00 PM</span>
                                <span class="timeline-event" data-i18n="t3_e3">Nashta</span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-time">09:00 PM</span>
                                <span class="timeline-event" data-i18n="t3_e4">Dinner</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div style="margin: 40px 0; padding: 30px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;">
                    <div style="font-size: 0.9rem; letter-spacing: 2px; text-transform: uppercase; color: var(--secondary); margin-bottom: 10px; font-weight: 700;" data-i18n="location_title">The Venue</div>
                    <h3 class="serif" style="font-size: 1.8rem; margin-bottom: 15px;">Royal Stage</h3>
                    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                        Im Hegen 16<br>
                        22113 Glinde
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query=Royal+Stage+Im+Hegen+16+22113+Glinde" 
                       target="_blank" 
                       class="btn-luxury" 
                       style="display: inline-block; width: auto; padding: 12px 30px; font-size: 0.9rem; text-decoration: none;">
                       <span data-i18n="location_btn">Open in Navigation</span>
                    </a>
                </div>

                <!-- RSVP Section -->
                <div class="rsvp-form-container">
                    <h3 class="serif" data-i18n="rsvp_title" style="margin-bottom: 10px;">Your RSVP</h3>
                    <p data-i18n="rsvp_deadline" style="color: var(--secondary); font-weight: 600; margin-bottom: 30px; font-size: 0.95rem; letter-spacing: 0.5px;">Please RSVP by June 1st - we look forward to your response!</p>
                    <?php if ($guest): ?>
                        <?php if ($guest['status'] === 'pending' || $guest['status'] === 'declined'): ?>
                        <form id="rsvpForm">
                            <input type="hidden" name="guest_hash" value="<?php echo htmlspecialchars($guest_hash); ?>">
                            <div class="form-group" style="margin-bottom: 30px; text-align: left;">
                                <label data-i18n="rsvp_label">Will you attend?</label>
                                <select name="status" id="status" style="width: 100%; padding: 18px; border-radius: 15px; border: 1px solid #eee; font-family: inherit; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 1rem center / 1rem;">
                                    <option value="" data-i18n="opt_choose">Please choose...</option>
                                    <option value="accepted" data-i18n="opt_yes">Yes, I/we will be there!</option>
                                    <option value="declined" data-i18n="opt_no" <?php echo $guest['status'] === 'declined' ? 'selected' : ''; ?>>No, unfortunately I/we cannot make it.</option>
                                </select>
                            </div>
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
                            <div style="background: #fff; padding: 15px; border-radius: 10px; font-size: 1.1rem; border: 1px solid #eee;">
                                <?php if ($guest['status'] === 'accepted'): ?>
                                    <span data-i18n="opt_yes">Yes, I/we will be there!</span>
                                <?php else: ?>
                                    <span data-i18n="opt_no">No, unfortunately I/we cannot make it.</span>
                                <?php endif; ?>
                            </div>
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

        <div class="family-cartoon-container fade-in">
            <img src="images/story_cartoon.png" alt="Family Photo">
            <div class="contact-section" style="margin-top: 25px; text-align: center; padding: 20px; border-top: 1px solid #eee;">
                <h4 style="margin-bottom: 20px; letter-spacing: 2px; text-transform: uppercase; font-size: 0.9rem; color: var(--secondary); font-weight: 700;">Kontakt</h4>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; line-height: 1.6;">
                    <div><strong>Hitesh Arenja</strong><br><a href="tel:+" style="color: inherit; text-decoration: none; opacity: 0.8;">+49 ...</a></div>
                    <div><strong>Rajinder Singh Kapoor</strong><br><a href="tel:+" style="color: inherit; text-decoration: none; opacity: 0.8;">+49 ...</a></div>
                </div>
            </div>
        </div>

        <!-- Story Section -->
        <section id="story">
            <div class="story-card fade-in">
                <div><img src="images/story_cartoon.png" alt="Couple"></div>
                <div>
                    <h2 class="serif" data-i18n="story_title" style="font-size: 2.2rem; margin-bottom: 20px;">Special Message from Saymen & Disha</h2>
                    <p style="color: #666; font-size: 1.1rem;" data-i18n="story_text">Join us on our journey to our big day. We can't wait to celebrate this special moment with our family and closest friends.</p>
                </div>
            </div>
        </section>
    </div>

    <footer style="padding: 100px 0; text-align: center; background: #000; color: #fff; margin-top: 100px;">
        <div class="container">
            <h2 class="serif" style="font-size: 2.5rem; letter-spacing: 2px;">Saymen & Disha</h2>
            <div class="ornament"></div>
            <p style="margin-top: 20px; opacity: 0.3; letter-spacing: 5px;">#SAYMENANDDISHA</p>
            <p style="margin-top: 40px; font-size: 0.7rem; opacity: 0.4;">
                <a href="legal.php" style="color: #fff; text-decoration: none;" data-i18n="legal_link">Impressum & Datenschutz</a>
            </p>
        </div>
    </footer>

    <script>
        const i18n = {
            en: {
                hero_sub: "We are getting engaged!",
                blessings: "With the blessings of<br><strong>Late Smt. Laxmi Devi & Late Shri Hondraj Tchanra</strong><br>&<br><strong>Late Smt. Chambeli Devi & Late Shri Dev Raj Gandhi</strong>",
                parents_invite: "<strong>Mrs. Sapna & Mr. Manoj Kumar Tchanra</strong><br>request the honour of your presence at the engagement celebration of our son",
                invite_prefix: "Dear",
                invite_text: "cordially invite you to the engagement celebration of our son.",
                generic_invite_text: "We look forward to celebrating with you!",
                invalid_link_title: "Invitation link not found.",
                invalid_link_text: "Please open the personal link from your message to RSVP.",
                missing_link_text: "Please use your personal invitation link to send an RSVP.",
                program_title: "Wedding Schedule",
                tag1_title: "Haldi & Sagan Ceremony",
                t1_e1: "Welcome of Guests",
                t1_e2: "Haldi Ceremony",
                t1_e3: "Nashta",
                t1_e4: "Sagan Ceremony",
                t1_e5: "Dinner",
                dress_code_t1: "Dresscode: Pink or Purple (Optional - if possible/no pressure!)",
                tag2_title: "Wedding Ceremony",
                t2_e1: "Welcome of Guests",
                t2_e2: "Nashta",
                t2_e3: "Baraat",
                tag3_title: "Reception",
                t3_e1: "Welcome of Guests",
                t3_e2: "Light Music and Cocktails",
                t3_e3: "Nashta",
                t3_e4: "Dinner",
                rsvp_title: "Your RSVP",
                rsvp_deadline: "Please RSVP by May 20th, 2026 – we look forward to your response!",
                rsvp_label: "Will you attend?",
                opt_choose: "Please choose...",
                opt_yes: "Yes, I/we will be there!",
                opt_no: "No, unfortunately I/we cannot make it.",
                attendees_label: "Please confirm who will attend.",
                attendees_hint: "You can uncheck anyone who cannot attend.",
                btn_send: "Send RSVP",
                rsvp_status_required: "Please choose whether you will attend.",
                rsvp_attendee_required: "Please select at least one person for the RSVP.",
                rsvp_error: "Error sending RSVP. Please try again.",
                story_title: "Special Message from Saymen & Disha",
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
                hero_sub: "Wir verloben uns!",
                blessings: "Mit dem Segen von<br><strong>Verst. Smt. Laxmi Devi & Verst. Shri Hondraj Tchanra</strong><br>&<br><strong>Verst. Smt. Chambeli Devi & Verst. Shri Dev Raj Gandhi</strong>",
                parents_invite: "<strong>Mrs. Sapna & Mr. Manoj Kumar Tchanra</strong><br>bitten um die Ehre Ihrer Anwesenheit bei der Verlobungsfeier unseres Sohnes",
                invite_text: "laden Sie herzlich zur Verlobungsfeier unseres Sohnes ein.",
                generic_invite_text: "Wir freuen uns darauf, mit euch zu feiern!",
                invalid_link_title: "Einladungslink nicht gefunden.",
                invalid_link_text: "Bitte nutzen Sie den persoenlichen Link aus Ihrer Nachricht fuer die Rueckmeldung.",
                missing_link_text: "Bitte nutzen Sie Ihren persoenlichen Einladungslink fuer die Rueckmeldung.",
                program_title: "Hochzeits-Ablauf",
                tag1_title: "Haldi & Sagan Ceremony",
                t1_e1: "Willkommen der Gäste",
                t1_e2: "Haldi Zeremonie",
                t1_e3: "Nashta",
                t1_e4: "Sagan Zeremonie",
                t1_e5: "Abendessen",
                dress_code_t1: "Dresscode: Pink oder Lila (Optional - nur falls ihr die Möglichkeit seht, kein Zwang!)",
                tag2_title: "Hochzeits-Zeremonie",
                t2_e1: "Willkommen der Gäste",
                t2_e2: "Nashta",
                t2_e3: "Baraat",
                tag3_title: "Reception",
                t3_e1: "Willkommen der Gäste",
                t3_e2: "Leichte Musik & Cocktails",
                t3_e3: "Nashta",
                t3_e4: "Abendessen",
                rsvp_title: "Ihre Rückmeldung",
                rsvp_deadline: "Wir bitten um Ihre Rückmeldung bis zum 20. Mai 2026 – wir freuen uns sehr auf Ihre Zusage!",
                rsvp_label: "Kommen Sie?",
                opt_choose: "Bitte wählen...",
                opt_yes: "Ja, wir kommen sehr gerne!",
                opt_no: "Leider können wir nicht teilnehmen.",
                attendees_label: "Bitte bestaetigen Sie, wer teilnehmen wird.",
                attendees_hint: "Sie koennen Personen abwaehlen, die nicht teilnehmen koennen.",
                btn_send: "Zusage senden",
                rsvp_status_required: "Bitte waehlen Sie zuerst eine Rueckmeldung aus.",
                rsvp_attendee_required: "Bitte waehlen Sie mindestens eine teilnehmende Person aus.",
                rsvp_error: "Die Rueckmeldung konnte nicht gesendet werden. Bitte versuchen Sie es erneut.",
                story_title: "Special Message from Saymen & Disha",
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
            
            const greetingEl = document.getElementById('guest-greeting-text');
            if (greetingEl) {
                greetingEl.textContent = lang === 'de' ? greetingEl.dataset.greetingDe : greetingEl.dataset.greetingEn;
            }
        }

        function toggleTimeline(el) {
            const isActive = el.classList.contains('active');
            document.querySelectorAll('.program-item').forEach(item => item.classList.remove('active'));
            if (!isActive) el.classList.add('active');
        }

        // Initialize
        const savedLang = localStorage.getItem('wedding_lang') || 'en';
        setLang(savedLang);

        const statusSelect = document.getElementById('status');
        const attendeeSection = document.getElementById('attendee-section');
        const rsvpForm = document.getElementById('rsvpForm');
        const formResponse = document.getElementById('formResponse');
        const submitButton = rsvpForm ? rsvpForm.querySelector('button[type="submit"]') : null;

        function getActiveLang() {
            return localStorage.getItem('wedding_lang') || 'en';
        }

        function setFormMessage(message, isSuccess) {
            formResponse.style.display = 'block';
            formResponse.textContent = message;
            formResponse.style.backgroundColor = isSuccess ? '#d4edda' : '#f8d7da';
            formResponse.style.color = isSuccess ? '#155724' : '#721c24';
        }

        function updateAttendeeSection() {
            if (!statusSelect || !attendeeSection) {
                return;
            }

            attendeeSection.style.display = statusSelect.value === 'accepted' ? 'block' : 'none';
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', updateAttendeeSection);
            updateAttendeeSection();
        }

        if (rsvpForm) {
            rsvpForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const lang = getActiveLang();
                const formData = new FormData(this);
                const data = {
                    guest_hash: formData.get('guest_hash'),
                    status: formData.get('status'),
                    attending_members: formData.getAll('attending_members[]'),
                    lang
                };

                if (!data.status) {
                    setFormMessage(i18n[lang].rsvp_status_required, false);
                    return;
                }

                if (data.status === 'accepted' && data.attending_members.length === 0) {
                    setFormMessage(i18n[lang].rsvp_attendee_required, false);
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch('api/rsvp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
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
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        }
    </script>
</body>
</html>
