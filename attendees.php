<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_attendee') {
        try {
            $attendeeName = trim((string) ($_POST['attendee_name'] ?? ''));
            $attendeeType = trim((string) ($_POST['attendee_type'] ?? ''));
            $roomNumber = trim((string) ($_POST['room_number'] ?? ''));
            $companyName = trim((string) ($_POST['company_name'] ?? ''));

            if ($attendeeName === '' || $attendeeType === '') {
                throw new RuntimeException('Attendee name and type are required.');
            }

            $pdo->beginTransaction();

            $nextId = (int) $pdo->query('SELECT COALESCE(MAX(AttendeeID), 0) + 1 FROM Attendee')->fetchColumn();

            $insertAttendee = $pdo->prepare('INSERT INTO Attendee (AttendeeID, AttendeeName) VALUES (?, ?)');
            $insertAttendee->execute([$nextId, $attendeeName]);

            if ($attendeeType === 'student') {
                if ($roomNumber === '') {
                    throw new RuntimeException('A hotel room is required for student attendees.');
                }

                $insertStudent = $pdo->prepare('INSERT INTO Student (AttendeeID, Fee, RoomNumber) VALUES (?, ?, ?)');
                $insertStudent->execute([$nextId, 50.00, (int) $roomNumber]);
            } elseif ($attendeeType === 'professional') {
                $insertProfessional = $pdo->prepare('INSERT INTO Professional (AttendeeID, Fee) VALUES (?, ?)');
                $insertProfessional->execute([$nextId, 100.00]);
            } elseif ($attendeeType === 'sponsor') {
                if ($companyName === '') {
                    throw new RuntimeException('A sponsoring company is required for sponsor representatives.');
                }

                $insertSponsor = $pdo->prepare('INSERT INTO SponsorRep (AttendeeID, CompName) VALUES (?, ?)');
                $insertSponsor->execute([$nextId, $companyName]);
            } else {
                throw new RuntimeException('The selected attendee type is invalid.');
            }

            $pdo->commit();
            redirect_with_message('attendees.php', 'success', 'Attendee added successfully.');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            redirect_with_message('attendees.php', 'error', $exception->getMessage());
        }
    }
}

$rooms = fetch_all_rows(
    'SELECT hr.RoomNumber, hr.BedCount, COUNT(s.AttendeeID) AS Occupancy
     FROM HotelRoom hr
     LEFT JOIN Student s ON hr.RoomNumber = s.RoomNumber
     GROUP BY hr.RoomNumber, hr.BedCount
     ORDER BY hr.RoomNumber'
);

$companies = fetch_all_rows('SELECT CompName FROM Company ORDER BY CompName');

$students = fetch_all_rows(
    'SELECT a.AttendeeID, a.AttendeeName, s.Fee, s.RoomNumber
     FROM Attendee a
     JOIN Student s ON a.AttendeeID = s.AttendeeID
     ORDER BY a.AttendeeName'
);

$professionals = fetch_all_rows(
    'SELECT a.AttendeeID, a.AttendeeName, p.Fee
     FROM Attendee a
     JOIN Professional p ON a.AttendeeID = p.AttendeeID
     ORDER BY a.AttendeeName'
);

$sponsors = fetch_all_rows(
    'SELECT a.AttendeeID, a.AttendeeName, sr.CompName
     FROM Attendee a
     JOIN SponsorRep sr ON a.AttendeeID = sr.AttendeeID
     ORDER BY a.AttendeeName'
);

render_header('Attendees', 'attendees.php');
?>
<section class="grid grid-3">
    <article class="stat-card">
        <h3>Students</h3>
        <p class="stat-number"><?php echo e((string) count($students)); ?></p>
    </article>
    <article class="stat-card">
        <h3>Professionals</h3>
        <p class="stat-number"><?php echo e((string) count($professionals)); ?></p>
    </article>
    <article class="stat-card">
        <h3>Sponsor Representatives</h3>
        <p class="stat-number"><?php echo e((string) count($sponsors)); ?></p>
    </article>
</section>

<section class="form-card" style="margin-top: 1.5rem;">
    <h3>Add a New Attendee</h3>
    <form method="post">
        <input type="hidden" name="action" value="add_attendee">
        <div class="form-grid">
            <div class="field">
                <label for="attendee_name">Attendee Name</label>
                <input type="text" id="attendee_name" name="attendee_name" required>
            </div>
            <div class="field">
                <label for="attendee_type">Attendee Type</label>
                <select id="attendee_type" name="attendee_type" required>
                    <option value="student">Student</option>
                    <option value="professional">Professional</option>
                    <option value="sponsor">Sponsor Representative</option>
                </select>
            </div>
            <div class="field">
                <label for="room_number">Hotel Room</label>
                <select id="room_number" name="room_number">
                    <option value="">Not applicable</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo e((string) $room['RoomNumber']); ?>">
                            Room <?php echo e((string) $room['RoomNumber']); ?> (<?php echo e((string) $room['Occupancy']); ?>/<?php echo e((string) $room['BedCount']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="company_name">Sponsor Company</label>
                <select id="company_name" name="company_name">
                    <option value="">Not applicable</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?php echo e($company['CompName']); ?>"><?php echo e($company['CompName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field-full">
                <button type="submit">Add Attendee</button>
            </div>
        </div>
    </form>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Student Attendees</h3>
    <?php if ($students === []): ?>
        <p class="empty-state">No student attendees were found.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Attendee ID</th>
                        <th>Name</th>
                        <th>Fee</th>
                        <th>Room Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo e((string) $student['AttendeeID']); ?></td>
                            <td><?php echo e($student['AttendeeName']); ?></td>
                            <td><?php echo e(money_format_local((float) $student['Fee'])); ?></td>
                            <td><?php echo $student['RoomNumber'] !== null ? e((string) $student['RoomNumber']) : 'Unassigned'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Professional Attendees</h3>
    <?php if ($professionals === []): ?>
        <p class="empty-state">No professional attendees were found.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Attendee ID</th>
                        <th>Name</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professionals as $professional): ?>
                        <tr>
                            <td><?php echo e((string) $professional['AttendeeID']); ?></td>
                            <td><?php echo e($professional['AttendeeName']); ?></td>
                            <td><?php echo e(money_format_local((float) $professional['Fee'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Sponsor Representatives</h3>
    <?php if ($sponsors === []): ?>
        <p class="empty-state">No sponsor representatives were found.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Attendee ID</th>
                        <th>Name</th>
                        <th>Company</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $sponsor): ?>
                        <tr>
                            <td><?php echo e((string) $sponsor['AttendeeID']); ?></td>
                            <td><?php echo e($sponsor['AttendeeName']); ?></td>
                            <td><?php echo e($sponsor['CompName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
