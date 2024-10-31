<?php
/**
 * Plugin Name: Restrict Content Pro - MailPoet
 * Plugin URL: https://restrictcontentpro.com/downloads/wysija-add-on/
 * Description: Include a MailPoet signup option with your Restrict Content Pro registration form
 * Version: 1.0.5
 * Author: iThemes, LLC
 * Author URI: https://ithemes.com
 * Contributors: jthillithemes, layotte, ithemes
 * Text Domain: rcp_wysija
 * Domain Path: languages
 * iThemes Package: restrict-content-pro-wysija
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Load plugin textdomain
 *
 * @since  1.0
 * @return void
 */
function rcp_wysija_textdomain() {

	// Set filter for plugin's languages directory
	$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$lang_dir = apply_filters( 'rcp_wysija_languages_directory', $lang_dir );

	// Load the translations
	load_plugin_textdomain( 'rcp_wysija', false, $lang_dir );
}


/**
 * Add settings menu
 *
 * @since  1.0
 * @return void
 */
function rcp_Wysija_settings_menu() {
	// add settings page
	add_submenu_page( 'rcp-members', __( 'Restrict Content Pro MailPoet Settings', 'rcp_wysija' ), __( 'MailPoet', 'rcp_wysija' ), 'manage_options', 'rcp-Wysija', 'rcp_wysija_settings_page');
}


/**
 * Register plugin settings
 *
 * @since  1.0
 * @return void
 */
function rcp_wysija_register_settings() {
	// create whitelist of options
	register_setting( 'rcp_wysija_settings_group', 'rcp_wysija_settings' );
}


/**
 * Render the settings page
 *
 * @since  1.0
 * @return void
 */
