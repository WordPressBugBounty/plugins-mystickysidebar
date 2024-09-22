<?php
/*
Plugin Name: WP Sticky Sidebar
Plugin URI: https://premio.io/
Description: Simple sticky sidebar implementation. After install go to Settings / WP Sticky Sidebar and change Sticky Class to .your_sidebar_class.
Version: 1.4.1
Author: Premio
Author URI: https://premio.io/downloads/wpstickysidebar/
Text Domain: mystickysidebar
Domain Path: /languages
*/

defined('ABSPATH') or die("Cannot access pages directly.");

define('STICKY_SIDEBAR_VER', "1.4.1");
define('STICKY_SIDEBAR_PATH', plugin_basename(__FILE__) );
define('STICKY_SIDEBAR_URL', plugin_dir_url(__FILE__) );

class MyStickysidebarBackend
{

    private $options;

	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'mystickyside_load_transl') );
		add_action( 'admin_init', array( $this, 'mystickyside_default_options' ) );

		add_filter( 'plugin_action_links_mystickysidebar/mystickysidebar.php', array( $this, 'mystickysidebar_settings_link' )  );
		add_action( 'activated_plugin', array( $this, 'mystickysidebar_activation_redirect' ) );
		add_action( 'admin_init', array( $this, 'check_for_redirection' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'mystickysidebar_admin_script' ) );
    }

	public static function sanitize_options($value, $type = "") {
		$value = stripslashes($value);
		if($type == "int") {
			$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
		} else if($type == "email") {
			$value = sanitize_email($value);
		} else {
			$value = sanitize_text_field($value);
		}
		return $value;
	}

	public function mystickysidebar_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=my-stickysidebar-settings">Settings</a>';
		$links['go_pro'] = '<a href="'.admin_url("admin.php?page=my-stickysidebar-settings&type=upgrade").'" style="color: #FF5983;font-weight: bold;">'.__( 'Upgrade', 'mystickysidebar' ).'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	public function mystickysidebar_activation_redirect( $plugin ) {
		if( $plugin == plugin_basename( __FILE__ ) ) {
			if(!defined( 'DOING_AJAX' )) {
                add_option("check_for_mss_redirection", 1);
			}
		}
	}

    public function check_for_redirection()
    {
        if(!defined( 'DOING_AJAX' )) {
            $status = get_option("check_for_mss_redirection");
            if($status) {
                delete_option("check_for_mss_redirection");
                wp_redirect(admin_url("admin.php?page=my-stickysidebar-settings"));
                exit;
            }
        }

        if(isset($_GET['hide_my_sticky_sidebar_plugins'])) {
            $nonce = isset($_GET['mss_nonce'])?esc_attr($_GET['mss_nonce']):'';
            if($nonce && wp_verify_nonce($nonce, 'hide_my_sticky_sidebar_plugins')) {
                add_option('hide_my_sticky_sidebar_plugins', 1);
                wp_redirect(admin_url("admin.php?page=my-stickysidebar-settings"));
                exit;
            }
        }
    }
	public function mystickysidebar_admin_script() {

		if ( isset($_GET['page']) && ($_GET['page'] == 'my-stickysidebar-settings' || $_GET['page'] == 'my-sticky-sidebar-settings-upgrade')) {

			wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css?family=Poppins:400,600,700' );

			wp_register_style('mystickysidebar-admin-css', plugins_url('/css/mystickysidebar-admin.css', __FILE__), array(), STICKY_SIDEBAR_VER );
			wp_enqueue_style('mystickysidebar-admin-css');
		}
	}
	public function mystickyside_load_transl()
	{
		load_plugin_textdomain('mystickysidebar', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
	}

	public function add_plugin_page()
	{
        if (current_user_can('manage_options')) {
            $hasPluginPage = get_option("hide_my_sticky_sidebar_plugins");
            add_menu_page(
                esc_attr__('WP Sticky Sidebar', 'mystickysidebar'),
                esc_attr__('WP Sticky Sidebar', 'mystickysidebar'),
                'manage_options',
                'my-stickysidebar-settings',
                [$this,'create_admin_page'],
                'dashicons-sticky'
            );
            add_submenu_page(
                'my-stickysidebar-settings',
                esc_attr__('Settings', 'mystickysidebar'),
                esc_attr__('Settings', 'mystickysidebar'),
                'manage_options',
                'my-stickysidebar-settings',
                [$this,'create_admin_page']
            );
            if($hasPluginPage != 1) {
                add_submenu_page(
                    'my-stickysidebar-settings',
                    esc_attr__('Recommended Plugins', 'mystickysidebar'),
                    esc_attr__('Recommended Plugins', 'mystickysidebar'),
                    'manage_options',
                    'my-sticky-sidebar-plugins',
                    [$this, 'recommended_plugins']
                );
            }
            add_submenu_page(
                'my-stickysidebar-settings',
                esc_attr__('Upgrade to Pro', 'mystickysidebar'),
                esc_attr__('Upgrade to Pro', 'mystickysidebar'),
                'manage_options',
                'my-sticky-sidebar-settings-upgrade',
                [$this,'upgrade_to_pro']
            );
        }
	}

    public function recommended_plugins()
    {
        include_once plugin_dir_path( __FILE__ ) . 'recommended-plugins.php';
    }

    public function upgrade_to_pro()
    { ?>
        <style>
            div#wpcontent {
                background: rgba(101,114,219,1);
                background: -moz-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
                background: -webkit-gradient(left top, right bottom, color-stop(0%, rgba(101,114,219,1)), color-stop(67%, rgba(238,134,198,1)), color-stop(100%, rgba(238,134,198,1)));
                background: -webkit-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
                background: -o-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
                background: -ms-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
                background: linear-gradient(135deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6572db', endColorstr='#ee86c6', GradientType=1 );
            }
        </style>
        <script>
            jQuery(document).ready(function(){
                jQuery(".sticky-sidebar-menu ul li a").on( 'click', function(e){
                    e.preventDefault();
                    if(!jQuery(this).hasClass("active")) {
                        jQuery(".sticky-sidebar-menu ul li a").removeClass("active");
                        jQuery(this).addClass("active");
                        thisHref = jQuery(this).attr("href");
                        jQuery(".sticky-sidebar-content").hide();
                        jQuery(thisHref).show();
                    }
                });
                jQuery(".sticky-sidebar-upgrade-now").on( 'click', function(e){
                    e.preventDefault();
                    jQuery(".sticky-sidebar-menu ul li a:last").trigger("click");
                });
                jQuery(".multiple-options").on( 'change', function(){
                    thisValue = jQuery(this).val();
                    jQuery(this).closest(".rpt_plan").find("a.rpt_foot").attr("href", thisValue);
                    thisPrice = jQuery(this).find("option:selected").attr("data-price");
                    jQuery(this).closest(".rpt_plan").find(".rpt_price").text("$"+thisPrice);
                    priceText = jQuery(this).find("option:selected").attr("data-header");
                    jQuery(this).closest(".rpt_plan").find(".rpt_desc").text(priceText);
                });
            });
        </script>

        <?php $pro_url = "https://go.premio.io/?edd_action=add_to_cart&download_id=2525&edd_options[price_id]=" ?>
        <div id="mystickysidebar" class="mystickysidebar wrap">
            <div style="display: block" id="sticky-sidebar-upgrade" class="sticky-sidebar-content">
                <div id="rpt_pricr" class="rpt_plans rpt_3_plans  rpt_style_basic">
                    <p class="udner-title">
                        <strong class="text-primary">Unlock All Features</strong>
                    </p>
                    <div class="">
                        <div class="rpt_plan  rpt_plan_0  ">
                            <div style="text-align:left;" class="rpt_title rpt_title_0">Basic</div>
                            <div class="rpt_head rpt_head_0">
                                <div class="rpt_recurrence rpt_recurrence_0">For small website owners</div>
                                <div class="rpt_price rpt_price_0">$19</div>
                                <div class="rpt_description rpt_description_0 rpt_desc">Per year. Renewals for 25% off</div>
                                <div style="clear:both;"></div>
                                <div class="rpt_features rpt_features_0">
                                    <div class="rpt_feature rpt_feature_0-0"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Use WP Sticky Sidebar on 1 domain</span>1 website<span class="rpt_tooltip_plus" > +</span></a></div>
                                    <div class="rpt_feature rpt_feature_0-2"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>You can disable the sticky effect on desktop or mobile</span>Devices<span class="rpt_tooltip_plus" > +</span></a></div>
                                    <div class="rpt_feature rpt_feature_0-3"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Add CSS of your own to the sticky sidebar</span>CSS style<span class="rpt_tooltip_plus" > +</span></a></div>
                                    <div class="rpt_feature rpt_feature_0-4"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Exclude pages you don't want to have sticky sidebar</span>Page targeting<span class="rpt_tooltip_plus" > +</span></a></div>
                                    <div class="rpt_feature rpt_feature_0-9">
                                        <select data-key="0" class="multiple-options">
                                            <option data-header="Renewals for 25% off" data-price="19" value="<?php echo esc_url($pro_url."1") ?>">
                                                <?php esc_html_e("Updates & support for 1 year") ?>
                                            </option>
                                            <option data-header="For 2 years" data-price="29" value="<?php echo esc_url($pro_url."13") ?>">
                                                <?php esc_html_e("Updates & support for 2 years") ?>
                                            </option>
                                            <option data-header="For lifetime" data-price="59" value="<?php echo esc_url($pro_url."10") ?>">
                                                <?php esc_html_e("Updates & support for lifetime") ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                                <a target="_blank" href="https://go.premio.io/?edd_action=add_to_cart&amp;download_id=2525&amp;edd_options[price_id]=1" class="rpt_foot rpt_foot_0">Buy now</a>
                            </div>
                        </div>
                        <div class="rpt_plan  rpt_plan_1 rpt_recommended_plan ">
                            <div style="text-align:left;" class="rpt_title rpt_title_1">Plus<img class="rpt_recommended" src="<?php echo plugins_url("") ?>/mystickysidebar/images/rpt_recommended.png" style="top: 27px;"></div>
                            <div class="rpt_head rpt_head_1">
                                <div class="rpt_recurrence rpt_recurrence_1">For businesses with multiple websites</div>
                                <div class="rpt_price rpt_price_1">$39</div>
                                <div class="rpt_description rpt_description_1 rpt_desc">Per year. Renewals for 25% off</div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="rpt_features rpt_features_1">
                                <div class="rpt_feature rpt_feature_1-0"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Use WP Sticky Sidebar on 5 domains</span>5 websites<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-2"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>You can disable the sticky effect on desktop or mobile</span>Devices<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-3"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Add CSS of your own to the sticky sidebar</span>CSS style<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-4"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Exclude pages you don't want to have sticky sidebar</span>Page targeting<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-9">
                                    <select data-key="0" class="multiple-options">
                                        <option data-header="Renewals for 25% off" data-price="39" value="<?php echo esc_url($pro_url."2") ?>">
                                            <?php esc_html_e("Updates & support for 1 year") ?>
                                        </option>
                                        <option data-header="For 2 years" data-price="59" value="<?php echo esc_url($pro_url."14") ?>">
                                            <?php esc_html_e("Updates & support for 2 years") ?>
                                        </option>
                                        <option data-header="For lifetime" data-price="99" value="<?php echo esc_url($pro_url."11") ?>">
                                            <?php esc_html_e("Updates & support for lifetime") ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <a target="_blank" href="https://go.premio.io/?edd_action=add_to_cart&amp;download_id=2525&amp;edd_options[price_id]=2" class="rpt_foot rpt_foot_1">Buy now</a>
                        </div>
                        <div class="rpt_plan  rpt_plan_2  ">
                            <div style="text-align:left;" class="rpt_title rpt_title_2">Agency</div>
                            <div class="rpt_head rpt_head_2">
                                <div class="rpt_recurrence rpt_recurrence_2">For agencies who manage clients</div>
                                <div class="rpt_price rpt_price_2">$79</div>
                                <div class="rpt_description rpt_description_2 rpt_desc">Per year. Renewals for 25% off</div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="rpt_features rpt_features_2">
                                <div class="rpt_feature rpt_feature_2-0"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Use WP Sticky Sidebar on 50 domains</span>50 websites<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-2"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>You can disable the sticky effect on desktop or mobile</span>Devices<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-3"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Add CSS of your own to the sticky sidebar</span>CSS style<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-4"><a href="javascript:;" class="rpt_tooltip"><span class="intool"><b></b>Exclude pages you don't want to have sticky sidebar</span>Page targeting<span class="rpt_tooltip_plus" > +</span></a></div>
                                <div class="rpt_feature rpt_feature_0-9">
                                    <select data-key="0" class="multiple-options">
                                        <option data-header="Renewals for 25% off" data-price="79" value="<?php echo esc_url($pro_url."3") ?>">
                                            <?php esc_html_e("Updates & support for 1 year") ?>
                                        </option>
                                        <option data-header="For 2 years" data-price="125" value="<?php echo esc_url($pro_url."15") ?>">
                                            <?php esc_html_e("Updates & support for 2 years") ?>
                                        </option>
                                        <option data-header="For lifetime" data-price="199" value="<?php echo esc_url($pro_url."12") ?>">
                                            <?php esc_html_e("Updates & support for lifetime") ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <a target="_blank" href="https://go.premio.io/?edd_action=add_to_cart&amp;download_id=2525&amp;edd_options[price_id]=3" class="rpt_foot rpt_foot_2">Buy now</a>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                    <div class="client-testimonial">
                        <p class="text-center"><span class="dashicons dashicons-yes"></span> 30 days money back guaranteed</p>
                        <p class="text-center"><span class="dashicons dashicons-yes"></span> The plugin will always keep working even if you don't renew your license</p>
                        <div class="payment">
                            <img src="<?php echo esc_url(STICKY_SIDEBAR_URL."images/payment.png") ?>" alt="Payment" class="payment-img" />
                        </div>
                        <div class="easy-modal__bottom">
                            <img class="user-photo" src="<?php echo esc_url(STICKY_SIDEBAR_URL."images/client-image.jpeg") ?>">
                            <div class="easy-modal__bottom-p">
                                <svg width="47" height="31" viewBox="0 0 47 31" fill="none" xmlns="http://www.w3.org/2000/svg" class="quote">
                                    <path d="M2.61501 31C1.8523 31 1.19854 30.7281 0.653751 30.1842C0.217918 29.6404 0 28.9877 0 28.2263C0 27.5737 0.163438 26.7035 0.490314 25.6158L8.98907 3.75263C9.53386 2.44736 10.1331 1.5228 10.7869 0.978942C11.4406 0.326313 12.4213 0 13.7288 0H18.7953C19.667 0 20.3207 0.326313 20.7566 0.978942C21.3014 1.63158 21.4648 2.44737 21.2469 3.42632L17.6513 26.2684C17.3244 29.4228 15.799 31 13.075 31H2.61501ZM28.2747 31C27.512 31 26.8583 30.7281 26.3135 30.1842C25.8776 29.6404 25.6597 28.9877 25.6597 28.2263C25.6597 27.5737 25.8232 26.7035 26.15 25.6158L34.6488 3.75263C35.1936 2.44736 35.7929 1.5228 36.4466 0.978942C37.1003 0.326313 38.081 0 39.3885 0H44.455C45.3267 0 45.9805 0.326313 46.4163 0.978942C46.9611 1.63158 47.1245 2.44737 46.9066 3.42632L43.311 26.2684C42.9841 29.4228 41.4587 31 38.7347 31H28.2747Z" fill="#A886CD" fill-opacity="0.1"></path>
                                </svg>
                                I was using my default theme’s sticky sidebar, but it was not working as I want on my blog, then I got this plugin and it worked perfectlly.
                                <p>
                                    <span class="user-name">Divesh Diggiwal</span>,
                                    <span class="user-role">WebTechPreneur</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }

	public function create_admin_page()
	{

		if (isset($_POST['mystickyside_option_name']) && !empty($_POST['mystickyside_option_name']) && isset($_POST['nonce'])) {
			if(!empty($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], 'mysticky_sidebar_update_options_nonce')) {
				$post = $_POST['mystickyside_option_name'];
				$data_array = array();
				if(isset($post['mystickyside_disable_at_front_home'])) {
					$data_array['mystickyside_disable_at_front_home'] = self::sanitize_options($post['mystickyside_disable_at_front_home']);
				}
				if(isset($post['mystickyside_class_selector'])) {
					$data_array['mystickyside_class_selector'] = self::sanitize_options($post['mystickyside_class_selector']);
				}
				if(isset($post['mystickyside_class_content_selector'])) {
					$data_array['mystickyside_class_content_selector'] = self::sanitize_options($post['mystickyside_class_content_selector']);
				}
				if(isset($post['device_desktop'])) {
					$data_array['device_desktop'] = self::sanitize_options($post['device_desktop']);
				}
				if(isset($post['device_mobile'])) {
					$data_array['device_mobile'] = self::sanitize_options($post['device_mobile']);
				}
				if(isset($post['mystickyside_margin_top'])) {
					$data_array['mystickyside_margin_top'] = self::sanitize_options($post['mystickyside_margin_top'], "int");
				}
				if(isset($post['mystickyside_min_width'])) {
					$data_array['mystickyside_min_width'] = self::sanitize_options($post['mystickyside_min_width'], "int");
				}
				if(isset($post['mystickyside_margin_bot'])) {
					$data_array['mystickyside_margin_bot'] = self::sanitize_options($post['mystickyside_margin_bot'], "int");
				}
				if(isset($post['mystickyside_update_sidebar_height'])) {
					$data_array['mystickyside_update_sidebar_height'] = self::sanitize_options($post['mystickyside_update_sidebar_height']);
				}
				if(isset($post['mystickyside_disable_at_page'])) {
					$data_array['mystickyside_disable_at_page'] = self::sanitize_options($post['mystickyside_disable_at_page']);
				}
				if(isset($post['mystickyside_disable_at_blog'])) {
					$data_array['mystickyside_disable_at_blog'] = self::sanitize_options($post['mystickyside_disable_at_blog']);
				}
				if(isset($post['mystickyside_disable_at_tag'])) {
					$data_array['mystickyside_disable_at_tag'] = self::sanitize_options($post['mystickyside_disable_at_tag']);
				}
				if(isset($post['mystickyside_disable_at_category'])) {
					$data_array['mystickyside_disable_at_category'] = self::sanitize_options($post['mystickyside_disable_at_category']);
				}
				if(isset($post['mystickyside_disable_at_single'])) {
					$data_array['mystickyside_disable_at_single'] = self::sanitize_options($post['mystickyside_disable_at_single']);
				}
				if(isset($post['mystickyside_disable_at_archive'])) {
					$data_array['mystickyside_disable_at_archive'] = self::sanitize_options($post['mystickyside_disable_at_archive']);
				}
				if(isset($post['mystickyside_enable_at_pages'])) {
					$data_array['mystickyside_enable_at_pages'] = self::sanitize_options($post['mystickyside_enable_at_pages']);
				}
				if(isset($post['mystickyside_enable_at_posts'])) {
					$data_array['mystickyside_enable_at_posts'] = self::sanitize_options($post['mystickyside_enable_at_posts']);
				}
				if(isset($post['mystickyside_disable_at_search'])) {
					$data_array['mystickyside_disable_at_search'] = self::sanitize_options($post['mystickyside_disable_at_search']);
				}
				if(isset($post['myfixed_cssstyle'])) {
					$data_array['myfixed_cssstyle'] = self::sanitize_options($post['myfixed_cssstyle']);
				}
				if(!empty($data_array)) {
					update_option('mystickyside_option_name', $data_array);
				}
				echo '<div class="updated settings-error notice is-dismissible "><p><strong>' . esc_html__('Settings saved.','mystickymenu'). '</p></strong></div>';
			} else {
				echo '<div class="error settings-error notice is-dismissible "><p><strong>' . esc_html__('Unable to complete your request','mystickymenu'). '</p></strong></div>';
			}
		}
		$mystickyside_option = get_option( 'mystickyside_option_name');


		$is_old = get_option("has_sticky_sidebar_old_version");
		$is_old = ($is_old == "no")?false:true;

		?>

		<style>
			div#wpcontent {
				background: rgba(101,114,219,1);
				background: -moz-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
				background: -webkit-gradient(left top, right bottom, color-stop(0%, rgba(101,114,219,1)), color-stop(67%, rgba(238,134,198,1)), color-stop(100%, rgba(238,134,198,1)));
				background: -webkit-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
				background: -o-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
				background: -ms-linear-gradient(-45deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
				background: linear-gradient(135deg, rgba(101,114,219,1) 0%, rgba(238,134,198,1) 67%, rgba(238,134,198,1) 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6572db', endColorstr='#ee86c6', GradientType=1 );
			}
		</style>
		<script>
			jQuery(document).ready(function(){
				jQuery(".sticky-sidebar-menu ul li a").on( 'click', function(e){
					e.preventDefault();
					if(!jQuery(this).hasClass("active")) {
						jQuery(".sticky-sidebar-menu ul li a").removeClass("active");
						jQuery(this).addClass("active");
						thisHref = jQuery(this).attr("href");
						jQuery(".sticky-sidebar-content").hide();
						jQuery(thisHref).show();
					}
				});
				jQuery(".sticky-sidebar-upgrade-now").on( 'click', function(e){
					e.preventDefault();
					jQuery(".sticky-sidebar-menu ul li a:last").trigger("click");
				});
                jQuery(".multiple-options").on( 'change', function(){
                    thisValue = jQuery(this).val();
                    jQuery(this).closest(".rpt_plan").find("a.rpt_foot").attr("href", thisValue);
                    thisPrice = jQuery(this).find("option:selected").attr("data-price");
                    jQuery(this).closest(".rpt_plan").find(".rpt_price").text("$"+thisPrice);
                    priceText = jQuery(this).find("option:selected").attr("data-header");
                    jQuery(this).closest(".rpt_plan").find(".rpt_desc").text(priceText);
                });
			});
		</script>
		<div id="mystickysidebar" class="mystickysidebar wrap">
			<div style="display: block"  id="sticky-sidebar-settings" class="sticky-sidebar-content">
				<div class="mystickymenu-heading">
					<div class="myStickymenu-header-title">
						<h3><?php esc_attr_e('WP Sticky Sidebar Settings', 'mystickysidebar'); ?></h3>
					</div>
					<p><?php esc_attr_e("Add floating sticky sidebar to any WordPress theme.", 'mystickysidebar'); ?></p>
				</div>
				<form class="mystickysidebar-form" method="post" action="#">
					<div class="mystickysidebar-content-section">
						<table>
						<tr>
							<td width="50%">
								<label class="mysticky_title"><?php esc_attr_e("Sticky Class", 'mystickysidebar')?></label>
								<br /><br />
								<input type="text" size="26" id="mystickyside_class_selector" name="mystickyside_option_name[mystickyside_class_selector]" value="<?php echo isset($mystickyside_option['mystickyside_class_selector'])?$mystickyside_option['mystickyside_class_selector']:"" ;?>" />
								<p class="description"><?php esc_attr_e("Sidebar element CSS class or id", 'mystickysidebar');?></p>
								<br /><br />
								<input type="text" size="26" id="mystickyside_class_content_selector" name="mystickyside_option_name[mystickyside_class_content_selector]" value="<?php echo isset($mystickyside_option['mystickyside_class_content_selector'])?$mystickyside_option['mystickyside_class_content_selector']:""; ?>" />
								<p class="description"><?php esc_attr_e("Container element class or id. It must be element that contains both sidebar and content. If left blank script will try to guess. Usually it's #main or #main-content", 'mystickysidebar'); ?></p>
							</td>

							<td>
								<div class="mysticky_device_upgrade">
								<label class="mysticky_title myssticky-remove-hand"><?php esc_attr_e("Devices", 'mystickysidebar')?></label>
								<span class="mystickysidebar-upgrade sticky-sidebar-upgrade-now"><a href="#" target="_blank"><?php _e( 'Upgrade Now', 'mystickysidebar' );?></a></span>
								<ul class="mystickysidebar-input-multicheckbox">
									<li>
									<label>
										<input id="disable_desktop" name="mystickyside_option_name[device_desktop]" type="checkbox"  checked  disabled />
										<?php _e( 'Desktop', 'mystickysidebar' );?>
									<label>
									</li>
									<li>
									<label>
										<input id="disable_mobile" name="mystickyside_option_name[device_mobile]" type="checkbox" checked disabled />
										<?php _e( 'Mobile', 'mystickysidebar' );?>
									<label>
									</li>
								</ul>
								</div>

							</td>
						</tr>
					</table>
				</div>

				<div class="mystickysidebar-content-section">
					<h3><?php esc_html_e( 'Settings', 'mystickysidebar' );?></h3>
					<table class="form-table">
						<tr>
							<td width="25%">
								<label for="mystickyside_margin_top" class="mysticky_title"><?php esc_attr_e("Additional top margin", 'mystickysidebar')?></label>
							</td>
							<td width="25%">
								<div class="px-wrap">
									<input type="number" class="small-text" min="0" step="1" id="mystickyside_margin_top" name="mystickyside_option_name[mystickyside_margin_top]" value="<?php echo isset($mystickyside_option['mystickyside_margin_top'])?$mystickyside_option['mystickyside_margin_top']:""; ?>" placeholder="90" />
									<span class="input-px">PX</span>
								</div>
							</td>
							<td>
								<label for="mystickyside_min_width" class="mysticky_title"><?php esc_attr_e("Disable in small screens", 'mystickysidebar')?></label>
								<p class="description"><?php _e( 'Disable if screen width is smaller than', 'mystickysidebar' );?></p>

							</td>
							<td>
								<div class="px-wrap">
									<input type="number" class="small-text" min="0" step="1" id="mystickyside_min_width" name="mystickyside_option_name[mystickyside_min_width]" value="<?php echo isset($mystickyside_option['mystickyside_min_width'])?$mystickyside_option['mystickyside_min_width']:"" ;?>" placeholder="795" />
									<span class="input-px">PX</span>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<label for="mystickyside_margin_bot" class="mysticky_title"><?php esc_attr_e("Additional bottom margin", 'mystickysidebar')?></label>
							</td>
							<td>
								<div class="px-wrap">
									<input type="number" class="small-text" min="0" step="1" id="mystickyside_margin_bot" name="mystickyside_option_name[mystickyside_margin_bot]" value="<?php echo isset($mystickyside_option['mystickyside_margin_bot'])?$mystickyside_option['mystickyside_margin_bot']:"" ;?>" placeholder="0" />
									<span class="input-px">PX</span>
								</div>
							</td>
							<td>
								<label for="mystickyside_update_sidebar_height" class="mysticky_title"><?php esc_attr_e("Update sidebar height", 'mystickysidebar')?></label>
								<p class="description"><?php _e( 'Troubleshooting option, try this if your sidebar loses its background color.', 'mystickysidebar' );?></p>
							</td>
							<td>
								<?php $height = isset($mystickyside_option['mystickyside_update_sidebar_height'])?$mystickyside_option['mystickyside_update_sidebar_height']:"" ?>
								<select id="mystickyside_update_sidebar_height" class="mystickysidebar-select" name="mystickyside_option_name[mystickyside_update_sidebar_height]" >
									<option name="true" value="true" <?php selected($height , 'true');?>>true</option>
									<option name="false" value="false" <?php selected($height , 'false');?>>false</option>
								</select>
							</td>
						</tr>

					</table>
				</div>

				<div class="mystickysidebar-content-section <?php echo !($is_old)?"mystickysidebar-content-upgrade":"" ?>">
					<div class="mystickysidebar-content-option">
						<span class="mystickysidebar-upgrade sticky-sidebar-upgrade-now"><a href="#" target="_blank"><?php _e( 'Upgrade Now', 'mystickysidebar' );?></a></span>
					</div>

					<div class="mystickysidebar-content-option">
						<label class="mysticky_title css-style-title"><?php esc_attr_e("CSS style", 'mystickysidebar'); ?></label>
						<label class="mysticky_text"><?php _e( 'Add/edit CSS style. Leave it blank for default style.', 'mystickysidebar');?></label>
						<div class="mystickysidebar-input-section">
							<textarea placeholder="<?php esc_attr_e('/* Custom CSS will go here */', 'mystickysidebar') ?> " type="text" rows="4" cols="60" id="myfixed_cssstyle" name="mystickyside_option_name[myfixed_cssstyle]" disabled><?php echo isset($mystickyside_option['myfixed_cssstyle'])?trim($mystickyside_option['myfixed_cssstyle']):""; ?></textarea>
						</div>
					</div>

					<div class="mystickysidebar-content-option">
						<label class="mysticky_title"><?php esc_attr_e("Disable at", 'mystickysidebar'); ?></label>
						<?php if(!$is_old) { ?><span class="mystickysidebar-upgrade sticky-sidebar-upgrade-now"><a href="#" target="_blank"><?php _e( 'Upgrade Now', 'mystickysidebar' );?></a></span><?php } ?>
						<div class="mystickysidebar-input-section">
							<ul class="mystickysidebar-input-multicheckbox">
								<li>
									<label>
										<input id="mystickyside_disable_at_front_home" name="mystickyside_option_name[mystickyside_disable_at_front_home]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_front_home'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Front Page', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_blog" name="mystickyside_option_name[mystickyside_disable_at_blog]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_blog'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Blog Page', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_page" name="mystickyside_option_name[mystickyside_disable_at_page]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_page'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Pages', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_tag" name="mystickyside_option_name[mystickyside_disable_at_tag]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_tag'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Tags', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_category" name="mystickyside_option_name[mystickyside_disable_at_category]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_category'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Categories', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_single" name="mystickyside_option_name[mystickyside_disable_at_single]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_single'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Posts', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_archive" name="mystickyside_option_name[mystickyside_disable_at_archive]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_archive'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Archives', 'mystickysidebar' );?></span>
									</label>
								</li>
								<li>
									<label>
										<input id="mystickyside_disable_at_search" name="mystickyside_option_name[mystickyside_disable_at_search]" type="checkbox" <?php checked( isset( $mystickyside_option['mystickyside_disable_at_search'] ), true )?> <?php echo !$is_old?"disabled":"" ?> >
										<span><?php esc_attr_e('Search', 'mystickysidebar' );?></span>
									</label>
								</li>
							</ul>
							<?php

							if  ( isset($mystickyside_option['mystickyside_disable_at_page'])  )  {

								echo '<div class="mystickysidebar-input-section">';
								_e('<span class="description"><strong>Except for this page:</strong> Shopping Cart, Checkout: </span>', 'mystickysidebar');

								printf(
									'<input type="text" size="26" class="mystickyside_normal_text" id="mystickyside_enable_at_pages" name="mystickyside_option_name[mystickyside_enable_at_pages]" value="%s" '.(!$is_old?"disabled":"").' /> ',
									isset( $mystickyside_option['mystickyside_enable_at_pages'] ) ? esc_attr( $mystickyside_option['mystickyside_enable_at_pages']) : ''
								);

								_e('<span class="description">Comma separated list of pages to enable. It should be page name, id or slug. Example: about-us, 1134, Contact Us. Leave blank if you realy want to disable sticky sidebar for all pages.</span>', 'mystickysidebar');
								echo '</div>';

							}

							if  ( isset($mystickyside_option['mystickyside_disable_at_single']) )  {

								echo '<div class="mystickysidebar-input-section">';
								_e('<span class="description"><strong>Except for this posts:</strong> myStickySidebar Demo: </span>', 'mystickysidebar');

								printf(
									'<input type="text" size="26" class="mystickyside_normal_text" id="mystickyside_enable_at_posts" name="mystickyside_option_name[mystickyside_enable_at_posts]" value="%s" '.(!$is_old?"disabled":"").' /> ',
									isset( $mystickyside_option['mystickyside_enable_at_posts'] ) ? esc_attr( $mystickyside_option['mystickyside_enable_at_posts']) : ''
								);
								_e('<span class="description">Comma separated list of posts to enable. It should be post name, id or slug. Example: about-us, 1134, Contact Us. Leave blank if you realy want to disable sticky sidebar for all posts.</span>', 'mystickysidebar');
								echo '</div>';
							}
							?>
							<p></p>
						</div>
					</div>
				</div>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save', 'mystickysidebar');?>">
					<input type="hidden" name="nonce" value="<?php echo wp_create_nonce("mysticky_sidebar_update_options_nonce"); ?>">
				</p>
				</form>
				<form class="mysticky-hideformreset" method="post" action="">
					<input name="reset" class="button button-secondary confirm" type="submit" value="<?php esc_attr_e('Reset', 'mystickysidebar');?>" >
					<input type="hidden" name="action" value="reset_mystickysidebar" />
					<input type="hidden" name="nonce" value="<?php echo wp_create_nonce("mysticky_sidebar_reset_options_nonce"); ?>">
				</form>
				<p class="mystickysidebar-review"><a href="https://wordpress.org/support/plugin/mystickysidebar/reviews/" target="_blank"><?php esc_attr_e('Leave a review','mystickysidebar'); ?></a></p>
			</div>
		</div>
		<?php
	}

	public function mystickyside_default_options() {

		global $options;
		$default = array(
				'mystickyside_class_selector' 	=> '#secondary',
				'mystickyside_class_content_selector' => '',
				'mystickyside_margin_top' 		=> '90',
				'mystickyside_margin_bot' 		=> '0',
				'mystickyside_min_width' 		=> '795',
				'mystickyside_update_sidebar_height' => 'false',
				'mystickyside_enable_at_pages' 	=> false,
				'mystickyside_enable_at_posts' 	=> false,
				'device_desktop'				=> true,
				'device_mobile' 				=> true,
			);

		if ( get_option('mystickyside_option_name') == false ) {
			$status = get_option("sticky_sidebar_status");
			if($status == false) {
				update_option("sticky_sidebar_status", "done");
				update_option("has_sticky_sidebar_old_version", "no");
			}
			update_option( 'mystickyside_option_name', $default );
		} else {
			$status = get_option("sticky_sidebar_status");
			if($status == false) {
				update_option("sticky_sidebar_status", "done");
				update_option("has_sticky_sidebar_old_version", "yes");
			}
		}

		if ( get_option('mystickyside_option_name') == false && current_user_can( 'manage_options' )) {
			update_option( 'mystickyside_option_name', $default );
		}

		if(isset($_POST['reset_my_sidebar_options']) && current_user_can( 'manage_options' )) {
			if(isset($_REQUEST['nonce']) && !empty($_REQUEST['nonce'])  && wp_verify_nonce($_REQUEST['nonce'], 'mysticky_option_backend_reset_nonce')) {
				update_option('mystickyside_option_name', $default);
			}
		}
	}
}

