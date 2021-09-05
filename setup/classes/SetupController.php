<?php

use Admin\Models\Locations_model;
use Admin\Models\Staff_groups_model;
use Admin\Models\Staff_roles_model;
use Admin\Models\Staffs_model;
use Admin\Models\Users_model;
use Carbon\Carbon;
use System\Classes\ExtensionManager;
use System\Classes\UpdateManager;
use System\Database\Seeds\DatabaseSeeder;
use System\Models\Languages_model;
use System\Models\Themes_model;

/**
 * SetupController Class
 */
class SetupController
{
    public const TI_ENDPOINT = 'https://api.tastyigniter.com/v2';

    public $page;

    public $logFile;

    protected string $baseDirectory;

    protected string $tempDirectory;

    protected string $configDirectory;

    protected $repository;

    public function __construct()
    {
        // Establish directory paths
        $this->baseDirectory = BASEPATH;
        $this->tempDirectory = SETUPPATH.'/temp';
        $this->configDirectory = $this->baseDirectory.'/config';
        $this->logFile = SETUPPATH.'/setup.log';
        $this->writePostToLog();

        $this->repository = new SetupRepository(SETUPPATH.'/setup_config');

        // Execute post handler
        $this->execPostHandler();
    }

    public function getPage()
    {
        if (!$this->page)
            $this->page = new stdClass;

        $this->page->currentStep = 'start';
        if (isset($_GET['step']) AND strlen($_GET['step']) AND in_array($_GET['step'], ['requirements', 'database', 'settings']))
            $this->page->currentStep = $_GET['step'];

        return $this->page;
    }

    //
    // Post Handlers
    //

    public function onCheckRequirements()
    {
        $code = $this->post('code');
        $this->writeLog('System check: %s', $code);
        $result = FALSE;
        switch ($code) {
            case 'php':
                $result = version_compare(PHP_VERSION, TI_PHP_VERSION, '>=');
                break;
            case 'pdo':
                $result = (extension_loaded('pdo') AND extension_loaded('pdo_mysql'));
                break;
            case 'mbstring':
                $result = extension_loaded('mbstring');
                break;
            case 'ssl':
                $result = extension_loaded('openssl');
                break;
            case 'gd':
                $result = extension_loaded('gd');
                break;
            case 'curl':
                $result = function_exists('curl_init') AND defined('CURLOPT_FOLLOWLOCATION');
                break;
            case 'zip':
                $result = class_exists('ZipArchive');
                break;
            case 'uploads':
                $result = ini_get('file_uploads');
                break;
            case 'connection':
                $result = $this->checkLiveConnection();
                break;
            case 'writable':
                @rmdir($this->tempDirectory);
                $result = @mkdir($this->tempDirectory, 0777, TRUE);
                @rmdir($this->tempDirectory);

                break;
        }

        $this->repository->set('requirement', $result ? $code : 'fail')->save();

        $this->writeLog('Requirement %s %s', $code, ($result ? '+OK' : '=FAIL'));

        return ['result' => $result];
    }

    public function onCompleteRequirements()
    {
        $this->repository->set('requirement', (string)$this->post('requirement'))->save();
        $this->repository->set('license_agreed', (string)$this->post('license_agreed'))->save();

        $this->writeLog('License Agreement: %s', 'accepted');

        return ['step' => 'database'];
    }

    public function onCheckDatabase()
    {
        $database = $this->post('database');
        $this->writeLog('Database check: %s', $database);

        $this->confirmRequirements();

        if (!strlen($this->post('host')))
            throw new SetupException('Please specify a database host');

        if (!strlen($database = $this->post('database')))
            throw new SetupException('Please specify the database name');

        $config = $this->verifyDbConfiguration($this->post());

        $db = $this->testDbConnection($config);

        $step = ($db AND $this->hasDbInstalledSettings($db)) ? 'install' : 'settings';

        if ($step == 'install' AND $this->post('upgrade') != 1) {
            return ['modal' => '_popup_upgrade'];
        }

        $this->repository->set('database', $config);
        $result = $this->repository->save();

        $this->writeLog('Database %s %s', $database, ($result ? '+OK' : '=FAIL'));

        return $result ? ['step' => $step] : ['result' => $result];
    }

