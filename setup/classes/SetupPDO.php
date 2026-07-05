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

        $dsn = 'mysql:host='.$host.';dbname='.$database;
        if ($port) {
            $dsn .= ';port='.$port;
        }

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
        return $this->countPrefixedTables() < 1;
    }

    public function canUpgradeExistingInstallation(): bool
    {
        $settingsTable = $this->prefixedTable('settings');

        if (!$this->tableExists($settingsTable)) {
            return false;
        }

        if ($this->settingExists('ti_version')) {
            return true;
        }

        if ($this->tableHasRows($settingsTable)) {
            return true;
        }

        foreach (['locations', 'users', 'categories'] as $table) {
            if ($this->tableExists($this->prefixedTable($table))) {
                return true;
            }
        }

        return false;
    }

    public function hasPreviouslyInstalledSettings()
    {
        return $this->canUpgradeExistingInstallation();
    }

    protected function countPrefixedTables(): int
    {
        $fetch = $this->query(
            "show tables where `tables_in_{$this->configDatabase}` like '".
            str_replace('_', '\\_', $this->configPrefix)."%'",
            static::FETCH_NUM
        );

        $tables = 0;
        while ($fetch->fetch()) {
            $tables++;
        }

        return $tables;
    }

    protected function prefixedTable(string $name): string
    {
        return sprintf('%s%s', $this->configPrefix, $name);
    }

    protected function tableExists(string $tableName): bool
    {
        $statement = $this->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?'
        );
        $statement->execute([$this->configDatabase, $tableName]);

        return (bool)$statement->fetchColumn();
    }

    protected function tableHasRows(string $tableName): bool
    {
        if (!$this->tableExists($tableName)) {
            return false;
        }

        $statement = $this->query(sprintf('SELECT COUNT(*) FROM `%s`', str_replace('`', '``', $tableName)));

        return (int)$statement->fetchColumn() > 0;
    }

    protected function settingExists(string $item): bool
    {
        $tableName = $this->prefixedTable('settings');

        if (!$this->tableExists($tableName)) {
            return false;
        }

        $fetch = $this->query(
            sprintf('SELECT 1 FROM `%s` WHERE item = ', str_replace('`', '``', $tableName)).$this->quote($item).' LIMIT 1',
            static::FETCH_NUM
        );

        return $fetch->fetch() !== false;
    }

    public function compareInstalledVersion()
    {
        $installedVersion = $this->query('select version()')->fetchColumn();

        if (stripos($installedVersion, 'MariaDB') !== false) {
            if (preg_match('/(\d+\.\d+)/', $installedVersion, $matches)) {
                return version_compare($matches[1], TI_MARIADB_VERSION, '>=');
            }

            return true;
        }

        if (preg_match('/(\d+\.\d+)/', $installedVersion, $matches)) {
            return version_compare($matches[1], TI_MYSQL_VERSION, '>=');
        }

        return version_compare(substr($installedVersion, 0, 6), TI_MYSQL_VERSION, '>=');
    }
}