//FRONTEND

class MyStickysidebarFrontend
{

	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'mystickysidebar_disable_at' ), 99 );
		add_action( 'wp_head', array( $this, 'mystickysidebar_build_stylesheet_content' ), 99 );
	}

	public function mystickysidebar_build_stylesheet_content() {
		$mystickyside_options = get_option( 'mystickyside_option_name' );
		if  ( isset($mystickyside_options ['myfixed_cssstyle']) && $mystickyside_options ['myfixed_cssstyle'] !=  "" ) {

			echo '<style id="mystickyside_cssstyle" type="text/css">';
			echo esc_attr($mystickyside_options ['myfixed_cssstyle']);
			echo '</style>';
		}
	}

	public function mystickysidebar_script() {

		$mystickyside_options = get_option( 'mystickyside_option_name' );

		if ( is_admin_bar_showing() ) {
			$aditionalmargintop = $mystickyside_options['mystickyside_margin_top'] + 32;
		} else {
			$aditionalmargintop = $mystickyside_options['mystickyside_margin_top'];
		}

		wp_register_script('detectmobilebrowser', plugins_url( 'js/detectmobilebrowser.js', __FILE__ ), array('jquery'), '1.2.3', true);
		wp_enqueue_script( 'detectmobilebrowser' );

		wp_register_script('mystickysidebar', plugins_url( 'js/theia-sticky-sidebar.js', __FILE__ ), array('jquery'), '1.2.3', true);
		wp_enqueue_script( 'mystickysidebar' );
		
		$mystickyside_translation_array = array(
			'mystickyside_string' => $mystickyside_options['mystickyside_class_selector'] ,
			'mystickyside_content_string' => $mystickyside_options['mystickyside_class_content_selector'] ,
			'mystickyside_margin_top_string' => $aditionalmargintop,
			'mystickyside_margin_bot_string' => $mystickyside_options['mystickyside_margin_bot'],
			'mystickyside_update_sidebar_height_string' => $mystickyside_options['mystickyside_update_sidebar_height'],
			'mystickyside_min_width_string' => $mystickyside_options['mystickyside_min_width'],
			'device_desktop'				=> ( !isset($mystickyside_options['device_desktop']) ) ? true : $mystickyside_options['device_desktop'],
			'device_mobile' 				=> ( !isset($mystickyside_options['device_mobile']) ) ? true : $mystickyside_options['device_mobile'],

		);
		wp_localize_script( 'mystickysidebar', 'mystickyside_name', $mystickyside_translation_array );

	}
	public function mystickysidebar_disable_at() {

		$mystickyside_options = get_option( 'mystickyside_option_name' );
		$mystickyside_disable_at_front_home = isset($mystickyside_options['mystickyside_disable_at_front_home']);
		$mystickyside_disable_at_blog = isset($mystickyside_options['mystickyside_disable_at_blog']);
		$mystickyside_disable_at_page = isset($mystickyside_options['mystickyside_disable_at_page']);
		$mystickyside_disable_at_tag = isset($mystickyside_options['mystickyside_disable_at_tag']);
		$mystickyside_disable_at_category = isset($mystickyside_options['mystickyside_disable_at_category']);
		$mystickyside_disable_at_single = isset($mystickyside_options['mystickyside_disable_at_single']);
		$mystickyside_disable_at_archive = isset($mystickyside_options['mystickyside_disable_at_archive']);
		$mystickyside_disable_at_search = isset($mystickyside_options['mystickyside_disable_at_search']);
		$mystickyside_enable_at_pages = isset($mystickyside_options['mystickyside_enable_at_pages']) ? $mystickyside_options['mystickyside_enable_at_pages'] : '';
		$mystickyside_enable_at_posts = isset($mystickyside_options['mystickyside_enable_at_posts']) ? $mystickyside_options['mystickyside_enable_at_posts'] : '';
		//$mystickyside_enable_at_pages_exp = explode( ',', $mystickyside_enable_at_pages );
		// Trim input to ignore empty spaces
		$mystickyside_enable_at_pages_exp = array_map('trim', explode(',', $mystickyside_enable_at_pages));
		$mystickyside_enable_at_posts_exp = array_map('trim', explode(',', $mystickyside_enable_at_posts));


		if ( is_front_page() && is_home() ) { /* Default homepage */
			
			if ( $mystickyside_disable_at_front_home == false ) {
				$this->mystickysidebar_script();
			}
		} elseif ( is_front_page()){ /* Static homepage */
			
			if ( $mystickyside_disable_at_front_home == false ) {
				$this->mystickysidebar_script();
			}
		} elseif ( is_home()){ /* Blog page */
			
			if ( $mystickyside_disable_at_blog == false ) {
				$this->mystickysidebar_script();
			}
		} elseif ( is_page() ){ /* Single page */			
			if ( $mystickyside_disable_at_page == false ) {
				$this->mystickysidebar_script();
			}
			if ( is_page( $mystickyside_enable_at_pages_exp  )  ){
				$this->mystickysidebar_script();
			}
		} elseif ( is_tag()){ /* Tag page */
			
			if ( $mystickyside_disable_at_tag == false ) {
				$this->mystickysidebar_script();
			}

		} elseif ( is_category()){ /* Category page */			
			if ( $mystickyside_disable_at_category == false ) {
				$this->mystickysidebar_script();
			}
		} elseif ( is_single()){ /* Single post */			
			if ( $mystickyside_disable_at_single == false ) {
				$this->mystickysidebar_script();
			}

			if ( is_single( $mystickyside_enable_at_posts_exp  )  ){
				$this->mystickysidebar_script();
			}

		} elseif ( is_archive()){ /* Archive */			
			if ( $mystickyside_disable_at_archive == false ) {
				$this->mystickysidebar_script();
			}

		} elseif ( is_search()){ /* Search */			
			if ( $mystickyside_disable_at_search == false ) {
				$this->mystickysidebar_script();
			}
		}

	}


}

