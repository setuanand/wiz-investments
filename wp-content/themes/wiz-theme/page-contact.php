<?php
/*
Template Name: Contact
*/

$contact_notice = '';
if (isset($_POST['wiz_contact_submit'])) {
    $contact_notice = function_exists('wiz_process_contact_submission') ? wiz_process_contact_submission() : '';
    if (strpos($contact_notice, 'success') !== false) {
        wp_redirect(home_url('/contact/?sent=1'));
        exit;
    }
}

get_header();

if (isset($_GET['sent'])) {
    $contact_notice = '<div class="form-notice success">Your message has been sent successfully. We will be in touch soon!</div>';
}
?>

<div class="contact-page">
  <div class="container">

    <!-- Page Header -->
    <div class="contact-hero">
      <span class="section-label">Get In Touch</span>
      <h1 class="contact-title">How can we help?</h1>
      <p class="contact-subtitle">Have a question about our platform, analytics, or membership? We'd love to hear from you. Send us a message and we'll get back to you promptly.</p>
    </div>

    <div class="contact-layout">

      <!-- Left: Form -->
      <div class="contact-form-wrap">
        <div class="dashboard-card">
          <h2 style="font-size: 1.2rem; margin-bottom: var(--space-lg); color: var(--text-primary);">Send us a message</h2>

          <?php if ($contact_notice) echo $contact_notice; ?>

          <form class="contact-form-inner" id="wiz-contact-form">
            <?php wp_nonce_field('wiz_contact_action', 'wiz_contact_nonce'); ?>

            <div class="form-group">
              <label class="form-label" for="name">Your Name</label>
              <input class="form-control" type="text" id="name" name="name" placeholder="John Smith" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="email">Email Address</label>
              <input class="form-control" type="email" id="email" name="email" placeholder="john@example.com" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="subject">Subject</label>
              <select class="form-control" id="subject" name="subject">
                <option value="General Enquiry">General Enquiry</option>
                <option value="Platform Support">Platform Support</option>
                <option value="Analytics Dashboard">Analytics Dashboard</option>
                <option value="Membership & Pricing">Membership &amp; Pricing</option>
                <option value="Partnership">Partnership</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="message">Message</label>
              <textarea class="form-control" id="message" name="message" rows="6" placeholder="Tell us how we can help you..." required></textarea>
            </div>

            <div id="contact-notice"></div>

            <button type="submit" name="wiz_contact_submit" class="btn btn-primary" style="width:100%; padding: var(--space-md); font-size: 1rem;">
              Send Message
            </button>
          </form>
        </div>
      </div>

      <!-- Right: Info + Cards -->
      <div class="contact-info-wrap">

        <div class="contact-info-card">
          <div class="contact-info-icon">✉️</div>
          <div>
            <h4>Email Us</h4>
            <p>For all enquiries, drop us an email and we'll respond within 24 hours.</p>
            <a href="mailto:wizinvesta@gmail.com" class="contact-info-link">wizinvesta@gmail.com</a>
          </div>
        </div>

        <div class="contact-info-card">
          <div class="contact-info-icon">⏱️</div>
          <div>
            <h4>Response Time</h4>
            <p>We aim to respond to all enquiries within 24 hours on business days.</p>
          </div>
        </div>

        <div class="contact-info-card">
          <div class="contact-info-icon">🔒</div>
          <div>
            <h4>Your Privacy</h4>
            <p>Your information is never shared with third parties. Read our <a href="<?php echo esc_url(wiz_get_page_url_by_slug('privacy-policy')); ?>" style="color: var(--primary);">Privacy Policy</a>.</p>
          </div>
        </div>

        <div class="contact-info-card">
          <div class="contact-info-icon">📊</div>
          <div>
            <h4>Platform Support</h4>
            <p>For technical issues with the analytics dashboard or your account, please include your registered email in the message.</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('wiz-contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var btn = form.querySelector('button[type="submit"]');
    var notice = document.getElementById('contact-notice');
    btn.disabled = true;
    btn.textContent = 'Sending...';
    var data = new FormData(form);
    data.append('action', 'wiz_contact');
    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
        method: 'POST',
        body: data
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            notice.innerHTML = '<div class="form-notice success">' + res.data + '</div>';
            form.reset();
        } else {
            notice.innerHTML = '<div class="form-notice error">' + res.data + '</div>';
        }
        btn.disabled = false;
        btn.textContent = 'Send Message';
    })
    .catch(function() {
        notice.innerHTML = '<div class="form-notice error">Something went wrong. Please try again.</div>';
        btn.disabled = false;
        btn.textContent = 'Send Message';
    });
});
</script>

<?php get_footer(); ?>