    public function onValidateSettings()
    {
        $siteName = $this->post('site_name');
        $this->writeLog('Settings check: %s', $siteName);

        $this->confirmRequirements();

        if (!strlen($siteName))
            throw new SetupException('Please specify your restaurant name');

        if (!strlen($siteEmail = $this->post('site_email')))
            throw new SetupException('Please specify your restaurant email');

        if (!strlen($adminName = $this->post('staff_name')))
            throw new SetupException('Please specify the administrator name');

        if (!strlen($username = $this->post('username')))
            throw new SetupException('Please specify the administrator username');

        $password = $this->post('password');
        if (!strlen($password) OR strlen($password) < 6)
            throw new SetupException('Please specify the administrator password, at least 6 characters');

        if (!strlen($this->post('confirm_password')))
            throw new SetupException('Please confirm the administrator password');

        if ($this->post('confirm_password') != $password)
            throw new SetupException('Password does not match');

        $this->repository->set('site_key', (string)$this->post('site_key'));

        $this->repository->set('settings', [
            'demo_data' => (int)$this->post('demo_data'),
            'site_name' => $siteName,
            'site_email' => $siteEmail,
            'staff_name' => $adminName,
            'username' => $username,
            'password' => $password,
        ]);

        $result = $this->repository->save();
        $this->writeLog('Settings %s', ($result ? '+OK' : '=FAIL'));

        return $result ? ['step' => 'install'] : ['result' => $result];
    }

    public function onInstall()
    {
        $installStep = $this->post('process');
        $this->writeLog('Foundation setup: %s', $installStep);
        $result = FALSE;

        $item = $this->post('item');

        $params = [];
        if ($this->post('step') != 'complete' AND isset($item['code'])) {
            $params = [
                'name' => $item['code'],
                'type' => $item['type'],
                'ver' => $item['version'],
                'action' => $item['action'],
            ];
        }

        switch ($installStep) {
            case 'apply':
                $result = $this->applyInstall();
                break;
            case 'downloadExtension':
            case 'downloadTheme':
            case 'downloadCore':
                if ($this->downloadFile($item['code'], $item['hash'], $params))
                    $result = TRUE;
                break;
            case 'extractExtension':
                if ($this->extractFile($item['code'], 'extensions/'))
                    $result = TRUE;
                break;
            case 'extractTheme':
                if ($this->extractFile($item['code'], 'themes/'))
                    $result = TRUE;

                $this->repository->set('activeTheme', $item['code']);
                break;
            case 'extractCore':
                if ($this->extractFile($item['code']))
                    $result = TRUE;

                $this->repository->set('core', $item);
                break;
            case 'writeConfig':
                $this->moveExampleFile('env', null, 'backup');
                $this->moveExampleFile('env', 'example', null);

                $this->rewriteEnvFile();

                $result = TRUE;
                break;
            case 'finishInstall':
                // Run migration
                $this->completeSetup();
                $this->completeInstall();
                $this->cleanUpAfterInstall();

                $this->moveExampleFile('htaccess', null, 'backup');
                $this->copyExampleFile('htaccess', 'example', null);

                $result = admin_url('login');
                break;
        }

        $status = $installStep == 'install' ? 'complete' : $installStep;
        $this->repository->set('install', $result ? $status : 'fail')->save();

        $this->writeLog('Foundation setup: %s %s', $installStep, ($result ? '+OK' : '=FAIL'));

        return ['result' => $result];
    }

