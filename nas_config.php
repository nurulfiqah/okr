<?php
/**
 * NAS credentials come from this module's .env (gitignored, same file
 * mailer.php reads MAIL_* from) - never hardcode them here.
 */
function okrNasEnv($key, $default = '') {
    static $env = null;
    if ($env === null) {
        $path = __DIR__ . '/.env';
        $env = (is_file($path) && is_readable($path))
            ? (parse_ini_file($path, false, INI_SCANNER_RAW) ?: [])
            : [];
    }
    return $env[$key] ?? $default;
}

define('CORP_NAS_IP',       okrNasEnv('NAS_HOST'));
define('CORP_NAS_PORT',     (int)okrNasEnv('NAS_PORT', 5101));
define('CORP_NAS_USER',     okrNasEnv('NAS_USERNAME'));
define('CORP_NAS_PASSWORD', okrNasEnv('NAS_PASSWORD'));
define('CORP_NAS_FOLDER',   '/DigitalInnovation/okr');

function corpNasConnect() {
    require_once __DIR__ . '/lib/synologynas.php';
    if (CORP_NAS_IP === '' || CORP_NAS_USER === '' || CORP_NAS_PASSWORD === '') {
        error_log('OKR NAS not configured: missing NAS_HOST/NAS_USERNAME/NAS_PASSWORD in okr/.env');
    }
    return new CorporateNAS(CORP_NAS_IP, CORP_NAS_PORT, CORP_NAS_USER, CORP_NAS_PASSWORD);
}
