<?php
/**
 * @package MyChatbotAgency chatbot
 * @version 1.1
 */
/*
Plugin Name: My Chatbot Agency Chat
Plugin URI: https://www.faqulty.co/
Description: A chat widget and chatbot to your website to reply in the best way to your customers, saving time and offering a 24/7 support.
Version: 1.1
Author: My Chatbot Agency
Author URI: https://www.faqulty.co/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mca
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action('admin_menu', 'MCA_create_menu');

/**
 * Add top-level menu page
 * 
 * @since 0.5
 * 
*/
function MCA_create_menu() {
  add_menu_page(__('MCA Settings', 'mca'), __('MCA Settings', 'mca'), 'administrator', __FILE__, 'MCA_plugin_settings_page' ,  plugins_url("assets/images/favicon.png", __FILE__));
  add_action('admin_init', 'MCA_plugin_register_settings' );
  add_action('admin_init', 'MCA_plugin_register_onboarding');
}

/**
 * Retrieves options
 * 
 * @since 0.5
 * 
 * 
*/
function MCA_plugin_register_onboarding() {
  $onboarding = get_option('mca_onboarding');
  $room_ID = get_option('room_ID');
  $chat_ID = get_option('chat_ID');

  if (empty($room_ID) && (empty($onboarding) || !$onboarding)) {
    update_option("mca_onboarding", true);
    wp_redirect(admin_url('admin.php?page='.plugin_basename(__FILE__)));
  }
}

/**
 * Register MCA settings
 * 
 * @since 0.5
 * 
*/
function MCA_plugin_register_settings() {
  register_setting( 'mca-plugin-settings-group', 'room_ID' );
  register_setting( 'mca-plugin-settings-group', 'chat_ID' );
  add_option('mca_onboarding', false);
}

/**
 * Create the administration plugin interface, 2 possibilities:
 *  - Freshly activated => Bouton to link your wordpress site with My Chatbot Agency platform account.
 *  - Once linked with MCA platform, one button to access inbox and another to the configuration chat widget page. 
 * 
 * @since 0.5
 * 
*/
function MCA_plugin_settings_page() {
  if (isset($_GET["roomId"]) && !empty($_GET["roomId"])) {
    update_option("room_ID", $_GET["roomId"]);
  }
  
  if (isset($_GET["configId"]) && !empty($_GET["configId"])) {
    update_option("chat_ID", $_GET["configId"]);
  }

  if (isset($_GET["mca_verify"]) && !empty($_GET["mca_verify"])) {
    update_option("website_verify", $_GET["mca_verify"]);
  }

  $chat_ID = get_option('chat_ID');
  $room_ID = get_option('room_ID');
  $is_mca_working = isset($room_ID) && !empty($room_ID) && isset($chat_ID) && !empty($chat_ID) ;
  $http_callback = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  $add_to_mca_link = "https://app.faqulty.co/signup?payload=$http_callback" . "&CMS=WP";
?>

<link rel="stylesheet" href="<?php echo plugins_url("assets/css/style.css", __FILE__ );?>">
  <?php
  if ($is_mca_working) {
  ?>

  <div class="wrap mca-wrap">
    <div class="mca-modal">
      <h2 class="mca-title"><?php _e('Connected with My Chatbot Agency.', 'mca'); ?></h2>
      <img src="<?php echo plugins_url("assets/images/logo.png", __FILE__);?>" alt="My Chatbot Agency logo"> 
      <p class="mca-subtitle"><?php _e('You can now use the chat and the automation tool! ', 'mca'); ?><a class="mca-subtitle" href="https://www.faqulty.co/"><?php _e('My Chatbot Agency.', 'mca'); ?></a></p>
      <a class="mca-button " href="https://app.faqulty.co/settings"><?php _e('Configuration', 'mca'); ?></a>

      <a class="mca-button " href="https://app.faqulty.co/chat"><?php _e('Messaging', 'mca'); ?></a>
    </div>

    <p class="mca-notice"><?php _e('You like My Chatbot Agency <b style="color:red">♥</b> ? Leave us a review at <a target="_blank" href="https://wordpress.org/support/plugin/my-chatbot-agency-chat/reviews/">Wordpress Plugin Directory</a>', 'mca'); ?></p>
  </div>
 
  <?php
  } else {
  ?>
  <div class="wrap mca-wrap">
    <div class="mca-modal">
      <h2 class="mca-title"><?php _e('Connect with My Chatbot Agency platform.', 'mca'); ?></h2>
      <img src="<?php echo plugins_url("assets/images/logo.png", __FILE__);?>" alt="My Chatbot Agency logo">
      <div class="mca-subtitle mca-text-center">
            <p><?php _e("My Chatbot Agency chat allow you to live speak with your Wordpress website users and to automate some questions/answers thanks to our FAQBOT tool. Click the button below to create your account, customize your chat widget and start saving time with automation!", 'mca'); ?></p>
            <p><?php _e("If you click the button, a new page will pop up where you will be able to create an account. This account is not related to your wordpress account.", 'mca'); ?> </p>
      </div>
      <a class="mca-button mca" href="<?php echo $add_to_mca_link; ?>"><?php _e('Connect with My Chatbot Agency platform', 'mca'); ?></a>
    </div>
  </div>
  <?php
  }
}

add_action('wp_head', 'MCA_hook_head', 1);

/**
 * Create the script to insert in the website for getting access to chat widget.
 * Chat_ID & room_ID are specific for each website. 
 * 
 * @since 0.5
 * 
*/
function MCA_hook_head() {
  $chat_ID = get_option('chat_ID');
  $room_ID = get_option('room_ID');
  $locale = str_replace("_", "-", strtolower(get_locale()));

  if (!in_array($locale, array("pt-br", "pt-pr"))) {
    $locale = substr($locale, 0, 2);
  }

  if (!isset($room_ID) || empty($room_ID)) {
    return;
  }

  if (!isset($chat_ID) || empty($chat_ID)) {
    return;
  }

  $output = "<script>
    var talooWidget = {
      chatId: '$room_ID',
      id: '$chat_ID'
    };
  </script>
  <script id='talooWidget' src='https://app.faqulty.co/static/js/widget.js'>
  </script>";
  
  echo $output;
}
