<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$stats = [
    'attendees' => (int) fetch_single_value('SELECT COUNT(*) FROM Attendee'),
    'companies' => (int) fetch_single_value('SELECT COUNT(*) FROM Company'),
    'sessions' => (int) fetch_single_value('SELECT COUNT(*) FROM Session'),
];

render_header('Home', 'conference.php');
?>
<section class="hero">
    <div>
        <h3>Manage conference operations from one place.</h3>
        <p>
            This portal helps conference organizers review committee membership, hotel assignments,
            schedules, sponsors, jobs, attendee categories, finances, and session updates using a single
            conference database.
        </p>
        <p>
            Use the navigation above or the quick links below to move through the required project features.
        </p>
    </div>
    <div>
        <img src="images/conference-banner.svg" alt="Conference hall illustration">
    </div>
</section>

<section class="grid grid-3">
    <article class="stat-card">
        <h3>Total Attendees</h3>
        <p class="stat-number"><?php echo e((string) $stats['attendees']); ?></p>
    </article>
    <article class="stat-card">
        <h3>Sponsoring Companies</h3>
        <p class="stat-number"><?php echo e((string) $stats['companies']); ?></p>
    </article>
    <article class="stat-card">
        <h3>Scheduled Sessions</h3>
        <p class="stat-number"><?php echo e((string) $stats['sessions']); ?></p>
    </article>
</section>

<section class="grid grid-3" style="margin-top: 1.5rem;">
    <article class="card">
        <h3>Committees</h3>
        <p>Browse sub-committees and view the members responsible for each organizing area.</p>
        <a class="quick-link" href="committee.php">Open committee tools</a>
    </article>
    <article class="card">
        <h3>Hotel Rooms</h3>
        <p>Check which students are assigned to each hotel room used by the conference.</p>
        <a class="quick-link" href="rooms.php">View room assignments</a>
    </article>
    <article class="card">
        <h3>Schedule</h3>
        <p>Display the conference schedule for a selected day and review all session timing.</p>
        <a class="quick-link" href="schedule.php">Review schedule</a>
    </article>
    <article class="card">
        <h3>Sponsors</h3>
        <p>List sponsors by level, manage companies, and inspect company-specific job postings.</p>
        <a class="quick-link" href="companies.php">Manage sponsors</a>
    </article>
    <article class="card">
        <h3>Attendees</h3>
        <p>See students, professionals, and sponsor representatives in separate lists and add new attendees.</p>
        <a class="quick-link" href="attendees.php">Open attendee tools</a>
    </article>
    <article class="card">
        <h3>Finance & Sessions</h3>
        <p>Check conference intake totals and switch session day, time, or location when needed.</p>
        <a class="quick-link" href="finance.php">Open finance page</a><br>
        <a class="quick-link" href="sessions.php">Open session updates</a>
    </article>
</section>
<?php
render_footer();
