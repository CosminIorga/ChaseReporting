<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 11/05/17
 * Time: 12:28
 */

namespace App\Services;


use App\Definitions\Columns;
use App\Definitions\Common;
use App\Definitions\Data;
use App\Definitions\Logger;
use App\Exceptions\ConfigException;
use App\Exceptions\ServiceException;

/**
 * Class ConfigGetter
 * @package App\Services
 * @property string $tableInterval
 * @property integer $dataInterval
 * @property array $primaryColumnData
 * @property array $pivotColumnsData
 * @property array $intervalColumnData
 * @property array $timestampData
 * @property array $aggregateData
 * @property array $loggerChannels
 */
class ConfigGetter
{

    /**
     * @var string
     */
    protected $_tableInterval;

    /**
     * @var int
     */
    protected $_dataInterval;

    /**
     * @var array
     */
    protected $_primaryColumnData;

    /**
     * @var array
     */
    protected $_pivotColumnsData;

    /**
     * @var array
     */
    protected $_intervalColumnData;

    /**
     * @var array
     */
    protected $_timestampData;

    /**
     * @var array
     */
    protected $_aggregateData;

    /**
     * @var array
     */
    protected $_loggerChannels;

    /**
     * Array used to store an association between column name and column type (Timestamp, Primary, Pivot, Interval)
     * @var array
     */
    protected $_columnMapping;


    /**
     * Array used to store associations between variable name and its config path
     * @var array
     */
    protected $mapping = [
        'tableInterval' => 'common.table_interval',
        'dataInterval' => 'common.data_interval',
        'primaryColumnData' => 'columns.primary_key',
        'pivotColumnsData' => 'columns.pivots',
        'intervalColumnData' => 'columns.intervals',
        'timestampData' => 'columns.timestamp_key',
        'aggregateData' => 'columns.aggregates',
        'loggerChannels' => 'logger'
    ];

    /**
     * Instantiator for
     * @return ConfigGetter
     */
    public static function Instance()
    {
        static $inst = null;

        if ($inst === null) {
            $inst = new ConfigGetter();
        }

        return $inst;
    }

    /**
     * ConfigGetter constructor.
     */
    private function __construct()
    {
    }

    /**
     * Function used to validate table interval
     * @param string $tableInterval
     * @throws ConfigException
     */
    protected function validateAndProcessTableInterval(string $tableInterval)
    {
        /* Check if tableInterval in allowed table intervals */
        if (!in_array($tableInterval, Common::AVAILABLE_TABLE_INTERVALS)) {
            throw new ConfigException(
                sprintf(
                    ConfigException::TABLE_INTERVAL_NOT_ALLOWED,
                    $tableInterval,
                    implode(', ', Common::AVAILABLE_TABLE_INTERVALS)
                )
            );
        }
    }

    /**
     * Function used to validate data interval
     * @param int $dataInterval
     * @throws ConfigException
     */
    protected function validateAndProcessDataInterval(int $dataInterval)
    {
        if (!in_array($dataInterval, Common::AVAILABLE_DATA_INTERVALS)) {
            throw new ConfigException(
                sprintf(
                    ConfigException::DATA_INTERVAL_NOT_ALLOWED,
                    $dataInterval,
                    implode(', ', Common::AVAILABLE_DATA_INTERVALS)
                )
            );
        }
    }

    /**
     * Function used to validate primary column data
     * @param array $primaryColumnData
     */
    protected function validateAndProcessPrimaryColumnData(array &$primaryColumnData)
    {
        $this->validateAndProcessColumnData($primaryColumnData);
    }

    /**
     * Function used to validate pivot columns data
     * @param array $pivotColumnsData
     */
    protected function validateAndProcessPivotColumnsData(array &$pivotColumnsData)
    {
        foreach ($pivotColumnsData as &$pivotColumnData) {
            $this->validateAndProcessColumnData($pivotColumnData);
        }
    }

    /**
     * Function used to validate interval column data
     * @param array $intervalColumnData
     */
    protected function validateAndProcessIntervalColumnData(array &$intervalColumnData)
    {
        $this->validateAndProcessColumnData($intervalColumnData);
    }

    /**
     * Function used to validate timestamp column data
     * @param array $timestampData
     */
    protected function validateAndProcessTimestampData(array &$timestampData)
    {
        $this->validateAndProcessColumnData($timestampData);
    }