    protected function execPostHandler()
    {
        $handler = $this->post('handler');
        if (!is_null($handler)) {
            if (!strlen($handler)) exit;

            try {
                if (!preg_match('/^on[A-Z]{1}[\w+]*$/', $handler))
                    throw new SetupException(sprintf('Invalid handler: %s', $this->e($handler)));
                if (method_exists($this, $handler) AND ($result = $this->$handler()) !== null) {
                    $this->writeLog('Execute handler (%s): %s', $handler, print_r($result, TRUE));
                    header('Content-Type: application/json');
                    exit(json_encode($result));
                }
            }
            catch (Exception $ex) {
                header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', TRUE, 500);
                $this->writeLog('Handler error (%s): %s', $handler, $ex->getMessage());
                $this->writeLog(['Trace log:', '%s'], $ex->getTraceAsString());
                exit($ex->getMessage());
            }
            exit;
        }
    }

    //
    // Database
    //

    public function checkDatabase()
    {
        if (!$config = $this->repository->get('database', []))
            return FALSE;

        // Make sure the database name is specified
        if (!isset($config['host']))
            return FALSE;

        try {
            $this->testDbConnection($config);
        }
        catch (Exception $ex) {
            return FALSE;
        }

        // At this point,
        // its clear database configuration is set
        // and connection successful
        return TRUE;
    }

    protected function testDbConnection($config = [])
    {
        try {
            $db = SetupPDO::makeFromConfig($config);

            if (!$db->compareInstalledVersion())
                throw new SetupException('Connection failed: '.lang('text_mysql_version'));
        }
        catch (PDOException $ex) {
            throw new SetupException('Connection failed: '.$ex->getMessage());
        }

        return $db;
    }

    protected function verifyDbConfiguration($config)
    {
        $result = [];
        $result['host'] = '127.0.0.1';
        if (isset($config['host']) AND is_string($config['host']))
            $result['host'] = trim($config['host']);

        $result['port'] = 3306;
        if (isset($config['port']) AND is_string($config['port']))
            $result['port'] = trim($config['port']);

        $result['database'] = '';
        if (isset($config['database']) AND is_string($config['database']))
            $result['database'] = trim($config['database']);

        $result['username'] = '';
        if (isset($config['username']) AND is_string($config['username']))
            $result['username'] = trim($config['username']);

        $result['password'] = '';
        if (isset($config['password']) AND is_string($config['password']))
            $result['password'] = $config['password'];

        $result['prefix'] = 'ti_';
        if (isset($config['prefix']) AND is_string($config['prefix']))
            $result['prefix'] = trim($config['prefix']);

        return $result;
    }

    /**
     * @param \SetupPDO $db
     *
     * @return bool
     */
    protected function hasDbInstalledSettings($db)
    {
        $this->repository->set('settingsInstalled', FALSE);

        // Nothing to import if database has no existing tables
        if ($db->isFreshlyInstalled())
            return FALSE;

        // Check whether to import existing database tables
        if ($db->hasPreviouslyInstalledSettings()) {
            $this->repository->set('settingsInstalled', TRUE);

            return TRUE;
        }

        throw new SetupException(sprintf(
            'Database "%s" is not empty. Please use an empty database or specify a different database table prefix.',
            $this->e($db->config('database'))
        ));
    }

    //
    // Setup steps
    //

    protected function applyInstall()
    {
        $params = $this->processInstallItems();

        $response = $this->requestRemoteData('core/install', $params);

        return $this->buildProcessSteps($response);
    }

    protected function processInstallItems()
    {
        $params = $items = [];

        if (!strlen($themeCode = $this->post('themeCode')))
            return $params;

        if ($dependencies = $this->fetchThemeDependencies($themeCode)) {
            foreach ($dependencies as $dependency) {
                $items[] = ['name' => $dependency['code'], 'type' => $dependency['type']];
            }
        }

        $items[] = ['name' => $themeCode, 'type' => 'theme'];

        $params['items'] = $items;

        return $params;
    }

