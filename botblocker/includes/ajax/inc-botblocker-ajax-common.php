<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

function clear_all_settings()
{
	global $wpdb;
	$table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'settings';

	$result = $wpdb->query("TRUNCATE TABLE $table_name");
}

function database_reinstallation_callback()
{
	$result = clear_all_rules();
	if ($result === false) {
		wp_send_json_error('Failed to clear rules.');
	}

	$result = clear_all_paths();
	if ($result === false) {
		wp_send_json_error('Failed to clear paths.');
	}

	$result = clear_all_white();
	if ($result === false) {
		wp_send_json_error('Failed to clear white bots.');
	}

	$result = clear_all_ipv4_rules();
	if ($result === false) {
		wp_send_json_error('Failed to clear IPv4 rules.');
	}

	$result = clear_all_ipv6_rules();
	if ($result === false) {
		wp_send_json_error('Failed to clear IPv6 rules.');
	}

	$result = clear_all_settings();
	if ($result === false) {
		wp_send_json_error('Failed to clear settings.');
	}

	bbcs_deleteRuleFiles();

	$bbcs_start_files = true;
	$salt_bb = bbcs_createSaltFile($bbcs_start_files);
	bbcs_createTables();
	bbcs_addServerIPs();
	bbcs_dbIndexCreate();
	bbcs_insertInitialData($salt_bb);
	bbcs_createRuleFiles($bbcs_start_files);

	wp_send_json_success('Database has been reinstalled successfully.');
}
add_action('wp_ajax_database_reinstallation', 'database_reinstallation_callback');


function backup_data_settings_callback()
{
	global $wpdb;

	$tables = [
		'settings',
		'se',
		'rules',
		'path',
		'ipv6rules',
		'ipv4rules',
	];

	$temp_dir = wp_upload_dir()['basedir'] . '/botblocker_backups';
	if (!file_exists($temp_dir)) {
		mkdir($temp_dir, 0755, true);
	}

	$zip_file = $temp_dir . '/botblocker_backup_' . date('YmdHis') . '.zip';
	$zip = new ZipArchive();
	if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
		wp_send_json_error('Failed to create ZIP archive.');
	}

	foreach ($tables as $table) {
		$table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . $table;
		$dump_file = $temp_dir . '/' . $table . '.sql';
		$result = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

		$dump_content = '';
		if ($result) {
			foreach ($result as $row) {
				$dump_content .= json_encode($row) . "\n";
			}
		}

		file_put_contents($dump_file, $dump_content);
		$zip->addFile($dump_file, $table . '.sql');
	}

	$zip->close();

	foreach ($tables as $table) {
		$dump_file = $temp_dir . '/' . $table . '.sql';
		if (file_exists($dump_file)) {
			unlink($dump_file);
		}
	}

	$download_url = wp_upload_dir()['baseurl'] . '/botblocker_backups/' . basename($zip_file);

	// Проверяем, используется ли SSL (HTTPS)
	if (!is_ssl() && strpos($download_url, 'https://') === 0) {
		$download_url = str_replace('https://', 'http://', $download_url);
	} elseif (is_ssl() && strpos($download_url, 'http://') === 0) {
		$download_url = str_replace('http://', 'https://', $download_url);
	}

	wp_send_json_success([
		'download_url' => $download_url,
		'message' => 'Backup was successful.',
	]);
}
add_action('wp_ajax_backup_data_settings', 'backup_data_settings_callback');

function import_data_settings_callback()
{
	global $wpdb;

	$tables = [
		'settings',
		'se',
		'rules',
		'path',
		'ipv6rules',
		'ipv4rules',
	];

	$temp_dir = wp_upload_dir()['basedir'] . '/botblocker_backups';
	if (!file_exists($temp_dir)) {
		mkdir($temp_dir, 0755, true);
	}

	$zip_file = $_FILES['zip_file']['tmp_name'];
	if (!file_exists($zip_file)) {
		wp_send_json_error('ZIP archive not found.');
	}

	$zip = new ZipArchive();
	if ($zip->open($zip_file) !== true) {
		wp_send_json_error('Failed to open ZIP archive.');
	}

	$zip->extractTo($temp_dir);
	$zip->close();

	foreach ($tables as $table) {
		$table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . $table;
		$dump_file = $temp_dir . '/' . $table . '.sql';

		if (!file_exists($dump_file)) {
			wp_send_json_error("Dump file for table $table not found.");
		}

		$wpdb->query("TRUNCATE TABLE $table_name");

		$dump_content = file_get_contents($dump_file);
		$rows = explode("\n", $dump_content);

		foreach ($rows as $row) {
			if (!empty($row)) {
				$data = json_decode($row, true);
				$wpdb->insert($table_name, $data);
			}
		}
	}

	foreach ($tables as $table) {
		$dump_file = $temp_dir . '/' . $table . '.sql';
		if (file_exists($dump_file)) {
			unlink($dump_file);
		}
	}

	$bbcs_start_files = true;
	bbcs_createRuleFiles($bbcs_start_files);

	wp_send_json_success([
		'message' => 'Import data and settings was successful.',
	]);
}
add_action('wp_ajax_import_data_settings', 'import_data_settings_callback');