    /**
     * Function used to validate generic column data
     * @param array $columnData
     * @throws ConfigException
     */
    protected function validateAndProcessColumnData(array &$columnData)
    {
        /* Required keys that have to be in column data */
        $requiredKeys = [
            Data::CONFIG_COLUMN_NAME,
            Data::CONFIG_COLUMN_DATA_TYPE,
        ];

        /* Values assumed if key not found */
        $defaultValues = [
            Data::CONFIG_COLUMN_INDEX => Columns::COLUMN_SIMPLE_INDEX,
            Data::CONFIG_COLUMN_ALLOW_NULL => false
        ];

        /* Validate required data */
        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $columnData)) {
                throw new ConfigException(
                    sprintf(
                        ConfigException::COLUMN_DATA_INCOMPLETE,
                        $requiredKey
                    ),
                    $columnData
                );
            }
        }

        /* Complete with default data */
        foreach ($defaultValues as $defaultKey => $defaultValue) {
            if (!array_key_exists($defaultKey, $columnData)) {
                $columnData[$defaultKey] = $defaultValue;
            }
        }

        /* Validate data_type value */
        if (!in_array($columnData[Data::CONFIG_COLUMN_DATA_TYPE], Columns::AVAILABLE_COLUMN_DATA_TYPES)) {
            throw new ConfigException(
                sprintf(
                    ConfigException::INVALID_CONFIG_DATA_TYPE,
                    $columnData[Data::CONFIG_COLUMN_DATA_TYPE]
                ),
                $columnData
            );
        }

        /* Validate index value */
        if (!in_array($columnData[Data::CONFIG_COLUMN_INDEX], Columns::AVAILABLE_COLUMN_INDEXES)) {
            throw new ConfigException(
                sprintf(
                    ConfigException::INVALID_CONFIG_INDEX,
                    $columnData[Data::CONFIG_COLUMN_INDEX]
                ),
                $columnData
            );
        }
    }

    /**
     * Function used to validate aggregate config data
     * @param array $aggregateData
     * @throws ConfigException
     */
    protected function validateAndProcessAggregateData(array &$aggregateData)
    {
        foreach ($aggregateData as $aggregateKey => &$aggregateRecord) {
            $requiredKeys = [
                Data::AGGREGATE_INPUT_NAME,
                Data::AGGREGATE_INPUT_FUNCTION,
                Data::AGGREGATE_OUTPUT_FUNCTIONS
            ];

            /* Values assumed if key not found */
            $defaultValues = [];

            /* Validate required data */
            foreach ($requiredKeys as $requiredKey) {
                if (!array_key_exists($requiredKey, $aggregateRecord)) {
                    throw new ConfigException(
                        sprintf(
                            ConfigException::AGGREGATE_DATA_INCOMPLETE,
                            $requiredKey
                        ),
                        $aggregateData
                    );
                }
            }

            /* Complete with default data */
            foreach ($defaultValues as $defaultKey => $defaultValue) {
                if (!array_key_exists($defaultKey, $aggregateRecord)) {
                    $aggregateRecord[$defaultKey] = $defaultValue;
                }
            }
        }
    }

    /**
     * Function used to validate config logger channels
     * @param array $loggerChannels
     * @throws ConfigException
     */
    protected function validateAndProcessLoggerChannels(array $loggerChannels)
    {
        /* Iterate over each logger channel */
        foreach ($loggerChannels as $channel) {
            /* Check if medium key is array */
            if (
                !array_key_exists(Logger::MEDIUMS, $channel)
                || !is_array($channel[Logger::MEDIUMS])
            ) {
                throw new ConfigException(
                    ConfigException::LOGGER_CHANNEL_MEDIUM_KEY_NOT_ARRAY
                );
            }

            /* Check if it has at least one medium set */
            if (empty($channel[Logger::MEDIUMS])) {
                throw new ConfigException(
                    ConfigException::LOGGER_CHANNEL_MEDIUM_NOT_EMPTY
                );
            }

            /* Check if minLevel key exists and contains a valid value */
            if (
                array_key_exists(Logger::MIN_LOG_LEVEL, $channel)
                && !in_array($channel[Logger::MIN_LOG_LEVEL], array_keys(Logger::LEVELS))
            ) {
                throw new ConfigException(
                    sprintf(
                        ConfigException::LOGGER_INVALID_MINIMUM_LOG_LEVEL,
                        $channel[Logger::MIN_LOG_LEVEL]
                    )
                );
            }
        }
    }


    /**
     * Magic getter for config variables
     * @param string $name
     * @return mixed
     * @throws ServiceException
     */
    public function __get(string $name)
    {
        /* Check if variable is allowed to be fetched */
        if (!in_array($name, array_keys($this->mapping))) {
            throw new ServiceException(
                sprintf(
                    ServiceException::SERVICE_GETTER_INVALID_FUNCTION,
                    $name
                )
            );
        }

        if (!is_null($this->{"_{$name}"})) {
            return $this->{"_{$name}"};
        }

        /* Get config value */
        $data = $this->computeValue($name);

        /* Store data */
        $this->{"_{$name}"} = $data;

        return $this->{"_{$name}"};
    }

    /**
     * Function used to compute a config value given a variable name
     * @param string $name
     * @return mixed
     */
    protected function computeValue(string $name)
    {
        /* Get config value */
        $data = config($this->mapping[$name]);

        /* Validate data */
        $function = "validateAndProcess" . ucfirst($name);

        if (method_exists($this, $function)) {
            $this->$function($data);
        }

        return $data;
    }

    /**
     * Function used to return an association between column type and column name
     * @return array
     */
    public function getColumnMapping(): array
    {
        if (is_null($this->_columnMapping)) {
            $this->_columnMapping = $this->computeColumnMapping();
        }

        return $this->_columnMapping;
    }

    /**
     * Function used to compute column mapping
     * @return array
     */
    protected function computeColumnMapping(): array
    {
        $columnMapping = [];

        /* Fetch primary column data */
        if (is_null($this->_primaryColumnData)) {
            $this->_primaryColumnData = $this->computeValue('primaryColumnData');
        }

        $columnMapping[] = [
            Data::CONFIG_COLUMN_NAME => $this->_primaryColumnData[Data::CONFIG_COLUMN_NAME],
            Data::CONFIG_COLUMN_TYPE => Columns::COLUMN_PRIMARY
        ];

        /* Fetch pivot column data */
        if (is_null($this->_pivotColumnsData)) {
            $this->_pivotColumnsData = $this->computeValue('pivotColumnsData');
        }

        foreach ($this->_pivotColumnsData as $pivot) {
            $columnMapping[] = [
                Data::CONFIG_COLUMN_NAME => $pivot[Data::CONFIG_COLUMN_NAME],
                Data::CONFIG_COLUMN_TYPE => Columns::COLUMN_PIVOT
            ];
        }

        /* Fetch timestamp column data */
        if (is_null($this->_timestampData)) {
            $this->_timestampData = $this->computeValue('timestampData');
        }

        $columnMapping[] = [
            Data::CONFIG_COLUMN_NAME => $this->_timestampData[Data::CONFIG_COLUMN_NAME],
            Data::CONFIG_COLUMN_TYPE => Columns::COLUMN_TIMESTAMP
        ];

        return $columnMapping;
    }

    /**
     * Function used to retrieve aggregate config by jsonName
     * @param string $jsonName
     * @return array
     * @throws ConfigException
     */
    public function getAggregateConfigByJsonName(string $jsonName): array
    {
        if (is_null($this->_aggregateData)) {
            $this->_aggregateData = $this->computeValue('aggregateData');
        }

        if (!array_key_exists($jsonName, $this->_aggregateData)) {
            throw new ConfigException(
                sprintf(
                    ConfigException::UNKNOWN_AGGREGATE_JSON_NAME,
                    $jsonName
                )
            );
        }

        return array_merge($this->_aggregateData[$jsonName], [
            Data::AGGREGATE_JSON_NAME => $jsonName
        ]);
    }

    /**
     * Function used to retrieve pivot column config based on given pivot column name
     * @param string $pivotName
     * @return array
     * @throws ConfigException
     */
    public function getPivotConfigByName(string $pivotName): array
    {
        if (is_null($this->_pivotColumnsData)) {
            $this->_pivotColumnsData = $this->computeValue('pivotColumnsData');
        }

        foreach ($this->_pivotColumnsData as $pivotColumnsDatum) {
            if ($pivotColumnsDatum[Data::CONFIG_COLUMN_NAME] == $pivotName) {
                return $pivotColumnsDatum;
            }
        }

        throw new ConfigException(
            sprintf(
                ConfigException::UNKNOWN_PIVOT_COLUMN_NAME,
                $pivotName
            )
        );
    }


}