function rcp_wysija_settings_page() {

	$options = get_option('rcp_wysija_settings');
	
	if( empty( $options['default_checkbox_state'] ) ) {
		$options['default_checkbox_state'] = 'checked';
	}

	?>
	<div class="wrap">
		<h2><?php _e('Restrict Content Pro MailPoet Settings', 'rcp_wysija'); ?></h2>
		<?php
		if ( ! isset( $_REQUEST['updated'] ) )
			$_REQUEST['updated'] = false;
		?>
		<?php if ( false !== $_REQUEST['updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'Options saved', 'rcp_wysija' ); ?></strong></p></div>
		<?php endif; ?>
		<form method="post" action="options.php" class="rcp_options_form">

			<?php settings_fields( 'rcp_wysija_settings_group' ); ?>
			<?php $lists = rcp_wysija_get_lists(); ?>

			<table class="form-table">
				<tr>
					<th>
						<label for="rcp_wysija_settings[wysija_list]"><?php _e( 'Newsletter List', 'rcp_wysija' ); ?></label>
					</th>
					<td>
						<select id="rcp_wysija_settings[wysija_list]" name="rcp_wysija_settings[wysija_list]">
							<?php
								if($lists) :
									foreach( $lists as $list_id => $list ) :
										echo '<option value="' . esc_attr( $list_id ) . '"' . selected( $options['wysija_list'], $list_id, false ) . '>' . esc_html( $list ) . '</option>';
									endforeach;
								else :
							?>
							<option value="no list"><?php _e( 'no lists', 'rcp_wysija' ); ?></option>
						<?php endif; ?>
						</select>
						<div class="description"><?php _e( 'Choose the list to subscribe users to', 'rcp_wysija' ); ?></div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="rcp_wysija_settings[signup_label]"><?php _e( 'Form Label', 'rcp_wysija' ); ?></label>
					</th>
					<td>
						<input class="regular-text" type="text" id="rcp_wysija_settings[signup_label]" name="rcp_wysija_settings[signup_label]" value="<?php if( ! empty( $options['signup_label'] ) ) { echo esc_html( $options['signup_label'] ); } ?>"/>
						<div class="description"><?php _e( 'Enter the label to be shown on the "Signup for Newsletter" checkbox', 'rcp_wysija' ); ?></div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="rcp_wysija_settings[default_checkbox_state]"><?php _e( 'Opt-In Checkbox Default State', 'rcp_wysija' ); ?></label>
					</th>
					<td>
						<select id="rcp_wysija_settings[default_checkbox_state]" name="rcp_wysija_settings[default_checkbox_state]">
							<option value="checked" <?php selected( empty( $options['default_checkbox_state'] ) || 'checked' == $options['default_checkbox_state'] ); ?>><?php _e( 'Checked', 'rcp_wysija' ); ?></option>
							<option value="unchecked" <?php selected( $options['default_checkbox_state'], 'unchecked' ); ?>><?php _e( 'Unchecked', 'rcp_wysija' ); ?></option>
						</select>
						<div class="description"><?php _e( 'Choose whether you want the opt-in on the registration form checked or unchecked by default.', 'rcp_wysija' ); ?></div>
					</td>
				</tr>
			</table>
			<!-- save the options -->
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Options', 'rcp_wysija' ); ?>" />
			</p>

		</form>
	</div><!--end .wrap-->
	<?php
}


/**
 * Retrieve an array of list IDs and names
 *
 * @since  1.0
 * @return array
 */
function rcp_wysija_get_lists() {

	$lists = array();

	if ( defined( 'MAILPOET_VERSION' ) && version_compare( MAILPOET_VERSION, '3', '>=' ) ) {

		// MailPoet 3.

		$mailpoet_lists = \MailPoet\API\API::MP( 'v1' )->getLists();

		if ( ! empty( $mailpoet_lists ) ) {
			foreach ( $mailpoet_lists as $list ) {
				$lists[ $list['id'] ] = $list['name'];
			}
		}

	} else {

		// MailPoet 2.

		if ( ! class_exists( 'WYSIJA' ) ) {
			return $lists;
		}

		$modelList   = WYSIJA::get( 'list', 'model' );
		$wysijaLists = $modelList->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );

		if ( ! empty( $wysijaLists ) ) {
			foreach ( $wysijaLists as $list ) {
				$lists[ $list['list_id'] ] = $list['name'];
			}
		}

	}

	return $lists;
}

/**
 * Adds an email to the MailPoet subscription list
 *
 * @param array $user_info Array of user information, including email and name.
 *
 * @since  1.0
 * @return bool Whether or not it was successful.
 */
function rcp_wysija_subscribe_email( $user_info ) {

	$options = get_option('rcp_wysija_settings');

	$list_id = isset( $options['wysija_list'] ) ? $options['wysija_list'] : false;

	if ( empty( $list_id ) ) {
		return false;
	}

	if ( defined( 'MAILPOET_VERSION' ) && version_compare( MAILPOET_VERSION, '3', '>=' ) ) {

		// MailPoet 3.

		$subscriber_exists = \MailPoet\Models\Subscriber::findOne( $user_info['email'] );

		try {
			if ( $subscriber_exists ) {
				$subscriber = \MailPoet\API\API::MP( 'v1' )->subscribeToList( $user_info['email'], $list_id );

				if ( function_exists( 'rcp_log' ) ) {
					rcp_log( sprintf( 'Added existing subscriber to MailPoet list #%d.', $list_id ) );
				}
			} else {
				$subscriber = \MailPoet\API\API::MP( 'v1' )->addSubscriber( $user_info, array( $list_id ) );

				if ( function_exists( 'rcp_log' ) ) {
					rcp_log( sprintf( 'Created new subscriber and added to MailPoet list #%d.', $list_id ) );
				}
			}
		} catch( Exception $exception ) {
			if ( function_exists( 'rcp_log' ) ) {
				rcp_log( sprintf( 'Failed adding user to MailPoet list. Error message: %s.', $exception->getMessage() ) );
			}

			return false;
		}

	} else {

		// MailPoet 2.

		if( ! class_exists( 'WYSIJA' ) ) {
			return false;
		}

		$data = array(
			'user'      => array(
				'email'     => $user_info['email'],
				'firstname' => $user_info['first_name'],
				'lastname'  => $user_info['last_name'],
			),
			'user_list' => array( 'list_ids' => array( $list_id ) )
		);

		$userHelper = WYSIJA::get( 'user', 'helper' );
		$userHelper->addSubscriber( $data );

	}

	return true;
}

/**
 * Display mailing list checkbox on registration form
 *
 * @since  1.0
 * @return void
 */
function rcp_wysija_fields() {

	$options = get_option('rcp_wysija_settings');
	$checked = empty( $options['default_checkbox_state'] ) || 'checked' == $options['default_checkbox_state'];

	ob_start();
		if( isset( $options['wysija_list'] ) ) { ?>
		<p>
			<input name="rcp_wysija_signup" id="rcp_wysija_signup" type="checkbox" <?php checked( $checked ); ?>/>
			<label for="rcp_wysija_signup"><?php echo isset( $options['signup_label'] ) ? $options['signup_label'] : __( 'Sign up for our mailing list', 'rcp_wysija' ); ?></label>
		</p>
		<?php
	}
	echo ob_get_clean();
}


/**
 * Checks whether a user should be signed up for the MailPoet list
 *
 * @param array                $posted              Array of form data.
 * @param int                  $user_id             ID of the user signing up.
 * @param float                $price               Price of the membership.
 * @param RCP_Customer|false   $customer            Customer object.
 * @param int                  $membership_id       ID of the membership for this registration.
 * @param RCP_Membership|false $previous_membership Previous membership, if this is an upgrade/downgrade.
 * @param string               $registration_type   Type of registration (new, upgrade, downgrade, renewal).
 *
 * @since  1.0
 * @return void
 */
function rcp_check_for_email_signup( $posted = array(), $user_id = 0, $price = 0.00, $payment_id = 0, $customer = false, $membership_id = 0, $previous_membership = false, $registration_type = 'new' ) {
	if ( function_exists( 'rcp_add_membership_meta' ) && ! empty( $membership_id ) ) {
		if ( isset( $posted['rcp_wysija_signup'] ) ) {
			rcp_add_membership_meta( $membership_id, 'mailpoet_pending_signup', true );
		} else {
			rcp_delete_membership_meta( $membership_id, 'mailpoet_pending_signup' );
		}
	} else {
		if ( isset( $posted['rcp_wysija_signup'] ) ) {
			update_user_meta( $user_id, 'rcp_pending_mailpoet_signup', true );
		} else {
			delete_user_meta( $user_id, 'rcp_pending_mailpoet_signup' );
		}
	}
}

/**
 * When a membership is activated, add the user to the MailPoet list if the flag has been set.
 *
 * @param RCP_Member           $member     Member object.
 * @param RCP_Customer|false   $customer   Customer object.
 * @param RCP_Membership|false $membership Membership object.
 *
 * @since 1.0.4
 * @return void
 */
function rcp_mailpoet_maybe_add_to_list( $member, $customer = false, $membership = false ) {

	if ( ! is_a( $membership, 'RCP_Membership' ) ) {
		return;
	}

	if ( ! rcp_get_membership_meta( $membership->get_id(), 'mailpoet_pending_signup', true ) ) {
		return;
	}

	$user = get_userdata( $membership->get_customer()->get_user_id() );

	if ( empty( $user ) ) {
		return;
	}

	$user_data = array(
		'email'      => $user->user_email,
		'first_name' => $user->first_name,
		'last_name'  => $user->last_name
	);

	$subscribed = rcp_wysija_subscribe_email( $user_data );

	if ( $subscribed ) {
		update_user_meta( $user->ID, 'rcp_subscribed_to_mailpoet', 'yes' );
		delete_user_meta( $user->ID, 'rcp_pending_mailpoet_signup' );
		rcp_delete_membership_meta( $membership->get_id(), 'mailpoet_pending_signup' );
	}

}

/**
 * Add member to the MailPoet list when their account is activated
 *
 * @deprecated 1.0.4 In favour of `rcp_mailpoet_maybe_add_to_list()`
 * @see        rcp_mailpoet_maybe_add_to_list()
 *
 * @param string     $status     New status.
 * @param int        $user_id    ID of the user.
 * @param string     $old_status Previous status.
 * @param RCP_Member $member     Member object.
 *
 * @since  1.0.1
 * @return void
 */
function rcp_mailpoet_add_to_list( $status, $user_id, $old_status, $member ) {

	if ( ! in_array( $status, array( 'active', 'free' ) ) ) {
		return;
	}

	if ( ! get_user_meta( $user_id, 'rcp_pending_mailpoet_signup', true ) ) {
		return;
	}

	$user_data = array(
		'email'      => $member->user_email,
		'first_name' => $member->first_name,
		'last_name'  => $member->last_name
	);

	rcp_wysija_subscribe_email( $user_data );
	delete_user_meta( $user_id, 'rcp_pending_mailpoet_signup' );

}

/**
 * Handles the plugin's loading process.
 *
 * @since 1.0.2
 */
function rcp_mailpoet_loader() {

	if ( ! defined( 'MAILPOET_VERSION' ) && ! class_exists( 'WYSIJA' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>' . __( 'Restrict Content Pro - MailPoet requires MailPoet 2.x or higher. Please install and activate the latest version to continue.', 'rcp_wysija' ) . '</p></div>';
		});
		return;
	}

	if( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
		add_action( 'admin_notices' , function() {
			echo '<div class="error"><p>' . __( 'Restrict Content Pro - MailPoet requires Restrict Content Pro. Please install and activate the latest version to continue.', 'rcp_wysija' ) . '</p></div>';
		});
		return;
	}

	add_action( 'init', 'rcp_wysija_textdomain');

	if ( function_exists( 'rcp_get_membership' ) ) {
		add_action( 'rcp_successful_registration', 'rcp_mailpoet_maybe_add_to_list', 10, 3 );
	} else {
		add_action( 'rcp_set_status', 'rcp_mailpoet_add_to_list', 10, 4 );
	}

	add_action( 'admin_menu', 'rcp_Wysija_settings_menu', 100 );

	add_action( 'admin_init', 'rcp_Wysija_register_settings', 100 );

	add_action( 'rcp_before_registration_submit_field', 'rcp_wysija_fields', 100 );

	add_action( 'rcp_form_processing', 'rcp_check_for_email_signup', 10, 8 );
}
add_action( 'plugins_loaded', 'rcp_mailpoet_loader' );

if ( ! function_exists( 'ithemes_restrict_content_pro_wysija_updater_register' ) ) {
	function ithemes_restrict_content_pro_wysija_updater_register( $updater ) {
		$updater->register( 'REPO', __FILE__ );
	}
	add_action( 'ithemes_updater_register', 'ithemes_restrict_content_pro_wysija_updater_register' );

	require( __DIR__ . '/lib/updater/load.php' );
}