    protected function buildProcessSteps($response)
    {
        $processSteps = [];

        foreach (['download', 'extract', 'config', 'install'] as $step) {
            $applySteps = [];

            if (in_array($step, ['config', 'install'])) {
                $processSteps[$step][] = [
                    'items' => $response['data'],
                    'process' => $step == 'install' ? 'finishInstall' : 'writeConfig',
                ];

                continue;
            }

            foreach ($response['data'] as $item) {
                if ($item['type'] == 'core') {
                    $applySteps[] = array_merge([
                        'action' => 'install',
                        'process' => "{$step}Core",
                    ], $item);
                }
                else {
                    $singularType = $item['type'];

                    $applySteps[] = array_merge([
                        'action' => 'install',
                        'process' => $step.ucfirst($singularType),
                    ], $item);
                }
            }

            $processSteps[$step] = $applySteps;
        }

        return $processSteps;
    }

    protected function completeSetup()
    {
        $this->bootFramework();

        $this->setSeederProperties($this->repository->get('settings'));

        // Install the database tables
        UpdateManager::instance()->update();

        // Create the admin user if no admin exists.
        $this->createSuperUser();

        $this->addSystemParameters();

        if ($this->repository->get('settingsInstalled', FALSE) === TRUE)
            return;

        // Save the site configuration to the settings table
        $this->addSystemSettings();
    }

    protected function createSuperUser()
    {
        // Abort: a super admin user already exists
        if (Users_model::where('super_user', 1)->count())
            return TRUE;

        if ($this->repository->get('settingsInstalled') === TRUE)
            return optional(Users_model::first())->update(['super_user' => 1]);

        $config = $this->repository->get('settings');

        $staffEmail = strtolower($config['site_email']);
        $staff = Staffs_model::firstOrNew([
            'staff_email' => $staffEmail,
        ]);

        $staff->staff_name = $config['staff_name'];
        $staff->staff_role_id = Staff_roles_model::first()->staff_role_id;
        $staff->language_id = Languages_model::first()->language_id;
        $staff->staff_status = TRUE;
        $staff->save();

        $staff->groups()->attach(Staff_groups_model::first()->staff_group_id);
        $staff->locations()->attach(Locations_model::first()->location_id);

        $user = Users_model::firstOrNew([
            'username' => $config['username'],
        ]);

        $user->staff_id = $staff->staff_id;
        $user->password = $config['password'];
        $user->super_user = TRUE;
        $user->is_activated = TRUE;
        $user->date_activated = Carbon::now();

        return $user->save();
    }

    protected function addSystemSettings()
    {
        setting()->flushCache();

        $settings = $this->repository->get('settings');
        $settings['sender_name'] = array_get($settings, 'site_name');
        $settings['sender_email'] = array_get($settings, 'site_email');

        setting()->set($settings);

        setting()->save();
    }

    protected function addSystemParameters()
    {
        $core = $this->repository->get('core');

        params()->flushCache();

        params()->set([
            'ti_setup' => 'installed',
            'ti_version' => array_get($core, 'version'),
            'sys_hash' => array_get($core, 'hash'),
            'site_key' => $this->repository->get('site_key'),
            'default_location_id' => Locations_model::first()->location_id,
        ]);

        // These parameter are no longer in use
        params()->forget('main_address');

        params()->save();
    }

    protected function completeInstall()
    {
        $item = $this->post('item');
        $items = $item['items'] ?? [];
        foreach ($items as $item) {
            if ($item['type'] != 'extension')
                continue;

            ExtensionManager::instance()->installExtension($item['code'], $item['version']);
        }

        Themes_model::syncAll();
        Themes_model::activateTheme($this->repository->get('activeTheme', 'demo'));
    }

    protected function cleanUpAfterInstall()
    {
        if (file_exists($this->tempDirectory)) {
            File::deleteDirectory($this->tempDirectory);
        }

        // Delete the setup repository file since its no longer needed
        $this->repository->destroy();
    }

    protected function setSeederProperties($properties)
    {
        DatabaseSeeder::$siteName = $properties['site_name'];
        DatabaseSeeder::$siteEmail = strtolower($properties['site_email']);
        DatabaseSeeder::$staffName = $properties['staff_name'];
        DatabaseSeeder::$seedDemo = (bool)$properties['demo_data'];
    }

    //
    // File
    //

    protected function getFilePath($fileCode)
    {
        $fileName = md5($fileCode).'.zip';

        return "$this->tempDirectory/$fileName";
    }

