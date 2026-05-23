<?php

function normalize_guest_value($value): string
{
    $value = preg_replace('/\s+/', ' ', (string) $value);
    return trim($value ?? '');
}

function decode_guest_list($value): array
{
    if (is_array($value)) {
        $items = $value;
    } elseif (is_string($value) && trim($value) !== '') {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $items = $decoded;
        } else {
            $items = explode(',', $value);
        }
    } else {
        $items = [];
    }

    $normalized = array_map('normalize_guest_value', $items);

    return array_values(array_filter($normalized, static function ($item) {
        return $item !== '';
    }));
}

function format_guest_person_name(?string $salutation, ?string $firstName, ?string $lastName): string
{
    return normalize_guest_value(implode(' ', array_filter([
        normalize_guest_value($salutation),
        normalize_guest_value($firstName),
        normalize_guest_value($lastName),
    ])));
}

function build_guest_primary_attendees(array $guest): array
{
    $attendees = [];

    $primaryOne = format_guest_person_name(
        $guest['salutation_1'] ?? '',
        $guest['first_name_1'] ?? '',
        $guest['last_name_1'] ?? ''
    );

    if ($primaryOne !== '') {
        $attendees[] = $primaryOne;
    }

    $primaryTwo = format_guest_person_name(
        $guest['salutation_2'] ?? '',
        $guest['first_name_2'] ?? '',
        $guest['last_name_2'] ?? ''
    );

    if ($primaryTwo !== '') {
        $attendees[] = $primaryTwo;
    }

    return $attendees;
}

function build_guest_family_attendees(array $guest): array
{
    return decode_guest_list($guest['family_members'] ?? []);
}

function build_guest_invited_attendees(array $guest): array
{
    return array_values(array_unique(array_merge(
        build_guest_primary_attendees($guest),
        build_guest_family_attendees($guest)
    )));
}

function build_smart_greeting(array $guest, string $lang = 'en'): string 
{
    $primaryAttendees = array_unique(build_guest_primary_attendees($guest));
    $count = count($primaryAttendees);
    $withFamily = !empty($guest['with_family']);
    
    if ($lang === 'de') {
        if ($count > 1 || $withFamily) {
            $prefix = "Sehr geehrte Gäste";
            if ($count > 0) {
                // Determine if it's a couple or family
                $names = implode(' & ', $primaryAttendees);
                if ($withFamily) $names .= " mit Familie";
                return "Sehr geehrte/r " . $names; 
                // Wait, "Sehr geehrte/r" is still generic.
            }
        }
        
        // Let's try a better approach for German
        $salutation1 = trim($guest['salutation_1'] ?? '');
        $isMale1 = in_array($salutation1, ['Mr.', 'Herr', 'Mr']);
        $isFemale1 = in_array($salutation1, ['Mrs.', 'Ms.', 'Frau', 'Mrs', 'Ms']);
        
        if ($count === 1) {
            if ($isMale1) $prefix = "Sehr geehrter Herr";
            elseif ($isFemale1) $prefix = "Sehr geehrte Frau";
            else $prefix = "Sehr geehrte/r";
            
            // For the name, we take out the salutation if it's Herr/Frau to avoid double
            $nameOnly = normalize_guest_value(($guest['first_name_1'] ?? '') . ' ' . ($guest['last_name_1'] ?? ''));
            if ($withFamily) $nameOnly .= " mit Familie";
            return $prefix . " " . $nameOnly;
        } else {
            // Multiple people or empty
            $names = implode(' & ', $primaryAttendees);
            if ($withFamily) $names .= " mit Familie";
            return "Sehr geehrte/r " . ($names ?: "Gast");
        }
    } else {
        // English
        $prefix = "Dear";
        $names = implode(' & ', $primaryAttendees);
        if ($names === '') $names = "Guest";
        if ($withFamily) $names .= " with family";
        return $prefix . " " . $names;
    }
}

function build_guest_greeting(array $guest): string
{
    // Keeping this for backward compatibility or simple cases, but using smart one in frontend
    $primaryAttendees = array_unique(build_guest_primary_attendees($guest));
    $names = implode(' & ', $primaryAttendees);
    if ($names === '') $names = 'Guest';
    if (!empty($guest['with_family'])) $names .= ' with family';
    return $names;
}

function build_guest_display_name(array $guest): string
{
    $displayName = implode(' & ', array_unique(build_guest_primary_attendees($guest)));

    if ($displayName === '') {
        $displayName = 'Guest';
    }

    if (!empty($guest['with_family'])) {
        $displayName .= ' with family';
    }

    return normalize_guest_value($displayName);
}

function filter_guest_attending_members(array $guest, array $submittedAttendees): array
{
    $allowedAttendees = build_guest_invited_attendees($guest);
    $requestedAttendees = array_fill_keys(decode_guest_list($submittedAttendees), true);

    $confirmedAttendees = [];
    foreach ($allowedAttendees as $attendee) {
        if (isset($requestedAttendees[$attendee])) {
            $confirmedAttendees[] = $attendee;
        }
    }

    return $confirmedAttendees;
}

function get_guest_confirmed_attendees(array $guest): array
{
    if (($guest['status'] ?? 'pending') !== 'accepted') {
        return [];
    }

    $storedAttendees = decode_guest_list($guest['attending_members'] ?? []);
    $version = isset($guest['attending_members_version']) ? (int) $guest['attending_members_version'] : 1;

    if ($version >= 2) {
        return filter_guest_attending_members($guest, $storedAttendees);
    }

    $confirmedAttendees = build_guest_primary_attendees($guest);
    $storedLookup = array_fill_keys($storedAttendees, true);

    foreach (build_guest_family_attendees($guest) as $familyMember) {
        if (isset($storedLookup[$familyMember])) {
            $confirmedAttendees[] = $familyMember;
        }
    }

    return array_values(array_unique($confirmedAttendees));
}

function count_guest_invited_people(array $guest): int
{
    return count(build_guest_invited_attendees($guest));
}

function count_guest_confirmed_people(array $guest): int
{
    return count(get_guest_confirmed_attendees($guest));
}