if( is_admin() ) {
	new MyStickysidebarBackend();
	include_once "class-review-box.php";
	include_once "class-affiliate.php";
} else {
	new MyStickysidebarFrontend();
}


if(!function_exists('mystickysidebar_change_menu_text')) {
    function mystickysidebar_change_menu_text()
    {
        global $submenu;
        $subMenuKey = 'my-stickysidebar-settings';
        if (isset($submenu[$subMenuKey])) {
            end($submenu[$subMenuKey]);         // move the internal pointer to the end of the array
            $key = key($submenu[$subMenuKey]);
            if (isset($submenu[$subMenuKey][$key][0])) {
                $submenu[$subMenuKey][$key][0] = '<span><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.0518 4.01946C12.9266 3.91499 12.7747 3.84781 12.6132 3.82557C12.4517 3.80333 12.2872 3.82693 12.1385 3.89367L9.3713 5.12414L7.76349 2.22571C7.68664 2.09039 7.5753 1.97785 7.44081 1.89956C7.30632 1.82127 7.15348 1.78003 6.99786 1.78003C6.84224 1.78003 6.6894 1.82127 6.55491 1.89956C6.42042 1.97785 6.30908 2.09039 6.23224 2.22571L4.62442 5.12414L1.85724 3.89367C1.70822 3.82703 1.54352 3.8034 1.38178 3.82545C1.22003 3.84751 1.06768 3.91437 0.941941 4.01849C0.816207 4.1226 0.722106 4.25982 0.670275 4.41461C0.618444 4.56941 0.610951 4.73562 0.648642 4.89446L2.0377 10.8171C2.06427 10.9318 2.11383 11.0399 2.18339 11.1348C2.25295 11.2297 2.34107 11.3096 2.44239 11.3695C2.57957 11.4516 2.73642 11.495 2.8963 11.4952C2.97402 11.4951 3.05133 11.484 3.12599 11.4624C5.65792 10.7624 8.33233 10.7624 10.8643 11.4624C11.0955 11.5232 11.3413 11.4898 11.5479 11.3695C11.6498 11.3103 11.7384 11.2307 11.8081 11.1357C11.8777 11.0406 11.9269 10.9321 11.9525 10.8171L13.3471 4.89446C13.3843 4.73558 13.3764 4.56945 13.3243 4.41482C13.2721 4.2602 13.1777 4.12326 13.0518 4.01946V4.01946Z" fill="white"/>
</svg></span> ' . esc_html__('Upgrade to Pro', 'chaty');
            }
        }
    }

    add_action('admin_init', 'mystickysidebar_change_menu_text');
}

