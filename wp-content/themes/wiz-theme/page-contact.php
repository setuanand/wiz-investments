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

<section class="page-content">
  <h1>Contact Us</h1>
  <p>Get in touch with our team for personalized investment advice and inquiries.</p>
  <?php if ($contact_notice): ?>
    <?php echo $contact_notice; ?>
  <?php endif; ?>
  <form class="contact-form" action="<?php echo esc_url( get_permalink() ); ?>" method="post">
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

<?php get_footer(); ?>