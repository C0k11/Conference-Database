<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_session') {
        try {
            $sessionId = (int) ($_POST['session_id'] ?? 0);
            $sessionDate = trim((string) ($_POST['session_date'] ?? ''));
            $sessionStart = trim((string) ($_POST['session_start'] ?? ''));
            $sessionEnd = trim((string) ($_POST['session_end'] ?? ''));
            $sessionLocation = trim((string) ($_POST['session_location'] ?? ''));

            if ($sessionId <= 0 || $sessionDate === '' || $sessionStart === '' || $sessionEnd === '' || $sessionLocation === '') {
                throw new RuntimeException('All session fields are required.');
            }

            $existingSessionCount = (int) fetch_single_value(
                'SELECT COUNT(*) FROM Session WHERE sessID = ?',
                [$sessionId]
            );

            if ($existingSessionCount === 0) {
                throw new RuntimeException('The selected session could not be found.');
            }

            $stmt = db()->prepare(
                'UPDATE Session
                 SET sessDate = ?, sessStart = ?, sessEnd = ?, sessLoc = ?
                 WHERE sessID = ?'
            );
            $stmt->execute([$sessionDate, $sessionStart, $sessionEnd, $sessionLocation, $sessionId]);

            redirect_with_message('sessions.php', 'success', 'Session updated successfully.', [
                'session_id' => (string) $sessionId,
            ]);
        } catch (Throwable $exception) {
            redirect_with_message('sessions.php', 'error', $exception->getMessage());
        }
    }
}

$sessions = fetch_all_rows(
    'SELECT sessID, sessTitle, sessDate, sessLoc, sessStart, sessEnd
     FROM Session
     ORDER BY sessDate, sessStart, sessTitle'
);

$selectedSessionId = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;

if ($selectedSessionId === 0 && $sessions !== []) {
    $selectedSessionId = (int) $sessions[0]['sessID'];
}

$selectedSession = null;

if ($selectedSessionId > 0) {
    $selectedSession = fetch_one_row(
        'SELECT sessID, sessTitle, sessDate, sessLoc, sessStart, sessEnd
         FROM Session
         WHERE sessID = ?',
        [$selectedSessionId]
    );
}

render_header('Session Updates', 'sessions.php');
?>
<section class="grid grid-2">
    <article class="form-card">
        <h3>Select a Session</h3>
        <form method="get">
            <div class="form-grid">
                <div class="field-full">
                    <label for="session_id">Session</label>
                    <select name="session_id" id="session_id">
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo e((string) $session['sessID']); ?>"<?php echo (int) $session['sessID'] === $selectedSessionId ? ' selected' : ''; ?>>
                                <?php echo e((string) $session['sessID']); ?> - <?php echo e($session['sessTitle']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-full">
                    <button type="submit">Load Session</button>
                </div>
            </div>
        </form>
    </article>

    <article class="card">
        <h3>Current Session Details</h3>
        <?php if ($selectedSession === null): ?>
            <p class="empty-state">No session is currently selected.</p>
        <?php else: ?>
            <p><strong>Title:</strong> <?php echo e($selectedSession['sessTitle']); ?></p>
            <p><strong>Date:</strong> <?php echo e($selectedSession['sessDate']); ?></p>
            <p><strong>Time:</strong> <?php echo e(substr($selectedSession['sessStart'], 0, 5)); ?> - <?php echo e(substr($selectedSession['sessEnd'], 0, 5)); ?></p>
            <p><strong>Location:</strong> <?php echo e($selectedSession['sessLoc']); ?></p>
        <?php endif; ?>
    </article>
</section>

<section class="form-card" style="margin-top: 1.5rem;">
    <h3>Switch Session Day, Time, or Location</h3>
    <?php if ($selectedSession === null): ?>
        <p class="empty-state">Select a session first.</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="action" value="update_session">
            <input type="hidden" name="session_id" value="<?php echo e((string) $selectedSession['sessID']); ?>">
            <div class="form-grid">
                <div class="field">
                    <label for="session_date">New Date</label>
                    <input type="date" id="session_date" name="session_date" value="<?php echo e($selectedSession['sessDate']); ?>" required>
                </div>
                <div class="field">
                    <label for="session_location">New Location</label>
                    <input type="text" id="session_location" name="session_location" value="<?php echo e($selectedSession['sessLoc']); ?>" required>
                </div>
                <div class="field">
                    <label for="session_start">New Start Time</label>
                    <input type="time" id="session_start" name="session_start" value="<?php echo e(substr($selectedSession['sessStart'], 0, 5)); ?>" required>
                </div>
                <div class="field">
                    <label for="session_end">New End Time</label>
                    <input type="time" id="session_end" name="session_end" value="<?php echo e(substr($selectedSession['sessEnd'], 0, 5)); ?>" required>
                </div>
                <div class="field-full">
                    <button type="submit">Update Session</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>All Sessions</h3>
    <?php if ($sessions === []): ?>
        <p class="empty-state">No sessions are available in the database.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo e((string) $session['sessID']); ?></td>
                            <td><?php echo e($session['sessTitle']); ?></td>
                            <td><?php echo e($session['sessDate']); ?></td>
                            <td><?php echo e(substr($session['sessStart'], 0, 5)); ?></td>
                            <td><?php echo e(substr($session['sessEnd'], 0, 5)); ?></td>
                            <td><?php echo e($session['sessLoc']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
