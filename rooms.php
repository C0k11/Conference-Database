<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$rooms = fetch_all_rows(
    'SELECT hr.RoomNumber, hr.BedCount, COUNT(s.AttendeeID) AS Occupancy
     FROM HotelRoom hr
     LEFT JOIN Student s ON hr.RoomNumber = s.RoomNumber
     GROUP BY hr.RoomNumber, hr.BedCount
     ORDER BY hr.RoomNumber'
);

$selectedRoom = isset($_GET['room']) ? (string) $_GET['room'] : '';

if ($selectedRoom === '' && $rooms !== []) {
    $selectedRoom = (string) $rooms[0]['RoomNumber'];
}

$roomInfo = null;
$students = [];

if ($selectedRoom !== '') {
    $roomInfo = fetch_one_row(
        'SELECT hr.RoomNumber, hr.BedCount, COUNT(s.AttendeeID) AS Occupancy
         FROM HotelRoom hr
         LEFT JOIN Student s ON hr.RoomNumber = s.RoomNumber
         WHERE hr.RoomNumber = ?
         GROUP BY hr.RoomNumber, hr.BedCount',
        [$selectedRoom]
    );

    $students = fetch_all_rows(
        'SELECT a.AttendeeID, a.AttendeeName
         FROM Student s
         JOIN Attendee a ON s.AttendeeID = a.AttendeeID
         WHERE s.RoomNumber = ?
         ORDER BY a.AttendeeName',
        [$selectedRoom]
    );
}

render_header('Hotel Room Assignments', 'rooms.php');
?>
<section class="grid grid-2">
    <article class="form-card">
        <h3>Select a Room</h3>
        <form method="get">
            <div class="form-grid">
                <div class="field-full">
                    <label for="room">Room Number</label>
                    <select name="room" id="room">
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo e((string) $room['RoomNumber']); ?>"<?php echo (string) $room['RoomNumber'] === $selectedRoom ? ' selected' : ''; ?>>
                                Room <?php echo e((string) $room['RoomNumber']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-full">
                    <button type="submit">Show Students</button>
                </div>
            </div>
        </form>
    </article>

    <article class="card">
        <h3>Room Summary</h3>
        <?php if ($roomInfo === null): ?>
            <p class="empty-state">No room details are available.</p>
        <?php else: ?>
            <p><strong>Room Number:</strong> <?php echo e((string) $roomInfo['RoomNumber']); ?></p>
            <p><strong>Bed Count:</strong> <?php echo e((string) $roomInfo['BedCount']); ?></p>
            <p><strong>Current Occupancy:</strong> <?php echo e((string) $roomInfo['Occupancy']); ?></p>
        <?php endif; ?>
    </article>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Students in This Room</h3>
    <?php if ($students === []): ?>
        <p class="empty-state">No students are currently assigned to this room.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Attendee ID</th>
                        <th>Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo e((string) $student['AttendeeID']); ?></td>
                            <td><?php echo e($student['AttendeeName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>All Hotel Rooms</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Bed Count</th>
                    <th>Occupancy</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?php echo e((string) $room['RoomNumber']); ?></td>
                        <td><?php echo e((string) $room['BedCount']); ?></td>
                        <td><?php echo e((string) $room['Occupancy']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
render_footer();
