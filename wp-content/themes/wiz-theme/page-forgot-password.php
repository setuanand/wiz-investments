<?php
/*
Template Name: Forgot Password
*/
get_header();
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <h1 class="auth-title">Forgot Password?</h1>
      <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>
    </div>

    <div id="forgot-notice"></div>

    <form class="auth-form" id="forgot-form">
      <?php wp_nonce_field('wiz_forgot_nonce', 'wiz_nonce'); ?>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input class="form-input" type="email" id="email" name="email" required placeholder="your@email.com" autofocus>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Send Reset Link</button>
    </form>

    <script>
    document.getElementById('forgot-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var btn = form.querySelector('button[type="submit"]');
        var notice = document.getElementById('forgot-notice');
        btn.disabled = true;
        btn.textContent = 'Sending...';
        var data = new FormData(form);
        data.append('action', 'wiz_forgot_password');
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: data
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            notice.innerHTML = '<div class="form-notice ' + (res.success ? 'success' : 'error') + '">' + res.data + '</div>';
            if (res.success) form.style.display = 'none';
            btn.disabled = false;
            btn.textContent = 'Send Reset Link';
        })
        .catch(function() {
            notice.innerHTML = '<div class="form-notice error">Something went wrong. Please try again.</div>';
            btn.disabled = false;
            btn.textContent = 'Send Reset Link';
        });
    });
    </script>

    <div style="text-align:center; margin-top: var(--space-xl);">
      <a href="<?php echo esc_url(wiz_get_page_url_by_slug('login')); ?>" style="color: var(--text-secondary); font-size: 0.85rem;">&larr; Back to Login</a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
