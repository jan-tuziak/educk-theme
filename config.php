<?php
$TLD = end(explode('.', parse_url($home_url)["host"])); // 'org' or 'pl'
$config_path = ($TLD === 'pl') ? 'configs/pl.json' : 'configs/org.json';
$config = json_decode(get_theme_file_path($config_path));