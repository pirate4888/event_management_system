<?php

/**
 * Plugin Name: Frontend User Management
 * Plugin URI: https://github.com/SchwarzwaldFalke/frontend-user-management
 * Description: Plugin which allows user to register, login and edit their user profile in frontend. It also adds activation mails during user registration
 * Version: 0.06
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */

require_once( 'src/frontend-user-management.php' );
new Frontend_User_Management(); //start plugin