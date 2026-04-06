<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money_format_local(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function nav_items(): array
{
    return [
        'conference.php' => 'Home',
        'committee.php' => 'Committees',
        'rooms.php' => 'Hotel Rooms',
        'schedule.php' => 'Schedule',
        'companies.php' => 'Sponsors',
        'jobs.php' => 'Jobs',
        'attendees.php' => 'Attendees',
        'finance.php' => 'Finance',
        'sessions.php' => 'Sessions',
    ];
}

function flash_message(): ?array
{
    if (!isset($_GET['status'], $_GET['message'])) {
        return null;
    }

    return [
        'status' => (string) $_GET['status'],
        'message' => (string) $_GET['message'],
    ];
}

function redirect_with_message(string $page, string $status, string $message, array $extra = []): void
{
    $params = array_merge($extra, [
        'status' => $status,
        'message' => $message,
    ]);

    header('Location: ' . $page . '?' . http_build_query($params));
    exit;
}

function render_header(string $title, string $activePage): void
{
    $flash = flash_message();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($title); ?> | Conference Database</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <header class="site-header">
        <div class="container nav-wrap">
            <div>
                <p class="eyebrow">Conference Organizer Portal</p>
                <h1 class="site-title">Conference Database Interface</h1>
            </div>
            <nav class="site-nav">
                <?php foreach (nav_items() as $file => $label): ?>
                    <a class="nav-link<?php echo $file === $activePage ? ' active' : ''; ?>" href="<?php echo e($file); ?>"><?php echo e($label); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>
    <main class="container page-shell">
        <section class="page-heading">
            <h2><?php echo e($title); ?></h2>
        </section>
        <?php if ($flash !== null): ?>
            <div class="alert <?php echo $flash['status'] === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">
        <div class="container footer-content">
            <p>Built with PHP, PDO, HTML, and CSS for the CISC332 conference database project.</p>
        </div>
    </footer>
    </body>
    </html>
    <?php
}

function fetch_all_rows(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one_row(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function fetch_single_value(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function level_to_amount(string $level): float
{
    $map = [
        'Platinum' => 5000.0,
        'Gold' => 3000.0,
        'Silver' => 1500.0,
        'Bronze' => 500.0,
    ];

    return $map[$level] ?? 0.0;
}