    protected function downloadFile($fileCode, $fileHash, $params)
    {
        return $this->requestRemoteFile('core/download', [
            'item' => $params,
        ], $fileCode, $fileHash);
    }

    protected function extractFile($fileCode, $directory = null)
    {
        $filePath = $this->getFilePath($fileCode);
        $this->writeLog('Extracting [%s] file %s', $fileCode, basename($filePath));

        $extractTo = $this->baseDirectory;
        if ($directory)
            $extractTo .= '/'.$directory.str_replace('.', '/', $fileCode);

        if (!file_exists($extractTo) AND !mkdir($extractTo, 0777, TRUE) AND !is_dir($extractTo)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $extractTo));
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
            @unlink($filePath);

            return TRUE;
        }

        $this->writeLog('Unable to open [%s] archive file %s', $fileCode, basename($filePath));

        return FALSE;
    }

    protected function rewriteEnvFile()
    {
        $env = $this->baseDirectory.'/.env';

        if (!file_exists($env))
            return;

        $database = $this->repository->get('database');
        foreach ($database as $config => $value) {
            if ($config === 'password') $value = '"'.$value.'"';
            $this->replaceInEnv('DB_'.strtoupper($config).'=', 'DB_'.strtoupper($config).'='.$value, $env);
        }

        $setting = $this->repository->get('settings');

        $this->replaceInEnv('APP_NAME=', 'APP_NAME="'.$setting['site_name'].'"', $env);
        $this->replaceInEnv('APP_URL=', 'APP_URL='.$this->getBaseUrl(), $env);
        $this->replaceInEnv('APP_KEY=', 'APP_KEY='.$this->generateKey(), $env);
        $this->replaceInEnv('IGNITER_LOCATION_MODE=', 'IGNITER_LOCATION_MODE='.$setting['site_location_mode'], $env);

        // Boot the framework after database config has been written
        // to eliminate database connection error.
        $this->bootFramework();
    }

    protected function moveExampleFile($name, $old, $new)
    {
        // /$old.$name => /$new.$name
        if (file_exists($this->baseDirectory.'/'.$old.'.'.$name)) {
            rename(
                $this->baseDirectory.'/'.$old.'.'.$name,
                $this->baseDirectory.'/'.$new.'.'.$name
            );
        }
    }

    protected function copyExampleFile($name, $old, $new)
    {
        // /$old.$name => /$new.$name
        if (file_exists($this->baseDirectory.'/'.$old.'.'.$name)) {
            if (file_exists($this->baseDirectory.'/'.$new.'.'.$name))
                unlink($this->baseDirectory.'/'.$new.'.'.$name);

            copy($this->baseDirectory.'/'.$old.'.'.$name, $this->baseDirectory.'/'.$new.'.'.$name);
        }
    }

    protected static function generateKey()
    {
        return 'base64:'.base64_encode(random_bytes(32));
    }

    protected function replaceInEnv(string $search, string $replace)
    {
        $file = $this->baseDirectory.'/.env';

        file_put_contents(
            $file,
            preg_replace('/^'.$search.'(.*)$/m', $replace, file_get_contents($file))
        );
    }

    //
    // Helpers
    //

    public function getDatabaseDetails()
    {
        $defaults = [
            'database' => '',
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => '',
            'password' => '',
            'prefix' => 'ti_',
        ];

        $settings = [];
        $db = $this->repository->get('database');
        foreach ($defaults as $item => $value) {
            if ($this->post($item)) {
                $settings[$item] = $this->post($item);
            }
            elseif (isset($db[$item])) {
                $settings[$item] = $db[$item];
            }
            else {
                $settings[$item] = $value;
            }
        }

        return (object)$settings;
    }

    public function getSettingsDetails()
    {
        $defaults = [
            'site_location_mode' => 'multiple',
            'site_name' => 'TastyIgniter',
            'site_email' => 'admin@restaurant.com',
            'staff_name' => 'Chef Sam',
            'username' => 'admin',
            'demo_data' => 1,
        ];

        $settings = [];
        foreach ($defaults as $item => $value) {
            if ($this->post($item)) {
                $settings[$item] = $this->post($item);
            }
            elseif ($this->repository->has($item)) {
                $settings[$item] = $this->repository->get($item);
            }
            else {
                $settings[$item] = $value;
            }
        }

        return (object)$settings;
    }

    protected function confirmRequirements()
    {
        $licenseAgreed = $this->repository->get('license_agreed');
        if (!strlen($licenseAgreed) OR $licenseAgreed != 'accepted')
            throw new SetupException('Please accept the TastyIgniter license before proceeding.');

        $requirement = $this->repository->get('requirement');
        if (!strlen($requirement) OR $requirement != 'complete')
            throw new SetupException('Please make sure your system meets all requirements');
    }

    protected function bootFramework()
    {
        if (!is_file($this->baseDirectory.'/vendor/tastyigniter/flame/src/Support/helpers.php'))
            throw new SetupException('Missing vendor files.');

        $autoloadFile = $this->baseDirectory.'/bootstrap/autoload.php';
        if (!file_exists($autoloadFile))
            throw new SetupException('Autoloader file was not found.');

        include $autoloadFile;

        $appFile = $this->baseDirectory.'/bootstrap/app.php';
        if (!file_exists($appFile))
            throw new SetupException('App loader file was not found.');

        $app = require_once $appFile;

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }

    protected function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', FALSE);
    }

    protected function server($key, $default = null)
    {
        if (array_key_exists($key, $_SERVER)) {
            $result = $_SERVER[$key];
            if (is_string($result)) $result = trim($result);

            return $result;
        }

        return $default;
    }

    protected function post($key = null, $default = null)
    {
        if (is_null($key))
            return $_POST;

        if (array_key_exists($key, $_POST)) {
            $result = $_POST[$key];
            if (is_string($result)) $result = trim($result);

            return $result;
        }

        return $default;
    }

    protected function session($key, $default = null)
    {
        if (array_key_exists($key, $_SESSION)) {
            $result = $_SESSION[$key];
            if (is_string($result)) $result = trim($result);

            return $result;
        }

        return $default;
    }

    protected function getBaseUrl()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $baseUrl = (!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
            $baseUrl .= '://'.$_SERVER['HTTP_HOST'];
            $baseUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }
        else {
            $baseUrl = 'http://localhost/';
        }

        return $baseUrl;
    }

    protected function requestRemoteData($uri, $params = [])
    {
        $result = null;
        $error = null;

        try {
            $this->writeLog('Server request: %s', $uri);

            $curl = $this->prepareRequest($uri, $params);
            $result = curl_exec($curl);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode == 500) {
                $error = $result;
                $result = '';
            }

            $this->writeLog('Request information: %s', print_r(curl_getinfo($curl), TRUE));
            curl_close($curl);
        }
        catch (Exception $ex) {
            $this->writeLog('Failed to get server data (ignored): '.$ex->getMessage());
        }

        if ($error !== null)
            throw new SetupException('Server responded with error: '.$error);

        try {
            $_result = @json_decode($result, TRUE);
        }
        catch (Exception $ex) {
        }

        if (!is_array($_result)) {
            $this->writeLog('Server response: '.$result);

            throw new SetupException('Server returned an invalid response.');
        }

        if (isset($_result['message']) AND !in_array($httpCode, [200, 201])) {
            if (isset($_result['errors']))
                $this->writeLog('Server validation errors: '.print_r($_result['errors'], TRUE));

            throw new SetupException($_result['message']);
        }

        return $_result;
    }

    protected function requestRemoteFile($uri, array $params, $code, $expectedHash)
    {
        if (!mkdir($this->tempDirectory, 0777, TRUE) AND !is_dir($this->tempDirectory))
            throw new SetupException(sprintf('Failed to create temp directory: %s', $this->tempDirectory));

        try {
            $filePath = $this->getFilePath($code);
            $curl = $this->prepareRequest($uri, $params);
            $fileStream = fopen($filePath, 'wb');
            curl_setopt($curl, CURLOPT_FILE, $fileStream);
            curl_exec($curl);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode == 500)
                throw new SetupException(file_get_contents($filePath));
            curl_close($curl);
            fclose($fileStream);
        }
        catch (Exception $ex) {
            $this->writeLog('Server responded with error: '.$ex->getMessage());

            throw new SetupException('Server responded with error: '.$ex->getMessage());
        }

        $fileSha = sha1_file($filePath);
        if ($expectedHash != $fileSha) {
            $this->writeLog(file_get_contents($filePath));
            $this->writeLog("Download failed, File hash mismatch: $expectedHash (expected) vs $fileSha (actual)");
            @unlink($filePath);

            throw new SetupException('Downloaded files from server are corrupt, check setup.log');
        }

        $this->writeLog('Downloaded file (%s) to %s', $code, $filePath);

        return TRUE;
    }

    protected function prepareRequest($uri, $params = [])
    {
        $params['client'] = 'tastyigniter';
        $params['server'] = base64_encode(serialize([
            'php' => PHP_VERSION,
            'url' => $this->getBaseUrl(),
            'version' => $this->repository->get('ti_version'),
        ]));

        if (isset($_GET['edge']) AND $_GET['edge'] == 1)
            $params['edge'] = 1;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, static::TI_ENDPOINT.'/'.$uri);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3600);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));

        // Used to skip SSL Check on Wamp Server which causes the Live Status Check to fail
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        if (strlen($siteKey = $this->repository->get('site_key'))) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["TI-Rest-Key: bearer {$siteKey}"]);
        }

        return $curl;
    }

    protected function fetchThemeDependencies($themeCode)
    {
        $theme = $this->requestRemoteData('item/detail', [
            'item' => [
                'name' => $themeCode ?? 'tastyigniter-orange',
                'type' => 'theme',
            ],
            'include' => 'require',
        ]);

        if (!isset($theme['data']['require']['data'])
            OR !is_array($theme['data']['require']['data'])
            OR !count($theme['data']['require']['data'])
        ) return [];

        return $theme['data']['require']['data'];
    }

    //
    // Logging
    //

    public function cleanLog()
    {
        $message = '.++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++.'."\n";
        $message .= '.------------------- TASTYIGNITER SETUP LOG ------------------.'."\n";
        $message .= '.++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++.';
        file_put_contents($this->logFile, $message.PHP_EOL);
    }

    public function writeLog()
    {
        $args = func_get_args();

        $message = array_shift($args);
        if (is_array($message))
            $message = implode(PHP_EOL, $message);

        $date = '';
        if (!array_key_exists('hideTime', $args))
            $date = '['.date('Y/m/d h:i:s').'] => ';

        $message = vsprintf($date.$message, $args).PHP_EOL;

        file_put_contents($this->logFile, $message, FILE_APPEND);
    }

    protected function writePostToLog()
    {
        if (!isset($_POST) OR !count($_POST)) return;

        $postData = $_POST;
        if (array_key_exists('disableLog', $postData))
            $postData = ['disableLog' => TRUE];

        // Filter sensitive data fields
        $fieldsToErase = [
            'encryption_code',
            'admin_password',
            'admin_confirm_password',
            'db_pass',
            'project_id',
        ];

        if (isset($postData['admin_email'])) $postData['admin_email'] = '*******@*****.com';
        foreach ($fieldsToErase as $field) {
            if (isset($postData[$field])) $postData[$field] = '*******';
        }

        $this->writeLog('.============================ POST REQUEST ==========================.', ['hideTime' => TRUE]);
        $this->writeLog('Postback payload: %s', print_r($postData, TRUE));
    }

    protected function checkLiveConnection()
    {
        $result = $this->requestRemoteData('ping') ?: [];

        $latestVersion = $result['pong'];
        $this->repository->set('ti_version', $latestVersion);

        return (bool)strlen($latestVersion);
    }
}
