<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$dates = fetch_all_rows('SELECT DISTINCT sessDate FROM Session ORDER BY sessDate');
$selectedDate = isset($_GET['date']) ? (string) $_GET['date'] : '';

if ($selectedDate === '' && $dates !== []) {
    $selectedDate = $dates[0]['sessDate'];
}

$schedule = [];

if ($selectedDate !== '') {
    $schedule = fetch_all_rows(
        "SELECT s.sessID, s.sessTitle, s.sessLoc, s.sessStart, s.sessEnd,
                COALESCE(a.AttendeeName, 'TBA') AS SpeakerName
         FROM Session s
         LEFT JOIN Speaks sp ON s.sessID = sp.sessID
         LEFT JOIN Attendee a ON sp.AttendeeID = a.AttendeeID
         WHERE s.sessDate = ?
         ORDER BY s.sessStart, s.sessTitle",
        [$selectedDate]
    );
}

render_header('Daily Conference Schedule', 'schedule.php');
?>
<section class="grid grid-2">
    <article class="form-card">
        <h3>Select a Day</h3>
        <form method="get">
            <div class="form-grid">
                <div class="field-full">
                    <label for="date">Conference Date</label>
                    <select name="date" id="date">
                        <?php foreach ($dates as $date): ?>
                            <option value="<?php echo e($date['sessDate']); ?>"<?php echo $date['sessDate'] === $selectedDate ? ' selected' : ''; ?>>
                                <?php echo e($date['sessDate']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-full">
                    <button type="submit">Show Schedule</button>
                </div>
            </div>
        </form>
    </article>

    <article class="card">
        <h3>Schedule Summary</h3>
        <p><strong>Selected Date:</strong> <?php echo $selectedDate !== '' ? e($selectedDate) : 'None'; ?></p>
        <p><strong>Sessions Found:</strong> <?php echo e((string) count($schedule)); ?></p>
        <p>The table below lists the sessions for the chosen day in chronological order.</p>
    </article>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Conference Schedule</h3>
    <?php if ($schedule === []): ?>
        <p class="empty-state">No sessions were found for the selected day.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Title</th>
                        <th>Speaker</th>
                        <th>Location</th>
                        <th>Start</th>
                        <th>End</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule as $session): ?>
                        <tr>
                            <td><?php echo e((string) $session['sessID']); ?></td>
                            <td><?php echo e($session['sessTitle']); ?></td>
                            <td><?php echo e($session['SpeakerName']); ?></td>
                            <td><?php echo e($session['sessLoc']); ?></td>
                            <td><?php echo e(substr($session['sessStart'], 0, 5)); ?></td>
                            <td><?php echo e(substr($session['sessEnd'], 0, 5)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
