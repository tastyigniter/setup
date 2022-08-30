<?php

class SetupPDO extends PDO
{
    protected $configHost;

    protected $configPort;

    protected $configDatabase;

    protected $configUsername;

    protected $configPassword;

    protected $configPrefix;

    public function __construct(string $dsn, string $username, string $passwd, array $options, array $config)
    {
        foreach ($config as $item => $value) {
            $item = 'config'.ucfirst($item);
            if (property_exists($this, $item)) {
                $this->$item = $value;
            }
        }

        parent::__construct($dsn, $username, $passwd, $options);
    }

    public static function makeFromConfig(array $config)
    {
        extract($config);

        // Try connecting to database using the specified driver
        $dsn = 'mysql:host='.$host.';dbname='.$database;
        if ($port) $dsn .= ';port='.$port;

        $options = [self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION];

        return new self($dsn, $username, $password, $options, $config);
    }

    public function config($key, $default = null)
    {
        $item = 'config'.ucfirst($key);

        return $this->$item ?? $default;
    }

    public function isFreshlyInstalled()
    {
        $fetch = $this->query(
            "show tables where `tables_in_{$this->configDatabase}` like '".
            str_replace('_', '\\_', $this->configPrefix)."%'",
            static::FETCH_NUM
        );

        $tables = 0;
        while ($result = $fetch->fetch()) $tables++;

        return $tables < 1;
    }

    public function hasPreviouslyInstalledSettings()
    {
        $tableName = sprintf('%s%s', $this->configPrefix, 'settings');
        $fetch = $this->query("select * from {$tableName} where item = ".$this->quote('ti_version'), static::FETCH_ASSOC);

        return $fetch->fetch() !== false;
    }

    public function compareInstalledVersion()
    {
        $installedVersion = $this->query('select version()')->fetchColumn();

        if (!(strpos($installedVersion, 'MariaDB') === false)) {
            return true;
        }

        $installedVersion = substr($installedVersion, 0, 6);

        return version_compare($installedVersion, TI_MYSQL_VERSION, '>=');
    }
}
