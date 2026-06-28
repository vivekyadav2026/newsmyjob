<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

Auth::logout();
Session::flash('success', 'You have been logged out successfully.');
redirect(adminUrl('login.php'));
