<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$jobs = fetch_all_rows(
    'SELECT JobID, JobTitle, JobCity, JobProv, JobPay, CompName
     FROM JobAd
     ORDER BY CompName, JobTitle'
);

render_header('All Job Listings', 'jobs.php');
?>
<section class="table-card">
    <h3>All Available Jobs</h3>
    <?php if ($jobs === []): ?>
        <p class="empty-state">There are no job listings available at this time.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Job ID</th>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>City</th>
                        <th>Province</th>
                        <th>Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?php echo e((string) $job['JobID']); ?></td>
                            <td><?php echo e($job['CompName']); ?></td>
                            <td><?php echo e($job['JobTitle']); ?></td>
                            <td><?php echo e($job['JobCity']); ?></td>
                            <td><?php echo e($job['JobProv']); ?></td>
                            <td><?php echo e(money_format_local((float) $job['JobPay'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
