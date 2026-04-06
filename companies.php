<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_company') {
            $companyName = trim((string) ($_POST['comp_name'] ?? ''));
            $sponsorLevel = trim((string) ($_POST['sponsor_level'] ?? ''));
            $promoLimit = (int) ($_POST['promo_limit'] ?? 0);

            if ($companyName === '' || $sponsorLevel === '') {
                throw new RuntimeException('Company name and sponsor level are required.');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO Company (CompName, SponsorLevel, PromoLimit) VALUES (?, ?, ?)'
            );
            $stmt->execute([$companyName, $sponsorLevel, $promoLimit]);

            redirect_with_message('companies.php', 'success', 'Sponsoring company added successfully.', [
                'company' => $companyName,
            ]);
        }

        if ($action === 'delete_company') {
            $companyName = trim((string) ($_POST['company_to_delete'] ?? ''));

            if ($companyName === '') {
                throw new RuntimeException('Please select a company to delete.');
            }

            $pdo->beginTransaction();

            $attendeeStmt = $pdo->prepare('SELECT AttendeeID FROM SponsorRep WHERE CompName = ?');
            $attendeeStmt->execute([$companyName]);
            $attendeeIds = $attendeeStmt->fetchAll(PDO::FETCH_COLUMN);

            if ($attendeeIds !== []) {
                $placeholders = implode(', ', array_fill(0, count($attendeeIds), '?'));
                $deleteAttendees = $pdo->prepare('DELETE FROM Attendee WHERE AttendeeID IN (' . $placeholders . ')');
                $deleteAttendees->execute($attendeeIds);
            }

            $deleteCompany = $pdo->prepare('DELETE FROM Company WHERE CompName = ?');
            $deleteCompany->execute([$companyName]);

            if ($deleteCompany->rowCount() === 0) {
                throw new RuntimeException('The selected company could not be found.');
            }

            $pdo->commit();
            redirect_with_message('companies.php', 'success', 'Company and associated sponsor attendees deleted successfully.');
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        redirect_with_message('companies.php', 'error', $exception->getMessage());
    }
}

$companies = fetch_all_rows(
    'SELECT c.CompName, c.SponsorLevel, c.PromoLimit, COUNT(sr.AttendeeID) AS RepresentativeCount
     FROM Company c
     LEFT JOIN SponsorRep sr ON c.CompName = sr.CompName
     GROUP BY c.CompName, c.SponsorLevel, c.PromoLimit
     ORDER BY c.CompName'
);

$selectedCompany = isset($_GET['company']) ? trim((string) $_GET['company']) : '';

if ($selectedCompany === '' && $companies !== []) {
    $selectedCompany = $companies[0]['CompName'];
}

$companyJobs = [];

if ($selectedCompany !== '') {
    $companyJobs = fetch_all_rows(
        'SELECT JobTitle, JobCity, JobProv, JobPay
         FROM JobAd
         WHERE CompName = ?
         ORDER BY JobTitle',
        [$selectedCompany]
    );
}

render_header('Sponsors and Company Management', 'companies.php');
?>
<section class="table-card">
    <h3>Current Sponsors</h3>
    <?php if ($companies === []): ?>
        <p class="empty-state">No sponsoring companies are currently stored in the database.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Sponsorship Level</th>
                        <th>Promo Limit</th>
                        <th>Sponsor Reps</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?php echo e($company['CompName']); ?></td>
                            <td><?php echo e($company['SponsorLevel']); ?></td>
                            <td><?php echo e((string) $company['PromoLimit']); ?></td>
                            <td><?php echo e((string) $company['RepresentativeCount']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="grid grid-2" style="margin-top: 1.5rem;">
    <article class="form-card">
        <h3>Jobs by Company</h3>
        <form method="get">
            <div class="form-grid">
                <div class="field-full">
                    <label for="company">Company</label>
                    <select name="company" id="company">
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo e($company['CompName']); ?>"<?php echo $company['CompName'] === $selectedCompany ? ' selected' : ''; ?>>
                                <?php echo e($company['CompName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-full">
                    <button type="submit">Show Jobs</button>
                </div>
            </div>
        </form>
    </article>

    <article class="form-card">
        <h3>Add a Sponsoring Company</h3>
        <form method="post">
            <input type="hidden" name="action" value="add_company">
            <div class="form-grid">
                <div class="field">
                    <label for="comp_name">Company Name</label>
                    <input type="text" id="comp_name" name="comp_name" required>
                </div>
                <div class="field">
                    <label for="sponsor_level">Sponsorship Level</label>
                    <select id="sponsor_level" name="sponsor_level" required>
                        <option value="Platinum">Platinum</option>
                        <option value="Gold">Gold</option>
                        <option value="Silver">Silver</option>
                        <option value="Bronze">Bronze</option>
                    </select>
                </div>
                <div class="field-full">
                    <label for="promo_limit">Promo Limit</label>
                    <input type="number" id="promo_limit" name="promo_limit" min="0" value="0" required>
                </div>
                <div class="field-full">
                    <button type="submit">Add Company</button>
                </div>
            </div>
        </form>
    </article>
</section>

<section class="grid grid-2" style="margin-top: 1.5rem;">
    <article class="table-card">
        <h3>Jobs for <?php echo $selectedCompany !== '' ? e($selectedCompany) : 'Selected Company'; ?></h3>
        <?php if ($companyJobs === []): ?>
            <p class="empty-state">No jobs were found for the selected company.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>City</th>
                            <th>Province</th>
                            <th>Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companyJobs as $job): ?>
                            <tr>
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
    </article>

    <article class="form-card">
        <h3>Delete a Sponsoring Company</h3>
        <p>Deleting a company will also delete its sponsor representatives, job ads, and sent emails.</p>
        <form method="post">
            <input type="hidden" name="action" value="delete_company">
            <div class="form-grid">
                <div class="field-full">
                    <label for="company_to_delete">Company</label>
                    <select id="company_to_delete" name="company_to_delete">
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo e($company['CompName']); ?>"><?php echo e($company['CompName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-full">
                    <button class="button-secondary" type="submit">Delete Company</button>
                </div>
            </div>
        </form>
    </article>
</section>
<?php
render_footer();
