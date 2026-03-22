<?php
/**
 * Settings — Index View
 *
 * Variables: $profile
 */

ob_start();

$name  = $profile['name'] ?? '';
$email = $profile['email'] ?? '';
$lang  = $profile['lang'] ?? $_SESSION['lang'] ?? 'en';
?>

<h2 class="mb-4"><i class="bi bi-gear me-2"></i>Settings</h2>

<div class="row g-4">
    <!-- Profile -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person me-2"></i>Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/settings">
                    <?= csrf_field() ?>
                    <input type="hidden" name="section" value="profile">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= e($name) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($email) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/settings">
                    <?= csrf_field() ?>
                    <input type="hidden" name="section" value="password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                        <input type="password" class="form-control" id="current_password"
                               name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" id="new_password"
                               name="new_password" required minlength="8">
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               name="confirm_password" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Preferences -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-sliders me-2"></i>Preferences</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/settings">
                    <?= csrf_field() ?>
                    <input type="hidden" name="section" value="preferences">

                    <div class="mb-3">
                        <label for="lang" class="form-label fw-semibold">Language</label>
                        <select class="form-select" id="lang" name="lang">
                            <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="es" <?= $lang === 'es' ? 'selected' : '' ?>>Spanish</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title   = 'Settings';
require VIEW_PATH . '/layouts/app.php';
?>
