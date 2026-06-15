<?php
session_start();

$active_tab      = $_SESSION['active_tab']      ?? 'signup';
$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message']   ?? '';
$old             = $_SESSION['form_data']        ?? [];

unset(
    $_SESSION['success_message'],
    $_SESSION['error_message'],
    $_SESSION['active_tab'],
    $_SESSION['form_data']
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Authentication</title>
    <link rel="icon" type="image/png" href="<?php echo '../assets/logo-s.svg'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="auth-container">

        <div class="auth-tabs">
            <button class="tab-btn <?php echo $active_tab === 'signup' ? 'active' : ''; ?>" onclick="switchTab('signup')">Sign up</button>
            <button class="tab-btn <?php echo $active_tab === 'login'  ? 'active' : ''; ?>" onclick="switchTab('login')">Log in</button>
        </div>

        <div class="brand-logo">
            <img src="../assets/logo.svg" alt="survAIval Logo"
                style="height: 60px; width: auto; display: block;" />
        </div>

        <form id="signup-form"
              method="POST"
              action="auth_process.php"
              class="auth-form <?php echo $active_tab === 'signup' ? 'active' : ''; ?>">

            <input type="hidden" name="action" value="signup">
            <h2 class="form-title">Create Your Account</h2>

            <div class="form-grid">

                <div>
                    <input type="text" name="fname" class="input-field"
                        placeholder="First Name *"
                        value="<?php echo $old['fname'] ?? ''; ?>" required>
                </div>
                <div>
                    <input type="text" name="lname" class="input-field"
                        placeholder="Last Name *"
                        value="<?php echo $old['lname'] ?? ''; ?>" required>
                </div>

                <div class="full-width">
                    <input type="text" name="mname" class="input-field"
                        placeholder="Middle Name (Optional)"
                        value="<?php echo $old['mname'] ?? ''; ?>">
                </div>

                <div>
                    <select name="gender" class="input-field select-field" required>
                        <option value="" disabled <?php echo empty($old['gender']) ? 'selected' : ''; ?>>Gender *</option>
                        <option value="Male"   <?php echo ($old['gender'] ?? '') === 'Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($old['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div>
                    <input type="tel" name="cellphone_no" class="input-field"
                        placeholder="Cellphone Number *"
                        value="<?php echo $old['cellphone_no'] ?? ''; ?>" required>
                </div>

                <div>
                    <input type="text" name="barangay" class="input-field"
                        placeholder="Barangay *"
                        value="<?php echo $old['barangay'] ?? ''; ?>" required>
                </div>
                <div>
                    <input type="text" name="city" class="input-field"
                        placeholder="City / Town *"
                        value="<?php echo $old['city'] ?? ''; ?>" required>
                </div>

                <div class="full-width spacer">
                    <input type="email" name="email" class="input-field"
                        placeholder="Email *"
                        value="<?php echo $old['email'] ?? ''; ?>" required>
                </div>

                <div>
                    <input type="password" name="password" class="input-field"
                        placeholder="Password *" required>
                </div>
                <div>
                    <input type="password" name="confirm_password" class="input-field"
                        placeholder="Confirm Password *" required>
                </div>

            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <form id="login-form"
              method="POST"
              action="auth_process.php"
              class="auth-form <?php echo $active_tab === 'login' ? 'active' : ''; ?>">

            <input type="hidden" name="action" value="login">
            <h2 class="form-title">Log in Your Account</h2>

            <div class="form-grid">

                <div class="full-width">
                    <input type="email" name="email" class="input-field"
                        placeholder="Email *"
                        value="<?php echo $active_tab === 'login' ? ($old['email'] ?? '') : ''; ?>"
                        required>
                </div>

                <div class="full-width spacer">
                    <input type="password" name="password" class="input-field"
                        placeholder="Password *" required>
                </div>

            </div>

            <label class="remember-me">
                <input type="checkbox" name="remember"
                    <?php echo isset($old['remember']) ? 'checked' : ''; ?>>
                Remember me
            </label>

            <button type="submit" class="submit-btn">Log In</button>
        </form>

    </div>

    <script src="script.js"></script>
</body>

</html>
