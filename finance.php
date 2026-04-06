<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$studentSummary = fetch_one_row(
    'SELECT COUNT(*) AS attendee_count, COALESCE(SUM(Fee), 0) AS total_fee FROM Student'
) ?? ['attendee_count' => 0, 'total_fee' => 0];

$professionalSummary = fetch_one_row(
    'SELECT COUNT(*) AS attendee_count, COALESCE(SUM(Fee), 0) AS total_fee FROM Professional'
) ?? ['attendee_count' => 0, 'total_fee' => 0];

$companyBreakdown = fetch_all_rows(
    'SELECT CompName, SponsorLevel, PromoLimit
     FROM Company
     ORDER BY SponsorLevel, CompName'
);

$sponsorshipTotal = 0.0;

foreach ($companyBreakdown as &$company) {
    $company['SponsorAmount'] = level_to_amount($company['SponsorLevel']);
    $sponsorshipTotal += $company['SponsorAmount'];
}
unset($company);

$studentTotal = (float) $studentSummary['total_fee'];
$professionalTotal = (float) $professionalSummary['total_fee'];
$registrationTotal = $studentTotal + $professionalTotal;
$grandTotal = $registrationTotal + $sponsorshipTotal;

render_header('Conference Intake', 'finance.php');
?>
<section class="grid grid-2">
    <article class="stat-card">
        <h3>Total Registration Amount</h3>
        <p class="stat-number"><?php echo e(money_format_local($registrationTotal)); ?></p>
        <p>Students: <?php echo e(money_format_local($studentTotal)); ?> | Professionals: <?php echo e(money_format_local($professionalTotal)); ?></p>
    </article>
    <article class="stat-card">
        <h3>Total Sponsorship Amount</h3>
        <p class="stat-number"><?php echo e(money_format_local($sponsorshipTotal)); ?></p>
        <p>Derived from the sponsor level of each company in the database.</p>
    </article>
</section>

<section class="grid grid-3" style="margin-top: 1.5rem;">
    <article class="stat-card">
        <h3>Student Registrations</h3>
        <p class="stat-number"><?php echo e((string) $studentSummary['attendee_count']); ?></p>
    </article>
    <article class="stat-card">
        <h3>Professional Registrations</h3>
        <p class="stat-number"><?php echo e((string) $professionalSummary['attendee_count']); ?></p>
    </article>
    <article class="stat-card">
        <h3>Grand Total Intake</h3>
        <p class="stat-number"><?php echo e(money_format_local($grandTotal)); ?></p>
    </article>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Registration Breakdown</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Attendees</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Students</td>
                    <td><?php echo e((string) $studentSummary['attendee_count']); ?></td>
                    <td><?php echo e(money_format_local($studentTotal)); ?></td>
                </tr>
                <tr>
                    <td>Professionals</td>
                    <td><?php echo e((string) $professionalSummary['attendee_count']); ?></td>
                    <td><?php echo e(money_format_local($professionalTotal)); ?></td>
                </tr>
                <tr>
                    <td><strong>Total Registration</strong></td>
                    <td><?php echo e((string) ((int) $studentSummary['attendee_count'] + (int) $professionalSummary['attendee_count'])); ?></td>
                    <td><strong><?php echo e(money_format_local($registrationTotal)); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="table-card" style="margin-top: 1.5rem;">
    <h3>Sponsorship Breakdown</h3>
    <?php if ($companyBreakdown === []): ?>
        <p class="empty-state">No sponsoring companies are available.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Level</th>
                        <th>Promo Limit</th>
                        <th>Computed Sponsorship Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companyBreakdown as $company): ?>
                        <tr>
                            <td><?php echo e($company['CompName']); ?></td>
                            <td><?php echo e($company['SponsorLevel']); ?></td>
                            <td><?php echo e((string) $company['PromoLimit']); ?></td>
                            <td><?php echo e(money_format_local((float) $company['SponsorAmount'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>Total Sponsorship</strong></td>
                        <td><strong><?php echo e(money_format_local($sponsorshipTotal)); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
render_footer();
