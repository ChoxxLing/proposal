<?php
$formatDate = static function (string $date): string {
    return date('F j, Y', strtotime($date));
};

$formatTime = static function (string $time): string {
    return date('g:i A', strtotime($time));
};

$currentCount = count(array_filter($seminars, static fn (array $seminar): bool => $seminar['public_status'] === 'current'));
$upcomingCount = count($seminars) - $currentCount;
$firstSeminar = $seminars[0] ?? null;
$publicBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$assetBasePath = ($publicBasePath === '' ? '' : $publicBasePath) . '/assets';
$stylesPath = __DIR__ . '/../../../public/assets/css/styles.css';
$stylesVersion = file_exists($stylesPath) ? (string) filemtime($stylesPath) : (string) time();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seminar Schedule | Parenting Seminar Attendance</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBasePath) ?>/css/styles.css?v=<?= htmlspecialchars($stylesVersion) ?>">
</head>
<body class="public-screen">
    <header class="public-header">
        <a class="public-wordmark" href="index.php">AttendSmart</a>
        <a class="admin-link" href="admin.php">Login</a>
    </header>

    <main class="public-main">
        <section class="public-hero">
            <div class="hero-copy">
                <p class="hero-kicker">Growing together</p>
                <h1>Nurturing the <span>Next Generation</span></h1>
                <p>Welcome to a clear and caring space for parenting seminar schedules. View upcoming sessions, venues, and important details in one organized page.</p>
                <div class="hero-actions">
                    <a class="hero-primary" href="#seminars">View Upcoming Seminars</a>
                    <a class="hero-secondary" href="#approach">Our Approach</a>
                </div>
            </div>
            <div class="hero-visual" aria-label="Parenting seminar visual">
                <div class="family-illustration">
                    <span class="sun-shape"></span>
                    <span class="adult-face"></span>
                    <span class="adult-body"></span>
                    <span class="child-face"></span>
                    <span class="child-body"></span>
                </div>
                <div class="hero-note">
                    <span>PS</span>
                    <div>
                        <strong>Expert Guided</strong>
                        <small>Led by school facilitators</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="family-section" id="approach">
            <div class="section-heading centered">
                <h2>Designed for Busy Families</h2>
                <p>Simple tools and clear schedules help parents focus on every important session.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <span class="feature-icon">QR</span>
                    <h3>QR Code Attendance</h3>
                    <p>Fast check-in support for organized seminar attendance records.</p>
                    <div class="feature-visual qr-visual">
                        <span></span>
                    </div>
                </article>
                <article class="feature-card">
                    <span class="feature-icon bell">SMS</span>
                    <h3>Real-time Notifications</h3>
                    <p>Parents receive timely reminders and attendance updates for scheduled events.</p>
                    <div class="message-stack">
                        <span>Reminder: Parenting Seminar starts soon.</span>
                        <span>Attendance confirmed.</span>
                    </div>
                </article>
                <article class="feature-card">
                    <span class="feature-icon report">RP</span>
                    <h3>Expert Insights</h3>
                    <p>Seminar details stay accessible and easy to review before the session.</p>
                    <div class="feature-visual report-visual">
                        <span></span>
                    </div>
                </article>
            </div>
        </section>

        <section class="seminars-section" id="seminars">
        <div class="section-heading split-heading">
            <div>
                <h2>Upcoming Seminars</h2>
                <p>Join posted school sessions and check the schedule before attending.</p>
            </div>
            <div class="schedule-summary" aria-label="Schedule summary">
                <span><?= count($seminars) ?> total</span>
                <span><?= $currentCount ?> current</span>
                <span><?= $upcomingCount ?> upcoming</span>
            </div>
        </div>

        <?php if (empty($seminars)): ?>
            <section class="empty-state">
                <p class="eyebrow">Schedule</p>
                <h2>No current or upcoming seminars</h2>
                <p class="muted">Please check this page again for the next posted seminar schedule.</p>
            </section>
        <?php else: ?>
            <section class="seminar-list" aria-label="Seminar schedule">
                <?php foreach ($seminars as $seminar): ?>
                    <article class="seminar-card">
                        <div class="seminar-cover">
                            <time datetime="<?= htmlspecialchars($seminar['seminar_date']) ?>">
                                <?= htmlspecialchars(date('M j', strtotime($seminar['seminar_date']))) ?>
                            </time>
                        </div>
                        <div class="seminar-card-main">
                            <div class="card-tags">
                                <span><?= htmlspecialchars(ucfirst($seminar['public_status'])) ?></span>
                                <span><?= htmlspecialchars($formatTime($seminar['start_time'])) ?></span>
                            </div>
                            <h2><?= htmlspecialchars($seminar['title']) ?></h2>
                            <?php if (trim($seminar['description'] ?? '') !== ''): ?>
                                <p><?= nl2br(htmlspecialchars($seminar['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <dl class="seminar-meta">
                            <div>
                                <dt>Time</dt>
                                <dd>
                                    <?= htmlspecialchars($formatTime($seminar['start_time'])) ?>
                                    -
                                    <?= htmlspecialchars($formatTime($seminar['end_time'])) ?>
                                </dd>
                            </div>
                            <div>
                                <dt>Venue</dt>
                                <dd><?= htmlspecialchars($seminar['venue']) ?></dd>
                            </div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        </section>

        <section class="public-cta">
            <h2>Ready for the next session?</h2>
            <p>
                <?php if ($firstSeminar): ?>
                    The next posted seminar is <?= htmlspecialchars($firstSeminar['title']) ?> on <?= htmlspecialchars($formatDate($firstSeminar['seminar_date'])) ?>.
                <?php else: ?>
                    New seminar schedules will appear here once they are posted.
                <?php endif; ?>
            </p>
            <a href="#seminars">Check Schedule</a>
        </section>
    </main>

    <footer class="public-footer">
        <div>
            <strong>Attendance Bloom</strong>
            <p>Supporting families through organized parenting seminars and attendance coordination.</p>
        </div>
        <div>
            <strong>Company</strong>
            <a href="#approach">Our Approach</a>
            <a href="#seminars">Seminars</a>
        </div>
        <div>
            <strong>Access</strong>
            <a href="admin.php">Admin Login</a>
        </div>
    </footer>
</body>
</html>
