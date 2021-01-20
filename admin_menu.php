<?php

require_once("config.php");
require_once("vendor/autoload.php");
require_once("includes/plugin_setting_menu.php");

class FacebookFeedBanner
{
    static function init()
    {
        return new self();
    }

    function __construct()
    {
        if (is_admin() && is_user_logged_in()) {
            // メニュー追加
            add_action('admin_menu', [$this, 'set_plugin_menu']);
        }
    }

    function plugin_settings_menu()
    {
        $menu = new FFD_SETTING_MENU();

        $menu->init();
    }

    function set_plugin_menu()
    {
        add_menu_page(
            'Facebook Feed',
            'Facebook Feed',           /* メニュータイトル */
            'manage_options',         /* 権限 */
            'facebook-feed',    /* ページを開いたときのURL */
            [$this, 'fbf_show_plugin_top'],       /* メニューに紐づく画面を描画するcallback関数 */
            'dashicons-format-gallery', /* アイコン see: https://developer.wordpress.org/resource/dashicons/#awards */
            99                          /* 表示位置のオフセット */
        );

        add_submenu_page(
            'facebook-feed',
            'Facebook Feed',
            'App settings',
            'manage_options',
            'facebook-feed-settings',
            [$this, "plugin_settings_menu"]
        );
    }

    function fbf_show_plugin_top()
    {
        // initial variables
        $fb_login = new FB_LOGIN($_ENV["FB_APP_ID"], $_ENV["FB_APP_SECRET"]);
        $fb_access_token = get_option(PLUGIN_PREFIX . "fb_access_token");
        $fb_user_access_token = get_option(PLUGIN_PREFIX . "fb_user_access_token");
        $fb = $fb_login->init();
        $helper = $fb->getJavaScriptHelper();

        // GET temp access token to check logged in.
        try {
            $tmp_access_token = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        // Logged in
        if ($tmp_access_token) {

            $_SESSION['fb_access_token'] = (string) $tmp_access_token;
            $oAuth2Client = $fb->getOAuth2Client();

            // Exchanges a short-lived access token for a long-lived one
            if ($tmp_access_token && !$fb_user_access_token->isLongLived()) {
                try {
                    $long_term_access_token = $oAuth2Client->getLongLivedAccessToken($tmp_access_token);
                    update_option(PLUGIN_PREFIX . "fb_user_access_token", $long_term_access_token);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
                }
            }

            // GET User Information
            $request = $fb->request('GET', '/me', [], $fb_user_access_token);

            try {
                $response = $fb->getClient()->sendRequest($request);
                $user = $response->getGraphNode();
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
            }
        }

        /**
         * From here, markup starts.
         */

?>
        <?php

        if (!$tmp_access_token) {
        ?>
            <fb:login-button scope="public_profile,email, pages_show_list" onlogin="checkLoginState();">
            </fb:login-button>
        <?php
        } else if ($tmp_access_token) {
            // When logged in, these information are displayed.
            try {
                // Returns a `FacebookFacebookResponse` object
                $response = $fb->get(
                    '/me',
                    $fb_access_token
                );
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
            }
            $page = $response->getGraphNode();

            try {
                $response = $fb->get(
                    '/me',
                    $fb_user_access_token
                );
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
            }


        ?>
            <h1>Basic Information</h1>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="column" colspan="2">
                            <h2>Facebook User information</h2>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">Facebook User ID</th>
                        <td><?= $user['id'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Facebook Name</th>
                        <td><?= $user['name'] ?></td>
                    </tr>
                    <tr>
                        <th scope="column" colspan="2">
                            <h2>Facebook Page information</h2>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">Facebook ID</th>
                        <td><?= $page['id'] ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Facebook Name</th>
                        <td><?= $page['name'] ?></td>
                    </tr>
                </tbody>
            </table>
        <?php
        }
        ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="column">Permanent access token</th>
                    <td style="word-break : break-all;">
                        <?php
                        if ($fb_access_token) {
                            echo $fb_access_token;
                        } else {
                        ?>
                            <p>There is no permanent access token.</p>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <form action="" method="POST">
            <button class="button button-primary" name="permanent_token">Get permanent token</button>
        </form>
<?php

        /**
         * From here markup ends.
         */

        if (isset($_POST["permanent_token"])) {
            $graphNode = $response->getGraphNode();

            $request = $fb->request('GET', '/' . $graphNode['id'] . '/accounts', [], $fb_user_access_token);

            try {
                $response = $fb->getClient()->sendRequest($request);
                $graphEdge = $response->getGraphEdge();
                foreach ($graphEdge as $graphNode) {
                    $accessToken = $graphNode['access_token'];
                    update_option(PLUGIN_PREFIX . "fb_access_token", $accessToken);
                }
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo $e->getMessage();
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo $e->getMessage();
            }
        }
    }
}
