<?php

/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (! defined('ABSPATH'))
    exit();

final class GADWP_Settings
{

    private static function update_options($who)
    {
        $gadwp = GADWP();
        $network_settings = false;
        $options = $gadwp->config->options; // Get current options
        if (isset($_POST['options']['ga_dash_hidden']) && isset($_POST['options']) && (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) && $who != 'Reset') {
            $new_options = $_POST['options'];
            if ($who == 'tracking') {
                $options['ga_dash_anonim'] = 0;
                $options['ga_event_tracking'] = 0;
                $options['ga_enhanced_links'] = 0;
                $options['ga_dash_remarketing'] = 0;
                $options['ga_dash_adsense'] = 0;
                $options['ga_event_bouncerate'] = 0;
                $options['ga_crossdomain_tracking'] = 0;
                $options['ga_aff_tracking'] = 0;
                $options['ga_hash_tracking'] = 0;
                if (isset($_POST['options']['ga_tracking_code'])) {
                    $new_options['ga_tracking_code'] = trim($new_options['ga_tracking_code'], "\t");
                }
                if (empty($new_options['ga_track_exclude'])) {
                    $new_options['ga_track_exclude'] = array();
                }
            } else 
                if ($who == 'backend') {
                    $options['switch_profile'] = 0;
                    $options['item_reports'] = 0;
                    $options['dashboard_widget'] = 0;
                    if (empty($new_options['ga_dash_access_back'])) {
                        $new_options['ga_dash_access_back'][] = 'administrator';
                    }
                } else 
                    if ($who == 'frontend') {
                        $options['ga_dash_frontend_stats'] = 0;
                        $options['ga_dash_frontend_keywords'] = 0;
                        if (empty($new_options['ga_dash_access_front'])) {
                            $new_options['ga_dash_access_front'][] = 'administrator';
                        }
                    } else 
                        if ($who == 'general') {
                            $options['ga_dash_userapi'] = 0;
                            if (! is_multisite()) {
                                $options['automatic_updates_minorversion'] = 0;
                            }
                        } else 
                            if ($who == 'network') {
                                $options['ga_dash_userapi'] = 0;
                                $options['ga_dash_network'] = 0;
                                $options['ga_dash_excludesa'] = 0;
                                $options['automatic_updates_minorversion'] = 0;
                                $network_settings = true;
                            }
            $options = array_merge($options, $new_options);
            $gadwp->config->options = $options;
            $gadwp->config->set_plugin_options($network_settings);
        }
        return $options;
    }

    private static function navigation_tabs($tabs)
    {
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            echo "<a class='nav-tab' id='tab-$tab' href='#top#gadwp-$tab'>$name</a>";
        }
        echo '</h2>';
    }

    public static function frontend_settings()
    {
        $gadwp = GADWP();
        if (! current_user_can('manage_options')) {
            return;
        }
        $options = self::update_options('frontend');
        if (isset($_POST['options']['ga_dash_hidden'])) {
            $message = "<div class='updated'><p>" . __("Settings saved.", 'ga-dash') . "</p></div>";
            if (! (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form'))) {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token']) {
            $message = sprintf('<div class="error"><p>%s</p></div>', sprintf(__('Something went wrong, check %1$s or %2$s.', 'ga-dash'), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_errors_debugging', false), __('Errors & Debug', 'ga-dash')), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_settings', false), __('authorize the plugin', 'ga-dash'))));
        }
        ?>
