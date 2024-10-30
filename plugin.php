<?php 
    /*
    Plugin Name: Flash Games
    Plugin URI: http://www.arcadegamescorner.com/1150-free-games-for-your-website/
    Description: Engage and attract visitors by displaying up to 1,150 flash games to your website using simple shortcodes.
    Author: Ag Corner
    Version: 2.2
    Author URI: http://www.arcadegamescorner.com/1150-free-games-for-your-website/
    */
// Installing Shortcode for the games
include_once(ABSPATH.WPINC.'/feed.php');
if(!class_exists('CGWP_Main')) {
    class CGWP_Main
    {
        // variable for games
        public $games;
        public $source_url;
        public $cats;

        public function __construct()
        {
            add_action('admin_init', array($this, 'get_all_games'));
            add_action('admin_init', array($this, 'enqueue_scripts'));
            add_action('admin_menu', array($this, 'card_games_plugin_menu'));
            add_action('wp_dashboard_setup', array($this, 'cgwp_add_admin_widget'));
            add_action('admin_notices', array($this, 'show_admin_notice'));
            add_action('wp_ajax_cgwp_search_game', array($this, 'cgwp_search_game'));
            add_action('wp_ajax_cgwp_sort_game', array($this, 'cgwp_sort_game'));
            add_action('wp_ajax_cgwp_set_support_time', array($this, 'cgwp_set_support_time'));
            add_action('wp_ajax_cgwp_set_support_link', array($this, 'cgwp_set_support_link'));
            add_action('wp_ajax_cgwp_set_support_link_check' , array($this , 'cgwp_set_support_link_check'));
            add_shortcode('cardgame', array($this, 'add_shortcodes'));
            $this->source_url = 'http://www.arcadegamescorner.com/game-api.php';
        }

        /** get all games from arcadegamescorner.com */
        public function get_all_games()
        {
            $url = $this->source_url;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $results = curl_exec($curl);
            curl_close($curl);
            $games = explode(";;;", $results);
            update_option('cgwp_games_title', $games[0]);
            $this->games = explode("::", $games[0]);
            update_option('cgwp_games_cat', $games[1]);
            $this->cats = explode("||", $games[1]);
        }

        /** enqueue scirpts and styles */
        public function enqueue_scripts()
        {
            wp_register_script('card_admin_js', plugins_url('js/card-admin.js', __FILE__));
            wp_register_style('card_admin_css', plugins_url('css/card-admin.css', __FILE__));
            wp_enqueue_style('card_admin_css');
            wp_enqueue_script('card_admin_js');
            wp_localize_script('card_admin_js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
        }

        /** add menu in the setting page */
        public function card_games_plugin_menu()
        {
            add_menu_page('Flash Games Option', 'Flash Games', 'manage_options', 'card-games-plugin-menu', array($this, 'cgwp_plugin_options'));
        }

        public function cgwp_plugin_options()
        {
            require_once('options/settings.php');
        }

        /** add widget in the admin dashboard */
        public function cgwp_add_admin_widget()
        {
            wp_add_dashboard_widget('cgwp_dashboard_widget', 'From the Arcadegamescorner.com', array(&$this, 'cgwp_dashboard_widget_function'));

            // Globalize the metaboxes array, this holds all the widgets for wp-admin
            global $wp_meta_boxes;

            // Get the regular dashboard widgets array
            // (which has our new widget already but at the end)
            $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

            // Backup and delete our new dashboard widget from the end of the array
            $cgwp_widget_backup = array('cgwp_dashboard_widget' => $normal_dashboard['cgwp_dashboard_widget']);
            unset($normal_dashboard['cgwp_dashboard_widget']);

            // Merge the two arrays together so our widget is at the beginning
            $sorted_dashboard = array_merge($cgwp_widget_backup, $normal_dashboard);

            // Save the sorted array back into the original metaboxes
            $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
        }

        /** add dashboard widget in the wp-dashboad page */
        public function cgwp_dashboard_widget_function()
        {
            $rss = fetch_feed('http://www.arcadegamescorner.com/feed/');
            $html = '<ul class = "arcadegamescorner_blogs">';
            if (!is_wp_error($rss)) {
                $maxitems = $rss->get_item_quantity(10);
                if ($maxitems == 0) {
                    $html .= '<li>There are not blogs yet.</li>';
                } else {
                    $rss_items = $rss->get_items(0, $maxitems);
                    foreach ($rss_items as $item) {
                        $html .= '<li><a href="' . esc_url($item->get_permalink()) . '" title = "Posted ' . $item->get_date() . '" style = "margin-right:20px;">' . esc_html($item->get_title()) . '</a>   ' . $item->get_date("j F Y") . ' </li>';
                    }
                }


            }
            $html .= '</ul>';
            echo $html;
        }

        /** show admin notice */
        public function show_admin_notice()
        {
            ?>
            <style>

                #cgwp-notice-support-view {
                    margin-top: 10px;
                    padding: 10px 10px 10px 10px;
                    border-color: rgba(0, 0, 0, 0.22);
                    border-width: 1px;
                    border-style: solid;
                    border-radius: 2px;
                    margin-left: 10px;
                }

                .cgwp-support-click-common {
                    display: inline;
                    position: relative;
                }

                .grow {
                    display: inline-block;
                    -webkit-transition-duration: 0.2s;
                    transition-duration: 0.2s;
                    -webkit-transition-property: -webkit-transform;
                    transition-property: transform;
                    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
                    -webkit-transform: translateZ(0);
                    -ms-transform: translateZ(0);
                    transform: translateZ(0);
                    box-shadow: 0 0 1px rgba(0, 0, 0, 0);
                }

                .grow:hover {
                    -webkit-transform: scale(1.3);
                    -ms-transform: scale(1.3);
                    transform: scale(1.3);
                }
            </style>
            <script>
                jQuery(document).ready(function () {
                    jQuery('#cgwp-notice-support-close').click(function (event) {
                        var data = {
                            action: 'cgwp_set_support_time',
                            set_time_check: '1'
                        };
                        jQuery.post(ajax_object.ajax_url, data, function (respond) {
                            jQuery("#cgwp-notice-support-view").hide();
                        });
                        return false;
                    });
                    jQuery('#cgwp-notice-support-click').click(function (event) {
                        var data = {
                            action: 'cgwp_set_support_link'
                        };
                        jQuery.post(ajax_object.ajax_url, data, function (respond) {
                            jQuery("#cgwp_support_title_1").hide();
                            jQuery("#cgwp_support_title_2").show();
                            jQuery("#cgwp_support_title_3").hide();
                        });
                    });
                });
            </script>

            <div class="updated" id="cgwp-notice-support-view" style="<?php
            $settings = get_option('cgwp_global_option');
            if ($settings['cgwp_author_link'] == '0') {
                if ((time() - $settings['cgwp_init_dt']) <= 24 * 60 * 60) {
                    if ($settings['cgwp_set_time_check'] == '0') {
                        echo 'display:block;';
                    } else if ($settings['cgwp_set_time_check'] == '1') {

                        echo 'display:none;';
                    }
                } else {

                    echo 'display: block;';
                    $settings = get_option('cgwp_global_option');
                    $settings['cgwp_set_time_check'] = '0';
                    update_option('cgwp_global_option', $settings);
                }

            } else {

                echo 'display: none;';

            }



            ?>">


                <div class="cgwp-support-click-title cgwp-support-click-common" id="cgwp_support_title_1">Thank you for
                    using

                    <a href="<?php  $url = admin_url();

                    echo $url . 'admin.php?page=card-games-plugin-menu';?>">Flash Games</a>,

                    <a href="#" id="cgwp-notice-support-click"> if you like our plugin please activate the author
                        credits by clicking here!</a>


                </div>

                <div class="cgwp-support-click-title cgwp-support-click-common" id="cgwp_support_title_2"
                     style="display: none;">Thank you for supporting Our plugin.
                </div>

                <div style="float: right;" id="cgwp_support_title_3">

                    <small><a href="#" id="cgwp-notice-support-close"> X</a></small>

                </div>
            </div>
        <?php
        }
        /** activation hook */
        public function cgwp_activate()
        {
            $options['cgwp_init_dt'] = time();
            $options['cgwp_set_time_check'] = '0';
            $options['cgwp_author_link'] = '0';
            update_option('cgwp_global_option' , $options);
        }
        /** deactivation hook : deletion opions related with this plugin */
        public function cgwp_deactivate()
        {
            delete_option('cgwp_games_title');
            delete_option('cgwp_games_cat');
            delete_option('cgwp_global_option');
        }

        public function add_shortcodes($atts)
        {
            $cardgames = get_option('cgwp_games_title');
            $cgwp_settings = get_option('cgwp_global_option');
            $author_link = $cgwp_settings['cgwp_author_link'];
            $index = $atts['id'];
            $title = $atts['title'];
            $games = explode('::', $cardgames);
            $cur_games = explode('||', $games[$index]);
            $iframe_src = $cur_games['2'];
            $href_real_link = 'http://www.arcadegamescorner.com/game/' . $title . '/';
            if($author_link == '1')
            {
                $output = '<div><iframe width="95%" height="600" src="' . $iframe_src . '"></iframe><p><a href="' . $href_real_link . '" title="' . $cur_games['0'] . '">' . $cur_games['0'] . '</a></p></div><br /><br />';
            }
            else
            {
                $output = '<div><iframe width="95%" height="600" src="' . $iframe_src . '"></iframe></div><br /><br />';

            }
            return $output;
        }

        /** function to search and sort game by title and category using ajax */
        public function cgwp_search_game()
        {
            check_ajax_referer('cgwp-search-ajax-nonce', 'security', false);
            $output = '';
            $i = 0;
            $title = $_POST['title'];
            $cat = $_POST['cat'];
            if ($title == '' && $cat == 'all') {
                echo 'all';
                wp_die();
            }
            $cardgames = get_option('cgwp_games_title');
            $games = explode('::', $cardgames);
            foreach ($games as $item) {
                if ($item == '') {
                    $i++;
                    break;
                }
                $game = explode('||', $item);
                if ($cat == 'all') {
                    if (strpos($game[0], $title) !== false) {
                        $image_src = 'http://www.arcadegamescorner.com/img/' . $game['0'] . '.jpg';
                        $output .= '<div class="cgwp_game"><img src="' . $image_src . '"/><p class="cgwp_game_title">title : ' . $game['0'] . '</p>shortcode: <input type="text" readonly value="[cardgame title=' . $game['1'] . ' id=' . $i . ']"></div>';
                    }
                } elseif ($title == '') {
                    if ($game[3] == $cat) {
                        $image_src = 'http://www.arcadegamescorner.com/img/' . $game['0'] . '.jpg';
                        $output .= '<div class="cgwp_game"><img src="' . $image_src . '"/><p class="cgwp_game_title">title : ' . $game['0'] . '</p>shortcode: <input type="text" readonly value="[cardgame title=' . $game['1'] . ' id=' . $i . ']"></div>';
                    }
                } elseif (strpos($game[0], $title) !== false && $game[3] == $cat) {
                    $image_src = 'http://www.arcadegamescorner.com/img/' . $game['0'] . '.jpg';
                    $output .= '<div class="cgwp_game"><img src="' . $image_src . '"/><p class="cgwp_game_title">title : ' . $game['0'] . '</p>shortcode: <input type="text" readonly value="[cardgame title=' . $game['1'] . ' id=' . $i . ']"></div>';
                }
                $i++;
            }
            echo $output;
            wp_die();
        }
        public function cgwp_set_support_time(){
            $settings = get_option('cgwp_global_option');
            $settings['cgwp_init_dt'] = time();
            $settings['cgwp_set_time_check'] = $_POST['set_time_check'];
            update_option('cgwp_global_option' , $settings);
            die();
        }
        public function cgwp_set_support_link()
        {
            $settings = get_option('cgwp_global_option');
            $settings['cgwp_author_link'] = '1';
            update_option('cgwp_global_option' , $settings);
            die();
        }
        public function cgwp_set_support_link_check()
        {
            $settings = get_option('cgwp_global_option');
            if(isset($_POST['author_link']))
            {
                $author_link = $_POST['author_link'];
                if($author_link == '0')
                {
                    $settings['cgwp_init_dt'] = time();
                    $settings['cgwp_set_time_check'] = '1';
                }
                $settings['cgwp_author_link'] = $author_link;
                update_option('cgwp_global_option' , $settings);
                echo 'success';
                wp_die();
            }
        }
    }
}
    $cgwp = new CGWP_Main();
    register_activation_hook( __FILE__, array( $cgwp, 'cgwp_activate' ) );
    register_deactivation_hook(__FILE__ , array($cgwp , 'cgwp_deactivate'));
