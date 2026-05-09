<?php
/*
Template Name: Contact
*/
get_header();
?>

<section class="page-content">
  <h1>Contact Us</h1>
  <p>Get in touch with our team for personalized investment advice and inquiries.</p>
  <div id="contact-notice"></div>
  <form class="contact-form" id="wiz-contact-form">
    <?php wp_nonce_field('wiz_contact_action', 'wiz_contact_nonce'); ?>
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="message">Message:</label>
    <textarea id="message" name="message" rows="5" required></textarea>

    <button type="submit" name="wiz_contact_submit">Send Message</button>
  </form>
  <div class="contact-info">
    <h2>Contact Information</h2>
    <p>Email: info@wizinvestments.com</p>
    <p>Phone: (555) 123-4567</p>
    <p>Address: 123 Finance Street, Investment City, IC 12345</p>
  </div>
</section>

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