<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Frontend Settings", 'ga-dash' ) . "</h2>"; ?><hr>
    </div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="settings-wrapper">
                    <div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="options">
                            <tr>
                                <td colspan="2"><?php echo "<h2>" . __( "Permissions", 'ga-dash' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td class="roles title"><label for="ga_dash_access_front"><?php _e("Show stats to:", 'ga-dash' ); ?></label></td>
                                <td class="roles">
                               		<?php
        if (! isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        $i = 0;
        ?>                      
                                <table>
                                        <tr>
		<?php
        foreach ($wp_roles->role_names as $role => $name) {
            if ($role != 'subscriber') {
                $i ++;
                ?>
		        <td><label> <input type="checkbox" name="options[ga_dash_access_front][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_dash_access_front']) || $role=='administrator') echo 'checked="checked"'; if ($role=='administrator') echo 'disabled="disabled"';?> /><?php echo $name; ?>
		          </label></td>
		                                    <?php
            }
            if ($i % 4 == 0) {
                ?>
                                        </tr>
                                        <tr>
                <?php
            }
        }
        ?>
                                    
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="options[ga_dash_frontend_stats]" value="1" class="onoffswitch-checkbox" id="ga_dash_frontend_stats" <?php checked( $options['ga_dash_frontend_stats'], 1 ); ?>> <label class="onoffswitch-label" for="ga_dash_frontend_stats">
                                            <div class="onoffswitch-inner"></div>
                                            <div class="onoffswitch-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php echo " ".__("show page sessions and users in frontend (after each article)", 'ga-dash' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="options[ga_dash_frontend_keywords]" value="1" class="onoffswitch-checkbox" id="ga_dash_frontend_keywords" <?php checked( $options['ga_dash_frontend_keywords'], 1 ); ?>> <label class="onoffswitch-label" for="ga_dash_frontend_keywords">
                                            <div class="onoffswitch-inner"></div>
                                            <div class="onoffswitch-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php echo " ".__("show page searches (after each article)", 'ga-dash' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'ga-dash' ) ?>" /></td>
                            </tr>
                        </table>
                        <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security');?>




</form>
<?php
        self::output_sidebar();
    }

    public static function backend_settings()
    {
        $gadwp = GADWP();
        if (! current_user_can('manage_options')) {
            return;
        }
        $options = self::update_options('backend');
        if (isset($_POST['options']['ga_dash_hidden'])) {
            $message = "<div class='updated'><p>" . __("Settings saved.", 'ga-dash') . "</p></div>";
            if (! (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form'))) {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token']) {
            $message = sprintf('<div class="error"><p>%s</p></div>', sprintf(__('Something went wrong, check %1$s or %2$s.', 'ga-dash'), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_errors_debugging', false), __('Errors & Debug', 'ga-dash')), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_settings', false), __('authorize the plugin', 'ga-dash'))));
        }
        ?>
<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Backend Settings", 'ga-dash' ) . "</h2>"; ?><hr>
    </div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="settings-wrapper">
                    <div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="options">
                            <tr>
                                <td colspan="2"><?php echo "<h2>" . __( "Permissions", 'ga-dash' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td class="roles title"><label for="ga_dash_access_back"><?php _e("Show stats to:", 'ga-dash' ); ?></label></td>
                                <td class="roles">
									<?php
        if (! isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        $i = 0;
        ?>
									<table>
                                        <tr>
        <?php
        
        foreach ($wp_roles->role_names as $role => $name) {
            if ($role != 'subscriber') {
                $i ++;
                ?>
                                        	<td><label> <input type="checkbox" name="options[ga_dash_access_back][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_dash_access_back']) || $role=='administrator') echo 'checked="checked"'; if ($role=='administrator') echo 'disabled="disabled"';?> />
                                            	<?php echo $name; ?>
                            				</label></td>
                <?php
            }
            if ($i % 4 == 0) {
                ?>
                                        </tr>
                                        <tr>
                                    		<?php
            }
        }
        ?>
                                    
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="options[switch_profile]" value="1" class="onoffswitch-checkbox" id="switch_profile" <?php checked( $options['switch_profile'], 1 ); ?>> <label class="onoffswitch-label" for="switch_profile">
                                            <div class="onoffswitch-inner"></div>
                                            <div class="onoffswitch-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php _e ( "enable Switch View functionality", 'ga-dash' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="options[item_reports]" value="1" class="onoffswitch-checkbox" id="item_reports" <?php checked( $options['item_reports'], 1 ); ?>> <label class="onoffswitch-label" for="item_reports">
                                            <div class="onoffswitch-inner"></div>
                                            <div class="onoffswitch-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php _e ( "enable reports on Posts List and Pages List", 'ga-dash' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="options[dashboard_widget]" value="1" class="onoffswitch-checkbox" id="dashboard_widget" <?php checked( $options['dashboard_widget'], 1 ); ?>> <label class="onoffswitch-label" for="dashboard_widget">
                                            <div class="onoffswitch-inner"></div>
                                            <div class="onoffswitch-switch"></div>
                                        </label>
                                    </div>
                                    <div class="switch-desc"><?php _e ( "enable the main Dashboard Widget", 'ga-dash' );?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "Real-Time Settings", 'ga-dash' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title"> <?php _e("Maximum number of pages to display on real-time tab:", 'ga-dash'); ?>
    								<input type="number" name="options[ga_realtime_pages]" id="ga_realtime_pages" value="<?php echo (int)$options['ga_realtime_pages']; ?>" size="3">
								</td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "Location Settings", 'ga-dash' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="title">
									<?php echo __("Target Geo Map to country:", 'ga-dash'); ?>
									<input type="text" style="text-align: center;" name="options[ga_target_geomap]" value="<?php echo esc_attr($options['ga_target_geomap']); ?>" size="3">
								</td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'ga-dash' ) ?>" /></td>
                            </tr>
                        </table>
                        <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>

</form>
<?php
        self::output_sidebar();
    }

    public static function tracking_settings()
    {
        $gadwp = GADWP();
        
        if (! current_user_can('manage_options')) {
            return;
        }
        $options = self::update_options('tracking');
        if (isset($_POST['options']['ga_dash_hidden'])) {
            $message = "<div class='updated'><p>" . __("Settings saved.", 'ga-dash') . "</p></div>";
            if (! (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form'))) {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (! $gadwp->config->options['ga_dash_tableid_jail']) {
            $message = sprintf('<div class="error"><p>%s</p></div>', sprintf(__('Something went wrong, check %1$s or %2$s.', 'ga-dash'), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_errors_debugging', false), __('Errors & Debug', 'ga-dash')), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_settings', false), __('authorize the plugin', 'ga-dash'))));
        }
        if (! $options['ga_dash_tracking']) {
            $message = "<div class='error'><p>" . __("The tracking component is disabled. You should set <strong>Tracking Options</strong> to <strong>Enabled</strong>", 'ga-dash') . ".</p></div>";
        }
        ?>
<form name="ga_dash_form" method="post" action="<?php  esc_url($_SERVER['REQUEST_URI']); ?>">
    <div class="wrap">
			<?php echo "<h2>" . __( "Google Analytics Tracking Code", 'ga-dash' ) . "</h2>"; ?>
	</div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="settings-wrapper">
                    <div class="inside">
                    <?php
        $tabs = array(
            'basic' => __("Basic Settings", 'ga-dash'),
            'events' => __("Events Tracking", 'ga-dash'),
            'custom' => __("Custom Definitions", 'ga-dash'),
            'exclude' => __("Exclude Tracking", 'ga-dash'),
            'advanced' => __("Advanced Settings", 'ga-dash')
        );
        self::navigation_tabs($tabs);
        ?>
					<?php if (isset($message)) echo $message; ?>
					    <div id="gadwp-basic">
                            <table class="options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Tracking Settings", 'ga-dash' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_dash_tracking"><?php _e("Tracking Options:", 'ga-dash' ); ?></label></td>
                                    <td><select id="ga_dash_tracking" name="options[ga_dash_tracking]" onchange="this.form.submit()">
                                            <option value="0" <?php selected( $options['ga_dash_tracking'], 0 ); ?>><?php _e("Disabled", 'ga-dash');?></option>
                                            <option value="1" <?php selected( $options['ga_dash_tracking'], 1 ); ?>><?php _e("Enabled", 'ga-dash');?></option>
                                    </select></td>
                                </tr>
								<?php if ($options['ga_dash_tracking']) {?>
								<tr>
                                    <td class="title"></td>
                                    <td>
                                        <?php $profile_info = GADWP_Tools::get_selected_profile($gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail']); ?>
                                        <?php echo '<pre>' . __("View Name:", 'ga-dash') . "\t" . esc_html($profile_info[0]) . "<br />" . __("Tracking ID:", 'ga-dash') . "\t" . esc_html($profile_info[2]) . "<br />" . __("Default URL:", 'ga-dash') . "\t" . esc_html($profile_info[3]) . "<br />" . __("Time Zone:", 'ga-dash') . "\t" . esc_html($profile_info[5]) . '</pre>';?>
                                    </td>
                                </tr>
								<?php }?>
								<tr>
                                    <td colspan="2"><hr><?php echo "<h2>" . __( "Basic Tracking", 'ga-dash' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_dash_tracking_type"><?php _e("Tracking Type:", 'ga-dash' ); ?></label></td>
                                    <td><select id="ga_dash_tracking_type" name="options[ga_dash_tracking_type]">
                                            <option value="classic" <?php selected( $options['ga_dash_tracking_type'], 'classic' ); ?>><?php _e("Classic Analytics", 'ga-dash');?></option>
                                            <option value="universal" <?php selected( $options['ga_dash_tracking_type'], 'universal' ); ?>><?php _e("Universal Analytics", 'ga-dash');?></option>
                                    </select></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_dash_anonim]" value="1" class="onoffswitch-checkbox" id="ga_dash_anonim" <?php checked( $options['ga_dash_anonim'], 1 ); ?>> <label class="onoffswitch-label" for="ga_dash_anonim">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("anonymize IPs while tracking", 'ga-dash' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_dash_remarketing]" value="1" class="onoffswitch-checkbox" id="ga_dash_remarketing" <?php checked( $options['ga_dash_remarketing'], 1 ); ?>> <label class="onoffswitch-label" for="ga_dash_remarketing">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable remarketing, demographics and interests reports", 'ga-dash' );?></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-events">
                            <table class="options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Events Tracking", 'ga-dash' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_event_tracking]" value="1" class="onoffswitch-checkbox" id="ga_event_tracking" <?php checked( $options['ga_event_tracking'], 1 ); ?>> <label class="onoffswitch-label" for="ga_event_tracking">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("track downloads, mailto and outbound links", 'ga-dash' ); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_event_downloads"><?php _e("Downloads Regex:", 'ga-dash'); ?></label></td>
                                    <td><input type="text" id="ga_event_downloads" name="options[ga_event_downloads]" value="<?php echo esc_attr($options['ga_event_downloads']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_aff_tracking]" value="1" class="onoffswitch-checkbox" id="ga_aff_tracking" <?php checked( $options['ga_aff_tracking'], 1 ); ?>> <label class="onoffswitch-label" for="ga_aff_tracking">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("track affiliate links matching this regex", 'ga-dash' ); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_event_affiliates"><?php _e("Affiliates Regex:", 'ga-dash'); ?></label></td>
                                    <td><input type="text" id="ga_event_affiliates" name="options[ga_event_affiliates]" value="<?php echo esc_attr($options['ga_event_affiliates']); ?>" size="50"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_hash_tracking]" value="1" class="onoffswitch-checkbox" id="ga_hash_tracking" <?php checked( $options['ga_hash_tracking'], 1 ); ?>> <label class="onoffswitch-label" for="ga_hash_tracking">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("track fragment identifiers, hashmarks (#) in URI links", 'ga-dash' ); ?></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-custom">
                            <table class="options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Custom Definitions", 'ga-dash' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_author_dimindex"><?php _e("Authors:", 'ga-dash' ); ?></label></td>
                                    <td><select id="ga_author_dimindex" name="options[ga_author_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_author_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_pubyear_dimindex"><?php _e("Publication Year:", 'ga-dash' ); ?></label></td>
                                    <td><select id="ga_pubyear_dimindex" name="options[ga_pubyear_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_pubyear_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_category_dimindex"><?php _e("Categories:", 'ga-dash' ); ?></label></td>
                                    <td><select id="ga_category_dimindex" name="options[ga_category_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_category_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_user_dimindex"><?php _e("User Type:", 'ga-dash' ); ?></label></td>
                                    <td><select id="ga_user_dimindex" name="options[ga_user_dimindex]">
										<?php for ($i=0;$i<21;$i++){?>
										<option value="<?php echo $i;?>" <?php selected( $options['ga_user_dimindex'], $i ); ?>><?php echo $i==0?'Disabled':'dimension '.$i; ?></option>
										<?php }?>
								</select></td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-advanced">
                            <table class="options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Advanced Tracking", 'ga-dash' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_speed_samplerate"><?php _e("Page Speed SR:", 'ga-dash'); ?></label></td>
                                    <td><input type="number" id="ga_speed_samplerate" name="options[ga_speed_samplerate]" value="<?php echo (int)($options['ga_speed_samplerate']); ?>" max="100" min="1"> %</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_event_bouncerate]" value="1" class="onoffswitch-checkbox" id="ga_event_bouncerate" <?php checked( $options['ga_event_bouncerate'], 1 ); ?>> <label class="onoffswitch-label" for="ga_event_bouncerate">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("exclude events from bounce-rate calculation", 'ga-dash' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_enhanced_links]" value="1" class="onoffswitch-checkbox" id="ga_enhanced_links" <?php checked( $options['ga_enhanced_links'], 1 ); ?>> <label class="onoffswitch-label" for="ga_enhanced_links">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable enhanced link attribution", 'ga-dash' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_dash_adsense]" value="1" class="onoffswitch-checkbox" id="ga_dash_adsense" <?php checked( $options['ga_dash_adsense'], 1 ); ?>> <label class="onoffswitch-label" for="ga_dash_adsense">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable AdSense account linking", 'ga-dash' );?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="title">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="options[ga_crossdomain_tracking]" value="1" class="onoffswitch-checkbox" id="ga_crossdomain_tracking" <?php checked( $options['ga_crossdomain_tracking'], 1 ); ?>> <label class="onoffswitch-label" for="ga_crossdomain_tracking">
                                                <div class="onoffswitch-inner"></div>
                                                <div class="onoffswitch-switch"></div>
                                            </label>
                                        </div>
                                        <div class="switch-desc"><?php echo " ".__("enable cross domain tracking", 'ga-dash' ); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="title"><label for="ga_crossdomain_list"><?php _e("Cross Domains:", 'ga-dash'); ?></label></td>
                                    <td><input type="text" id="ga_crossdomain_list" name="options[ga_crossdomain_list]" value="<?php echo esc_attr($options['ga_crossdomain_list']); ?>" size="50"></td>
                                </tr>
                            </table>
                        </div>
                        <div id="gadwp-exclude">
                            <table class="options">
                                <tr>
                                    <td colspan="2"><?php echo "<h2>" . __( "Exclude Tracking", 'ga-dash' ) . "</h2>"; ?></td>
                                </tr>
                                <tr>
                                    <td class="roles title"><label for="ga_track_exclude"><?php _e("Exclude tracking for:", 'ga-dash' ); ?></label></td>
                                    <td class="roles">
                               		<?php
        if (! isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        $i = 0;
        ?>		<table>
                                            <tr>
									<?php
        foreach ($wp_roles->role_names as $role => $name) {
            $i ++;
            ?>
	                                    	<td><label> <input type="checkbox" name="options[ga_track_exclude][]" value="<?php echo $role; ?>" <?php if (in_array($role,$options['ga_track_exclude'])) echo 'checked="checked"'; ?> />
	                                        	<?php echo $name; ?>
											</label></td>
	                                    <?php
            if ($i % 4 == 0) {
                ?>
                                 			</tr>
                                            <tr>
                                    		<?php
            }
        }
        ?>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <table class="options">
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'ga-dash' ) ?>" /></td>
                            </tr>
                        </table>
                        <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>

</form>
<?php
        self::output_sidebar();
    }

    public static function errors_debugging()
    {
        global $wp_version;
        
        $gadwp = GADWP();
        
        if (! current_user_can('manage_options')) {
            return;
        }
        
        $anonim = $gadwp->config->options;
        $anonim['wp_version'] = $wp_version;
        $anonim['gadwp_version'] = GADWP_CURRENT_VERSION;
        if ($anonim['ga_dash_token']) {
            $anonim['ga_dash_token'] = 'HIDDEN';
        }
        if ($anonim['ga_dash_refresh_token']) {
            $anonim['ga_dash_refresh_token'] = 'HIDDEN';
        }
        if ($anonim['ga_dash_clientid']) {
            $anonim['ga_dash_clientid'] = 'HIDDEN';
        }
        if ($anonim['ga_dash_clientsecret']) {
            $anonim['ga_dash_clientsecret'] = 'HIDDEN';
        }
        if ($anonim['ga_dash_apikey']) {
            $anonim['ga_dash_apikey'] = 'HIDDEN';
        }
        
        $options = self::update_options('frontend');
        if (! $gadwp->config->options['ga_dash_tableid_jail'] || ! $gadwp->config->options['ga_dash_token']) {
            $message = sprintf('<div class="error"><p>%s</p></div>', sprintf(__('Something went wrong, check %1$s or %2$s.', 'ga-dash'), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_errors_debugging', false), __('Errors & Debug', 'ga-dash')), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_settings', false), __('authorize the plugin', 'ga-dash'))));
        }
        ?>
