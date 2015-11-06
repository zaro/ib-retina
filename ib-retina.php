<?php
/*
Plugin Name: ib-retina
Description: Creates 2x images for image uploads.
Version: 1.1.0
Author: educatorteam
Author URI: http://educatorplugin.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ib-retina
*/

/*
Copyright (C) 2015 http://educatorplugin.com/ - contact@educatorplugin.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

define( 'IB_RETINA_URL', plugins_url( '', __FILE__ ) );

require_once 'include/ib-retina.php';
IB_Retina::init();

if ( is_admin() ) {
	require_once 'include/ib-retina-admin.php';
	IB_Retina_Admin::init();
}