if(!function_exists('mystickysidebar_admin_footer_style')) {
    function mystickysidebar_admin_footer_style() {
        ?>
        <style>
            #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child {
                padding: 5px 10px;
            }
            #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child a {
                display: flex;
                background-color: #B78DEB;
                border-radius: 6px;
                font-size: 12px;
                gap: 4px;
                padding: 4px 8px;
                color: #ffffff;
                align-items: center;
                transition: all 0.2s linear;
                font-weight: normal;
                box-shadow: 0px 6px 8px 0px #B78DEB3D;
                justify-content: center;
            }
            #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child a:hover, #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child a.current {
                box-shadow: 0px 6px 8px 0px #B78DEB3D;
                color: #ffffff;
                background-color: #9565d0;
                font-weight: normal;
            }
            #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child a span {
                flex: 0 0 16px;
                height: 16px;
                background-color: #c5a4ef;
                border-radius: 4px;
                padding: 2px;
                display: inline-flex;
                transition: all 0.2s linear;
            }
            #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child a:hover span {
                background-color: #B78DEB;
            }
            #adminmenu .toplevel_page_my-stickysidebar-settings > ul > li:last-child a span svg {
                width: 100%;
                height: 100%;
            }
        </style>
        <?php
    }
    add_action('admin_head', 'mystickysidebar_admin_footer_style');
}