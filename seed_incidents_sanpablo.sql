-- =============================================================
-- survAIval - Seed test data for Barangay San Pablo, Sto. Tomas, Batangas
-- Run in phpMyAdmin against database: dbsurvAIval
-- =============================================================
-- Center: 14.1074° N, 121.1416° E
-- (Coords below are real points within ~1.5 km of the barangay center)

USE `dbsurvAIval`;

-- Replace the user_id below with YOUR actual logged-in user id.
-- Find it in phpMyAdmin: SELECT user_id, fname, lname FROM tblusers;
SET @my_user := (SELECT user_id FROM tblusers ORDER BY user_id ASC LIMIT 1);

-- If you want to use a specific user id, uncomment this:
-- SET @my_user := 1;

-- Clear any existing seed rows for this user (safety check)
DELETE FROM `tblreports`
WHERE `user_id` = @my_user
  AND `incident_title` IN (
    'Small fire at residential area',
    'Minor flooding on San Pablo street',
    'Medical emergency near covered court',
    'Suspicious activity near sari-sari store',
    'Cleanup drive after light flood'
  );

-- Insert 5 sample incidents with realistic coordinates
INSERT INTO `tblreports`
  (`user_id`, `reporter_name`, `contact_number`, `incident_title`, `incident_type`,
   `location`, `description`, `latitude`, `longitude`, `status`)
VALUES
  (@my_user, 'Juan Dela Cruz',   '09171234567',
   'Small fire at residential area',
   'Fire',
   'Purok 1, Brgy. San Pablo, Sto. Tomas, Batangas',
   'A small kitchen fire was contained by residents. No injuries reported. BFP was notified.',
   14.10812, 121.14189, 'resolved'),

  (@my_user, 'Maria Santos',     '09181234567',
   'Minor flooding on San Pablo street',
   'Flood',
   'Brgy. San Pablo Main Road, Sto. Tomas, Batangas',
   'Heavy rain caused minor flooding along the main road. Passable but slow.',
   14.10745, 121.14245, 'responding'),

  (@my_user, 'Pedro Reyes',      '09191234567',
   'Medical emergency near covered court',
   'Medical',
   'Covered Court, Brgy. San Pablo, Sto. Tomas, Batangas',
   'Resident collapsed during the basketball game. Responder team on the way.',
   14.10687, 121.14105, 'pending'),

  (@my_user, 'Ana Garcia',       '09201234567',
   'Suspicious activity near sari-sari store',
   'Crime',
   'Corner Store, Purok 3, Brgy. San Pablo',
   'Two unidentified individuals were seen loitering. Barangay tanod alerted.',
   14.10855, 121.14078, 'pending'),

  (@my_user, 'Jose Mendoza',     '09211234567',
   'Cleanup drive after light flood',
   'Flood',
   'Riverside, Brgy. San Pablo, Sto. Tomas, Batangas',
   'Cleanup drive scheduled after the flood water receded.',
   14.10923, 121.14212, 'resolved');

-- Quick check
SELECT report_id, incident_type, incident_title, latitude, longitude, status
FROM tblreports
WHERE user_id = @my_user
ORDER BY report_id DESC;
