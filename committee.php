<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$committees = fetch_all_rows(
    'SELECT sc.ComName, sc.subDesc, cm.MemName AS ChairName
     FROM SubCommittee sc
     JOIN CommMember cm ON sc.Chair_MemberID = cm.MemberID
     ORDER BY sc.ComName'
);

$selectedCommittee = isset($_GET['committee']) ? trim((string) $_GET['committee']) : '';

if ($selectedCommittee === '' && $committees !== []) {
    $selectedCommittee = $committees[0]['ComName'];
}

$committeeInfo = null;
$members = [];

if ($selectedCommittee !== '') {
    $committeeInfo = fetch_one_row(
        'SELECT sc.ComName, sc.subDesc, cm.MemName AS ChairName
         FROM SubCommittee sc
         JOIN CommMember cm ON sc.Chair_MemberID = cm.MemberID
         WHERE sc.ComName = ?',
        [$selectedCommittee]
    );

    $members = fetch_all_rows(
        'SELECT cm.MemberID, cm.MemName
         FROM Committee_Members m
         JOIN CommMember cm ON m.MemberID = cm.MemberID
         WHERE m.ComName = ?
         ORDER BY cm.MemName',
        [$selectedCommittee]
    );
}

render_header('Committee Members', 'committee.php');
?>
<section class="grid grid-2">
    <article class="form-card">
        <h3>Select a Sub-Committee</h3>
        <form method="get">
            <div class="form-grid">
                <div class="field-full">
                    <label for="committee">Sub-Committee</label>
                    <select name="committee" id="committee">
                        <?php foreach ($committees as $committee): ?>
                            <option value="<?php echo e($committee['ComName']); ?>"<?php echo $committee['ComName'] === $selectedCommittee ? ' selected' : ''; ?>>
                                <?php echo e($committee['ComName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-full">
                    <button type="submit">Show Members</button>
                </div>
            </div>
        </form>
    </article>

    <article class="card">
        <h3>Committee Details</h3>
        <?php if ($committeeInfo === null): ?>
            <p class="empty-state">No committee information is available.</p>
        <?php else: ?>
            <p><strong>Name:</strong> <?php echo e($committeeInfo['ComName']); ?></p>
            <p><strong>Chair:</strong> <?php echo e($committeeInfo['ChairName']); ?></p>
            <p><strong>Description:</strong> <?php echo e((string) $committeeInfo['subDesc']); ?></p>
        <?php endif; ?>
    </article>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Members</h3>
    <?php if ($members === []): ?>
        <p class="empty-state">No members were found for the selected sub-committee.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo e((string) $member['MemberID']); ?></td>
                            <td><?php echo e($member['MemName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
