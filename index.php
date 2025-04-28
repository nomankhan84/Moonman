<?php
session_start();
$login_message = '';
$redirect_url = '';

if (isset($_SESSION['login_message'])) {
    $login_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if (isset($_SESSION['redirect_url'])) {
    $redirect_url = $_SESSION['redirect_url'];
    unset($_SESSION['redirect_url']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moon Man Tours</title>
    
    <link rel="shortcut icon" href="images2/logo.jpg">

    <link rel="stylesheet" href="css2/style2.css">
</head>
<body>
    <div class="login-wrap">
        <div class="login-html">
            <div class="head-logo">
                <img src="images2/logo.jpg" alt="logo">
            </div>
            <input id="tab-1" type="radio" name="tab" class="sign-in" checked><label for="tab-1" class="tab">Sign In</label>
            <input id="tab-2" type="radio" name="tab" class="sign-up"><label for="tab-2" class="tab">Sign Up</label>
            <div class="login-form">
            <?php if (!empty($login_message)): ?>
    <div id="login-message" class="alert alert-success">
        <?php echo $login_message; ?>
    </div>
<?php endif; ?>
                <form method="POST" action="login.php">
                <div class="sign-in-htm">
                    <div class="group">
                        <label for="email" class="label">Email Address</label>
                            <input id="email" name="email" type="email" class="input" required>
                    </div>
                    <div class="group">
                        <label for="password" class="label">Password</label>
                            <input id="password" name="password" type="password" class="input" required>
                    </div>
                    <div class="group">
                        <input id="check" type="checkbox" class="check" checked>
                        <label for="check"><span class="icon"></span> Keep me Signed in</label>
                    </div>
                    <div class="group">
                        <input type="submit" class="button" value="Sign In">
                    </div>
                    <div class="hr"></div>
                    <div class="foot-lnk">
                        <a href="html/index.html">Forgot Password?</a>
                    </div>
                </div>
            </form>
                <form method="POST" action="register.php">
                    <div class="sign-up-htm">
                        <div class="group">
                            <label for="username" class="label">Username</label>
                            <input id="username" name="username" type="text" class="input" required>
                        </div>
                        <div class="group">
                            <label for="password" class="label">Password</label>
                            <input id="password" name="password" type="password" class="input" required>
                        </div>
                        <div class="group">
                            <label for="referral_code" class="label">Referral Code</label>
                            <input id="referral_code" name="referral_code" type="text" class="input" required>
                        </div>
                        <div class="group">
                            <label for="email" class="label">Email Address</label>
                            <input id="email" name="email" type="email" class="input" required>
                        </div>
                        <div class="group">
                            <input type="submit" class="button" value="Sign Up">
                        </div>
                        <div class="hr"></div>
                        <div class="foot-lnk">
                            <label for="tab-1">Already a Member?</label>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
    <script>
        // Wait for DOM to be ready
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const refCode = urlParams.get("ref");
    
            if (refCode) {
                const refInput = document.getElementById("referral_code");
                refInput.value = refCode;
                refInput.readOnly = true; // Make it non-editable
            }
        });
    </script>
    <?php if (!empty($redirect_url)): ?>
<script>
    setTimeout(function() {
        window.location.href = '<?php echo $redirect_url; ?>';
    }, 2000); // 2000 milliseconds = 2 seconds
</script>
<?php endif; ?>

    
</body>
</html>