<div class="wrap">
    	<?php echo "<h2>" . __( "Google Analytics Errors & Debugging", 'ga-dash' ) . "</h2>"; ?>
    	</div>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
            <div class="settings-wrapper">
                <div class="inside">
    					<?php if (isset($message)) echo $message; ?>
    					<?php
        $tabs = array(
            'errors' => __("Errors & Details", 'ga-dash'),
            'config' => __("Plugin Settings", 'ga-dash')
        );
        self::navigation_tabs($tabs);
        ?>
						<div id="gadwp-errors">
                        <table class="options">
                            <tr>
                                <td>
                                    <?php echo "<h2>" . __( "Last Error detected", 'ga-dash' ) . "</h2>"; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                    				<?php
        $errors = esc_html(print_r(get_transient('ga_dash_lasterror'), true)) ? esc_html(print_r(get_transient('ga_dash_lasterror'), true)) : __("None", 'ga-dash');
        echo '<pre class="log_data">Last Error: ';
        echo $errors;
        ?></pre>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr><?php echo "<h2>" . __( "Error Details", 'ga-dash' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td>
                    				<?php
        echo '<pre class="log_data">Error Details: ';
        $error_details = esc_html(print_r(get_transient('ga_dash_gapi_errors'), true)) ? "\n" . esc_html(print_r(get_transient('ga_dash_gapi_errors'), true)) : __("None", 'ga-dash');
        echo $error_details;
        ?></pre><br />
                                    <hr>
                                </td>
                            
                            
                            <tr>
                        
                        </table>
                    </div>
                    <div id="gadwp-config">
                        <table class="options">
                            <tr>
                                <td><?php echo "<h2>" . __( "Plugin Configuration", 'ga-dash' ) . "</h2>"; ?></td>
                            </tr>
                            <tr>
                                <td><pre class="log_data"><?php echo esc_html(print_r($anonim, true));?></pre><br />
                                <hr></td>
                            </tr>
                        </table>
                    </div>
    <?php
        self::output_sidebar();
    }

    public static function general_settings()
    {
        $gadwp = GADWP();
        
        global $wp_version;
        
        if (! current_user_can('manage_options')) {
            return;
        }
        $options = self::update_options('general');
        printf('<div id="gapi-warning" class="updated"><p>%1$s <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=gadwp">%2$s</a></p></div>', __('Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', "ga-dash"), __('Library conflicts between WordPress plugins', "ga-dash"));
        if (null === $gadwp->gapi_controller) {
            $gadwp->gapi_controller = new GADWP_GAPI_Controller();
        }
        echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
        if (isset($_POST['ga_dash_code'])) {
            if (! stripos('x' . $_POST['ga_dash_code'], 'UA-', 1) == 1) {
                try {
                    $gadwp->gapi_controller->client->authenticate($_POST['ga_dash_code']);
                    $gadwp->config->options['ga_dash_token'] = $gadwp->gapi_controller->client->getAccessToken();
                    $google_token = json_decode($gadwp->gapi_controller->client->getAccessToken());
                    $gadwp->config->options['ga_dash_refresh_token'] = $google_token->refresh_token;
                    $gadwp->config->options['automatic_updates_minorversion'] = 1;
                    $gadwp->config->set_plugin_options();
                    $options = self::update_options('general');
                    $message = "<div class='updated'><p>" . __("Plugin authorization succeeded.", 'ga-dash') . "</p></div>";
                    delete_transient('ga_dash_gapi_errors');
                    delete_transient('ga_dash_lasterror');
                } catch (Google_IO_Exception $e) {
                    set_transient('ga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $gadwp->gapi_controller->error_timeout);
                    return false;
                } catch (Google_Service_Exception $e) {
                    set_transient('ga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $gadwp->gapi_controller->error_timeout);
                    set_transient('ga_dash_gapi_errors', $e->getErrors(), $gadwp->gapi_controller->error_timeout);
                    return $e->getCode();
                } catch (Exception $e) {
                    set_transient('ga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e) . "\nResponseHttpCode:" . $e->getCode(), $gadwp->gapi_controller->error_timeout);
                    $gadwp->gapi_controller->reset_token(false);
                }
            } else {
                $message = "<div class='error'><p>" . __("The access code is <strong>NOT</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X). Try again, and use the red link to get your access code", 'ga-dash') . ".</p></div>";
            }
        }
        if ($gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken()) {
            if (!empty($gadwp->config->options['ga_dash_profile_list'])) {
                $profiles = $gadwp->config->options['ga_dash_profile_list'];
            } else {
                $profiles = $gadwp->gapi_controller->refresh_profiles();
            }
            if ($profiles) {
                $gadwp->config->options['ga_dash_profile_list'] = $profiles;
                if (! $gadwp->config->options['ga_dash_tableid_jail']) {
                    $profile = GADWP_Tools::guess_default_domain($profiles);
                    $gadwp->config->options['ga_dash_tableid_jail'] = $profile;
                    $gadwp->config->options['ga_dash_tableid'] = $profile;
                }
                $gadwp->config->set_plugin_options();
                $options = self::update_options('general');
            }
        }
        if (isset($_POST['Clear'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                GADWP_Tools::clear_cache();
                $message = "<div class='updated'><p>" . __("Cleared Cache.", 'ga-dash') . "</p></div>";
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['Reset'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                $gadwp->gapi_controller->reset_token(true);
                GADWP_Tools::clear_cache();
                $message = "<div class='updated'><p>" . __("Token Reseted and Revoked.", 'ga-dash') . "</p></div>";
                $options = self::update_options('Reset');
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['Reset_Err'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                delete_transient('ga_dash_lasterror');
                delete_transient('ga_dash_gapi_errors');
                $message = "<div class='updated'><p>" . __("All errors reseted.", 'ga-dash') . "</p></div>";
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['options']['ga_dash_hidden']) && ! isset($_POST['Clear']) && ! isset($_POST['Reset']) && ! isset($_POST['Reset_Err'])) {
            $message = "<div class='updated'><p>" . __("Settings saved.", 'ga-dash') . "</p></div>";
            if (! (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form'))) {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['Hide'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                $message = "<div class='updated'><p>" . __("All other domains/properties were removed.", 'ga-dash') . "</p></div>";
                $lock_profile = GADWP_Tools::get_selected_profile($gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail']);
                $gadwp->config->options['ga_dash_profile_list'] = array(
                    $lock_profile
                );
                $options = self::update_options('general');
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        ?>
    <div class="wrap">
	<?php echo "<h2>" . __( "Google Analytics Settings", 'ga-dash' ) . "</h2>"; ?>
	                <hr>
                    </div>
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">
                                <div class="settings-wrapper">
                                    <div class="inside"><?php
        if ($gadwp->gapi_controller->gapi_errors_handler()) {
            $message = sprintf('<div class="error"><p>%s</p></div>', sprintf(__('Something went wrong, check %1$s or %2$s.', 'ga-dash'), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_errors_debugging', false), __('Errors & Debug', 'ga-dash')), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_settings', false), __('authorize the plugin', 'ga-dash'))));
        }
        if (isset($_POST['Authorize'])) {
            GADWP_Tools::clear_cache();
            $gadwp->gapi_controller->token_request();
            echo "<div class='updated'><p>" . __("Use the red link (see below) to generate and get your access code!", 'ga-dash') . "</p></div>";
        } else {
            if (isset($message))
                echo $message;
            ?>
					<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                                            <input type="hidden" name="options[ga_dash_hidden]" value="Y">
						<?php wp_nonce_field('gadash_form','gadash_security'); ?>
						<table class="options">
                                                <tr>
                                                    <td colspan="2">
                                                        <?php echo "<h2>" . __( "Plugin Authorization", 'ga-dash' ) . "</h2>";?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="info">
                                                        <?php printf(__('You should watch the %1$s and read this %2$s before proceeding to authorization. This plugin requires a properly configured Google Analytics account!', 'ga-dash'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_video&utm_campaign=gadwp', __("video", 'ga-dash')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=gadwp', __("tutorial", 'ga-dash')));?>
						                            </td>
                                                </tr>
						                          <?php if (! $options['ga_dash_token'] || $options['ga_dash_userapi']) {?>
						                          <tr>
                                                    <td colspan="2" class="info"><input name="options[ga_dash_userapi]" type="checkbox" id="ga_dash_userapi" value="1" <?php checked( $options['ga_dash_userapi'], 1 ); ?> onchange="this.form.submit()" <?php echo ($options['ga_dash_network'])?'disabled="disabled"':''; ?> /><?php echo " ".__("use your own API Project credentials", 'ga-dash' );?>
							                            </td>
                                                </tr>                                				  <?php } if ($options['ga_dash_userapi']) { ?>
						                        <tr>
                                                    <td class="title"><label for="options[ga_dash_apikey]"><?php _e("API Key:", 'ga-dash'); ?></label></td>
                                                    <td><input type="text" name="options[ga_dash_apikey]" value="<?php echo esc_attr($options['ga_dash_apikey']); ?>" size="40" required="required"></td>
                                                </tr>
                                                <tr>
                                                    <td class="title"><label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'ga-dash'); ?></label></td>
                                                    <td><input type="text" name="options[ga_dash_clientid]" value="<?php echo esc_attr($options['ga_dash_clientid']); ?>" size="40" required="required"></td>
                                                </tr>
                                                <tr>
                                                    <td class="title"><label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'ga-dash'); ?></label></td>
                                                    <td><input type="text" name="options[ga_dash_clientsecret]" value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>" size="40" required="required"> <input type="hidden" name="options[ga_dash_hidden]" value="Y">
									                    <?php wp_nonce_field('gadash_form','gadash_security'); ?>
								                    </td>
                                                </tr>
						<?php
            }
            if ($options['ga_dash_token']) {
                ?>
					                            <tr>
                                                    <td colspan="2"><input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Clear Authorization", 'ga-dash' ); ?>" <?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /> <input type="submit" name="Reset_Err" class="button button-secondary" value="<?php _e( "Reset Errors", 'ga-dash' ); ?>" /></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><?php echo "<h2>" . __( "General Settings", 'ga-dash' ) . "</h2>"; ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="title"><label for="ga_dash_tableid_jail"><?php _e("Select View:", 'ga-dash' ); ?></label></td>
                                                    <td><select id="ga_dash_tableid_jail" <?php disabled(empty($options['ga_dash_profile_list']) || 1 == count($options['ga_dash_profile_list']), true); ?> name="options[ga_dash_tableid_jail]">
                                    								<?php
                if (!empty($options['ga_dash_profile_list'])) {
                    foreach ($options['ga_dash_profile_list'] as $items) {
                        if ($items[3]) {
                            echo '<option value="' . esc_attr($items[1]) . '" ' . selected($items[1], $options['ga_dash_tableid_jail']);
                            echo ' title="' . __("View Name:", 'ga-dash') . ' ' . esc_attr($items[0]) . '">' . esc_html(GADWP_Tools::strip_protocol($items[3])) . ' &#8658; ' . esc_attr($items[0]) . '</option>';
                        }
                    }
                } else {
                    echo '<option value="">' . __("Property not found", 'ga-dash') . '</option>';
                }
                ?>
                                    							</select>                                    							<?php
                if (count($options['ga_dash_profile_list']) > 1) {
                    ?>&nbsp;<input type="submit" name="Hide" class="button button-secondary" value="<?php _e( "Lock Selection", 'ga-dash' ); ?>" /><?php
                }
                ?>
							                         </td>
                                                </tr>
							<?php
                if ($options['ga_dash_tableid_jail']) {
                    ?>
							<tr>
                                                    <td class="title"></td>
                                                    <td><?php
                    $profile_info = GADWP_Tools::get_selected_profile($gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail']);
                    echo '<pre>' . __("View Name:", 'ga-dash') . "\t" . esc_html($profile_info[0]) . "<br />" . __("Tracking ID:", 'ga-dash') . "\t" . esc_html($profile_info[2]) . "<br />" . __("Default URL:", 'ga-dash') . "\t" . esc_html($profile_info[3]) . "<br />" . __("Time Zone:", 'ga-dash') . "\t" . esc_html($profile_info[5]) . '</pre>';
                    ?></td>
                                                </tr>
							<?php
                }
                ?>							                     <tr>
                                                    <td class="title"><label for="ga_dash_style"><?php _e("Theme Color:", 'ga-dash' ); ?></label></td>
                                                    <td><input type="text" id="ga_dash_style" class="ga_dash_style" name="options[ga_dash_style]" value="<?php echo esc_attr($options['ga_dash_style']); ?>" size="10"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
												<?php if ( !is_multisite()) {?>
												<tr>
                                                    <td colspan="2"><?php echo "<h2>" . __( "Automatic Updates", 'ga-dash' ) . "</h2>"; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="title">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="onoffswitch-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>> <label class="onoffswitch-label" for="automatic_updates_minorversion">
                                                                <div class="onoffswitch-inner"></div>
                                                                <div class="onoffswitch-switch"></div>
                                                            </label>
                                                        </div>
                                                        <div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'ga-dash' );?></div>
                                                    </td>
                                                </tr>
												<tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>                                                
												<?php }?>
                                                <tr>
                                                    <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'ga-dash' ) ?>" /></td>
                                                </tr>		<?php } else {?>
							                    <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><input type="submit" name="Authorize" class="button button-secondary" id="authorize" value="<?php _e( "Authorize Plugin", 'ga-dash' ); ?>" <?php echo $options['ga_dash_network']?'disabled="disabled"':''; ?> /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                            </table>
                                        </form>
			<?php
                self::output_sidebar();
                return;
            }
            ?>					</table>
                                        </form><?php
        }
        self::output_sidebar();
    }
    // Network Settings
    public static function general_settings_network()
    {
        $gadwp = GADWP();
        global $wp_version;
        
        if (! current_user_can('manage_network_options')) {
            return;
        }
        $options = self::update_options('network');
        /*
         * Include GAPI
         */
        echo '<div id="gapi-warning" class="updated"><p>' . __('Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', "ga-dash") . ' <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=general_screen&utm_campaign=gadwp">Library conflicts between WordPress plugins</a></p></div>';
        
        if (null === $gadwp->gapi_controller) {
            $gadwp->gapi_controller = new GADWP_GAPI_Controller();
        }
        
        echo '<script type="text/javascript">jQuery("#gapi-warning").hide()</script>';
        if (isset($_POST['ga_dash_code'])) {
            if (! stripos('x' . $_POST['ga_dash_code'], 'UA-', 1) == 1) {
                try {
                    $gadwp->gapi_controller->client->authenticate($_POST['ga_dash_code']);
                    $gadwp->config->options['ga_dash_token'] = $gadwp->gapi_controller->client->getAccessToken();
                    $google_token = json_decode($gadwp->gapi_controller->client->getAccessToken());
                    $gadwp->config->options['ga_dash_refresh_token'] = $google_token->refresh_token;
                    $gadwp->config->options['automatic_updates_minorversion'] = 1;
                    $gadwp->config->set_plugin_options(true);
                    $options = self::update_options('network');
                    $message = "<div class='updated'><p>" . __("Plugin authorization succeeded.", 'ga-dash') . "</p></div>";
                    if (is_multisite()) { // Cleanup errors on the entire network
                        foreach (wp_get_sites(array(
                            'limit' => apply_filters('gadwp_sites_limit', 100)
                        )) as $blog) {
                            switch_to_blog($blog['blog_id']);
                            delete_transient('ga_dash_gapi_errors');
                            restore_current_blog();
                        }
                    } else {
                        delete_transient('ga_dash_gapi_errors');
                    }
                } catch (Google_IO_Exception $e) {
                    set_transient('ga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e), $gadwp->gapi_controller->error_timeout);
                    return false;
                } catch (Google_Service_Exception $e) {
                    set_transient('ga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()), $gadwp->gapi_controller->error_timeout);
                    set_transient('ga_dash_gapi_errors', $e->getErrors(), $gadwp->gapi_controller->error_timeout);
                    return $e->getCode();
                } catch (Exception $e) {
                    set_transient('ga_dash_lasterror', date('Y-m-d H:i:s') . ': ' . esc_html($e) . "\nResponseHttpCode:" . $e->getCode(), $gadwp->gapi_controller->error_timeout);
                    $gadwp->gapi_controller->reset_token(false);
                }
            } else {
                $message = "<div class='error'><p>" . __("The access code is <strong>NOT</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X). Try again, and use the red link to get your access code", 'ga-dash') . ".</p></div>";
            }
        }
        if (isset($_POST['Refresh'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                $gadwp->config->options['ga_dash_profile_list'] = array();
                $message = "<div class='updated'><p>" . __("Properties refreshed.", 'ga-dash') . "</p></div>";
                $options = self::update_options('network');
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if ($gadwp->config->options['ga_dash_token'] && $gadwp->gapi_controller->client->getAccessToken()) {
            if (!empty($gadwp->config->options['ga_dash_profile_list'])) {
                $profiles = $gadwp->config->options['ga_dash_profile_list'];
            } else {
                $profiles = $gadwp->gapi_controller->refresh_profiles();
            }
            if ($profiles) {
                $gadwp->config->options['ga_dash_profile_list'] = $profiles;
                if (isset($gadwp->config->options['ga_dash_tableid_jail']) && ! $gadwp->config->options['ga_dash_tableid_jail']) {
                    $profile = GADWP_Tools::guess_default_domain($profiles);
                    $gadwp->config->options['ga_dash_tableid_jail'] = $profile;
                    $gadwp->config->options['ga_dash_tableid'] = $profile;
                }
                $gadwp->config->set_plugin_options(true);
                $options = self::update_options('network');
            }
        }
        if (isset($_POST['Clear'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                GADWP_Tools::clear_cache();
                $message = "<div class='updated'><p>" . __("Cleared Cache.", 'ga-dash') . "</p></div>";
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['Reset'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                $gadwp->gapi_controller->reset_token(true);
                GADWP_Tools::clear_cache();
                $message = "<div class='updated'><p>" . __("Token Reseted and Revoked.", 'ga-dash') . "</p></div>";
                $options = self::update_options('Reset');
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['options']['ga_dash_hidden']) && ! isset($_POST['Clear']) && ! isset($_POST['Reset']) && ! isset($_POST['Refresh'])) {
            $message = "<div class='updated'><p>" . __("Settings saved.", 'ga-dash') . "</p></div>";
            if (! (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form'))) {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        if (isset($_POST['Hide'])) {
            if (isset($_POST['gadash_security']) && wp_verify_nonce($_POST['gadash_security'], 'gadash_form')) {
                $message = "<div class='updated'><p>" . __("All other domains/properties were removed.", 'ga-dash') . "</p></div>";
                $lock_profile = GADWP_Tools::get_selected_profile($gadwp->config->options['ga_dash_profile_list'], $gadwp->config->options['ga_dash_tableid_jail']);
                $gadwp->config->options['ga_dash_profile_list'] = array(
                    $lock_profile
                );
                $options = self::update_options('network');
            } else {
                $message = "<div class='error'><p>" . __("Cheating Huh?", 'ga-dash') . "</p></div>";
            }
        }
        ?>
	<div class="wrap">
		<?php echo "<h2>" . __( "Google Analytics Settings", 'ga-dash' ) . "</h2>"; ?><hr>
                                        </div>
                                        <div id="poststuff">
                                            <div id="post-body" class="metabox-holder columns-2">
                                                <div id="post-body-content">
                                                    <div class="settings-wrapper">
                                                        <div class="inside">						<?php
        if ($gadwp->gapi_controller->gapi_errors_handler()) {
            $message = sprintf('<div class="error"><p>%s</p></div>', sprintf(__('Something went wrong, check %1$s or %2$s.', 'ga-dash'), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_errors_debugging', false), __('Errors & Debug', 'ga-dash')), sprintf('<a href="%1$s">%2$s</a>', menu_page_url('gadash_settings', false), __('authorize the plugin', 'ga-dash'))));
        }
        if (isset($_POST['Authorize'])) {
            GADWP_Tools::clear_cache();
            $gadwp->gapi_controller->token_request();
            echo "<div class='updated'><p>" . __("Use the red link (see below) to generate and get your access code!", 'ga-dash') . "</p></div>";
        } else {
            if (isset($message))
                echo $message;
            ?>
						<form name="ga_dash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                                                                <input type="hidden" name="options[ga_dash_hidden]" value="Y">
							<?php wp_nonce_field('gadash_form','gadash_security'); ?>
							<table class="options">
                                                                    <tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Network Setup", 'ga-dash' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="title">
                                                                            <div class="onoffswitch">
                                                                                <input type="checkbox" name="options[ga_dash_network]" value="1" class="onoffswitch-checkbox" id="ga_dash_network" <?php checked( $options['ga_dash_network'], 1); ?> onchange="this.form.submit()"> <label class="onoffswitch-label" for="ga_dash_network">
                                                                                    <div class="onoffswitch-inner"></div>
                                                                                    <div class="onoffswitch-switch"></div>
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch-desc"><?php echo " ".__("use a single Google Analytics account for the entire network", 'ga-dash' );?></div>
                                                                        </td>
                                                                    </tr>
								<?php if ($options['ga_dash_network']){  //Network Mode check?>
								<tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Plugin Authorization", 'ga-dash' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="info">
							                                                 <?php printf(__('You should watch the %1$s and read this %2$s before proceeding to authorization. This plugin requires a properly configured Google Analytics account!', 'ga-dash'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_video&utm_campaign=gadwp', __("video", 'ga-dash')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=top_tutorial&utm_campaign=gadwp', __("tutorial", 'ga-dash')));?>
							                                             </td>
                                                                    </tr>
							<?php
                if (! $options['ga_dash_token'] || $options['ga_dash_userapi']) {
                    ?>
							                                        <tr>
                                                                        <td colspan="2" class="info"><input name="options[ga_dash_userapi]" type="checkbox" id="ga_dash_userapi" value="1" <?php checked( $options['ga_dash_userapi'], 1 ); ?> onchange="this.form.submit()" /><?php echo " ".__("use your own API Project credentials", 'ga-dash' );?>
								                                        </td>
                                                                    </tr>							<?php
                }
                if ($options['ga_dash_userapi']) {
                    ?>
							                                        <tr>
                                                                        <td class="title"><label for="options[ga_dash_apikey]"><?php _e("API Key:", 'ga-dash'); ?></label></td>
                                                                        <td><input type="text" name="options[ga_dash_apikey]" value="<?php echo esc_attr($options['ga_dash_apikey']); ?>" size="40" required="required"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="title"><label for="options[ga_dash_clientid]"><?php _e("Client ID:", 'ga-dash'); ?></label></td>
                                                                        <td><input type="text" name="options[ga_dash_clientid]" value="<?php echo esc_attr($options['ga_dash_clientid']); ?>" size="40" required="required"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="title"><label for="options[ga_dash_clientsecret]"><?php _e("Client Secret:", 'ga-dash'); ?></label></td>
                                                                        <td><input type="text" name="options[ga_dash_clientsecret]" value="<?php echo esc_attr($options['ga_dash_clientsecret']); ?>" size="40" required="required"> <input type="hidden" name="options[ga_dash_hidden]" value="Y">
										<?php wp_nonce_field('gadash_form','gadash_security'); ?>
									</td>
                                                                    </tr>
							<?php
                }
                if ($options['ga_dash_token']) {
                    ?>
						<tr>
                                                                        <td colspan="2"><input type="submit" name="Reset" class="button button-secondary" value="<?php _e( "Clear Authorization", 'ga-dash' ); ?>" /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /> <input type="submit" name="Refresh" class="button button-secondary" value="<?php _e( "Refresh Properties", 'ga-dash' ); ?>" /></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Properties/Views Settings", 'ga-dash' ) . "</h2>"; ?></td>
                                                                    </tr>
								<?php
                    if (isset($options['ga_dash_tableid_network'])) {
                        $options['ga_dash_tableid_network'] = json_decode(json_encode($options['ga_dash_tableid_network']), false);
                    }
                    foreach (wp_get_sites(array(
                        'limit' => apply_filters('gadwp_sites_limit', 100)
                    )) as $blog) {
                        ?>
							                                         <tr>
                                                                        <td class="title-select"><label for="ga_dash_tableid_network"><?php echo '<strong>'.$blog['domain'].$blog['path'].'</strong>: ';?></label></td>
                                                                        <td><select id="ga_dash_tableid_network" <?php disabled(!empty($options['ga_dash_profile_list']),false);?> name="options[ga_dash_tableid_network][<?php echo $blog['blog_id'];?>]">
									<?php
                        if (!empty($options['ga_dash_profile_list'])) {
                            foreach ($options['ga_dash_profile_list'] as $items) {
                                if ($items[3]) {
                                    echo '<option value="' . esc_attr($items[1]) . '" ' . selected($items[1], isset($options['ga_dash_tableid_network']->$blog['blog_id']) ? $options['ga_dash_tableid_network']->$blog['blog_id'] : '');
                                    echo ' title="' . __("View Name:", 'ga-dash') . ' ' . esc_attr($items[0]) . '">' . esc_html(GADWP_Tools::strip_protocol($items[3])) . ' &#8658; ' . esc_attr($items[0]) . '</option>';
                                }
                            }
                        } else {
                            echo '<option value="">' . __("Property not found", 'ga-dash') . '</option>';
                        }
                        ?>
    								                                    </select> <br /></td>
                                                                    </tr>
							<?php
                    }
                    ?>
                    												<tr>
                                                                        <td colspan="2"><?php echo "<h2>" . __( "Automatic Updates", 'ga-dash' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="title">
                                                                            <div class="onoffswitch">
                                                                                <input type="checkbox" name="options[automatic_updates_minorversion]" value="1" class="onoffswitch-checkbox" id="automatic_updates_minorversion" <?php checked( $options['automatic_updates_minorversion'], 1 ); ?>> <label class="onoffswitch-label" for="automatic_updates_minorversion">
                                                                                    <div class="onoffswitch-inner"></div>
                                                                                    <div class="onoffswitch-switch"></div>
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch-desc"><?php echo " ".__( "automatic updates for minor versions (security and maintenance releases only)", 'ga-dash' );?></div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><hr><?php echo "<h2>" . __( "Exclude Tracking", 'ga-dash' ) . "</h2>"; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="title">
                                                                            <div class="onoffswitch">
                                                                                <input type="checkbox" name="options[ga_dash_excludesa]" value="1" class="onoffswitch-checkbox" id="ga_dash_excludesa"<?php checked( $options['ga_dash_excludesa'], 1); ?>"> <label class="onoffswitch-label" for="ga_dash_excludesa">
                                                                                    <div class="onoffswitch-inner"></div>
                                                                                    <div class="onoffswitch-switch"></div>
                                                                                </label>
                                                                            </div>
                                                                            <div class="switch-desc"><?php echo " ".__("exclude Super Admin tracking for the entire network", 'ga-dash' );?></div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'ga-dash' ) ?>" /></td>
                                                                    </tr>			<?php
                } else {
                    ?>								                                    <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input type="submit" name="Authorize" class="button button-secondary" id="authorize" value="<?php _e( "Authorize Plugin", 'ga-dash' ); ?>" /> <input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'ga-dash' ); ?>" /></td>
                                                                    </tr>
								   <?php }  //Network Mode check?>
									                                <tr>
                                                                        <td colspan="2"><hr></td>
                                                                    </tr>
                                                                </table>
                                                            </form>
				<?php
                self::output_sidebar();
                return;
            }
            ?>
						</table>
                                                            </form>
	<?php
        }
        self::output_sidebar();
    }

    public static function output_sidebar()
    {
        $gadwp = GADWP();
        ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="postbox-container-1" class="postbox-container">
                                                    <div class="meta-box-sortables">
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Setup Tutorial & Demo",'ga-dash') ?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=video&utm_campaign=gadwp" target="_blank"><img src="<?php echo plugins_url( 'images/google-analytics-dashboard.png' , __FILE__ );?>" width="100%" alt="" /></a>
                                                            </div>
                                                        </div>
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Support & Reviews",'ga-dash')?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=support&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/help.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('Plugin documentation and support on %s', 'ga-dash'), '<a href="https://deconf.com/google-analytics-dashboard-wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=support&utm_campaign=gadwp">deconf.com</a>');?>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-title">
                                                                    <a href="http://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp#plugin-info"><img src="<?php echo plugins_url( 'images/star.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('Your feedback and review are both important, %s!', 'ga-dash'), sprintf('<a href="http://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp#plugin-info">%s</a>', __('rate this plugin', 'ga-dash')));?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Further Reading",'ga-dash')?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/move-website-https-ssl/?utm_source=gadwp_config&utm_medium=link&utm_content=ssl&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/ssl.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('%s by moving your website to HTTPS/SSL.', 'ga-dash'), sprintf('<a href="https://deconf.com/move-website-https-ssl/?utm_source=gadwp_config&utm_medium=link&utm_content=ssl&utm_campaign=gadwp">%s</a>', __('Improve search rankings', 'ga-dash')));?>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=plugins&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/wp.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('Other %s written by the same author', 'ga-dash'), sprintf('<a href="https://deconf.com/wordpress/?utm_source=gadwp_config&utm_medium=link&utm_content=plugins&utm_campaign=gadwp">%s</a>', __('WordPress Plugins', 'ga-dash')));?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="postbox">
                                                            <h3>
                                                                <span><?php _e("Other Services",'ga-dash')?></span>
                                                            </h3>
                                                            <div class="inside">
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/wordpress-cdn-speeds-up-your-site/"><img src="<?php echo plugins_url( 'images/mcdn.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('Speed up your website and plug into a whole %s', 'ga-dash'), sprintf('<a href="https://deconf.com/wordpress-cdn-speeds-up-your-site/?utm_source=gadwp_config&utm_medium=link&utm_content=maxcdn&utm_campaign=gadwp">%s</a>.', __('new level of site speed', 'ga-dash')));?>
                                                                </div>
                                                                <br />
                                                                <div class="gadash-title">
                                                                    <a href="https://deconf.com/clicky-web-analytics-review/?utm_source=gadwp_config&utm_medium=link&utm_content=clicky&utm_campaign=gadwp"><img src="<?php echo plugins_url( 'images/clicky.png' , __FILE__ ); ?>" /></a>
                                                                </div>
                                                                <div class="gadash-desc">
                                                                    <?php printf(__('%s service with users tracking at IP level.', 'ga-dash'), sprintf('<a href="https://deconf.com/clicky-web-analytics-review/?utm_source=gadwp_config&utm_medium=link&utm_content=clicky&utm_campaign=gadwp">%s</a>', __('Web Analytics', 'ga-dash')));?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
<?php }
}
