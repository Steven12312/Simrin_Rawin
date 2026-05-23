# Wedding Website: Saymen & Disha

## Features
- **Personalized Links**: Each guest receives a unique URL (e.g., `wedding.com/?g=xyz123`).
- **Dynamic Content**: Shows specific invitation days (1, 2, or 3 days) based on the guest record.
- **Family RSVP**: Allows the main guest to RSVP for themselves and their invited family members.
- **Indian Aesthetic**: Modern design with traditional Indian elements.
- **SQL Backend**: Integration with ALL-INKL MySQL database.

## Technical Stack
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript.
- **Backend**: PHP (compatible with ALL-INKL).
- **Database**: MySQL.

## Database Schema (Concept)
### Table: `guests`
- `id`: INT AUTO_INCREMENT
- `guest_hash`: VARCHAR(32) UNIQUE (The unique identifier for the link)
- `salutation_1`, `first_name_1`, `last_name_1`: Main guest fields
- `salutation_2`, `first_name_2`, `last_name_2`: Optional second named guest
- `family_members`: JSON array of additional invited family members
- `with_family`: Whether the invite should show the "with family" suffix
- `invitation_days`: INT (1, 2, or 3 days)
- `status`: ENUM('pending', 'accepted', 'declined')
- `attending_members`: JSON array of everyone who confirmed attendance
- `attending_members_version`: Tracks the RSVP payload format for backward compatibility
- `message`: Optional RSVP note
- `updated_at`: TIMESTAMP

## Setup Instructions
1. Upload all files to ALL-INKL via FTP.
2. Create a MySQL database and run the `schema.sql`.
3. Copy `config.local.example.php` to `config.local.php` and fill in your database credentials, or provide the `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and optional `DB_CHARSET` environment variables on the server.
4. Open `/admin.php` once after setup to create the first admin account.

## GitHub Deploy Notes
- The deploy workflow uploads tracked files only. `config.local.php` is intentionally ignored and will not be deployed from your local machine.
- Add these GitHub repository secrets so the workflow can generate `config.local.php` during deploy: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, optional `DB_CHARSET`, plus your existing FTP secrets.
- After adding the secrets, trigger a new deploy by pushing to `main` or rerunning the workflow.

## Security Notes
- `config.local.php` is ignored via `.gitignore` so credentials do not need to live in the tracked source files.
- `APP_DEBUG=1` enables visible PHP errors for development. Leave it unset in production.
- The schema no longer creates a default admin user with a known password.
