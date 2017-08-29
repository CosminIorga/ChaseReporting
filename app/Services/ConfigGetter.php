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
 * @property array $metaAggregateData
 * @property array $allAggregateData
 * @property array $loggerChannels
 * @property array $columnMapping
 */
class ConfigGetter
{
    /**
     * Constants used in mapping array
     */
    const TYPE = 'type';
    const CONFIG = 'config';

    const CONFIG_TYPE = 'config';
    const PROCESS_TYPE = 'process';

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
    protected $_metaAggregateData;

    /**
     * @var array
     */
    protected $_allAggregateData;

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
        'tableInterval' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'common.table_interval',
        ],
        'dataInterval' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'common.data_interval',
        ],
        'primaryColumnData' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'columns.primary_key',
        ],
        'pivotColumnsData' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'columns.pivots',
        ],
        'intervalColumnData' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'columns.intervals',
        ],
        'timestampData' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'columns.timestamp_key',
        ],
        'aggregateData' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'columns.aggregates',
        ],
        'metaAggregateData' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'columns.meta_aggregates',
        ],
        'loggerChannels' => [
            self::TYPE => self::CONFIG_TYPE,
            self::CONFIG => 'logger',
        ],
        'allAggregateData' => [
            self::TYPE => self::PROCESS_TYPE,
            self::CONFIG => null,
        ],
        'columnMapping' => [
            self::TYPE => self::PROCESS_TYPE,
            self::CONFIG => null,
        ],
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
    protected function processAndValidateTableInterval(string $tableInterval)
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
    protected function processAndValidateDataInterval(int $dataInterval)
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
    protected function processAndValidatePrimaryColumnData(array &$primaryColumnData)
    {
        $this->processAndValidateColumnData($primaryColumnData);
    }

    /**
     * Function used to validate pivot columns data
     * @param array $pivotColumnsData
     */
    protected function processAndValidatePivotColumnsData(array &$pivotColumnsData)
    {
        foreach ($pivotColumnsData as &$pivotColumnData) {
            $this->processAndValidateColumnData($pivotColumnData);
        }
    }

    /**
     * Function used to validate interval column data
     * @param array $intervalColumnData
     */
    protected function processAndValidateIntervalColumnData(array &$intervalColumnData)
    {
        $this->processAndValidateColumnData($intervalColumnData);
    }

    /**
     * Function used to validate timestamp column data
     * @param array $timestampData
     */
    protected function processAndValidateTimestampData(array &$timestampData)
    {
        $this->processAndValidateColumnData($timestampData);
    }

    /**
     * Function used to validate generic column data
     * @param array $columnData
     * @throws ConfigException
     */
    protected function processAndValidateColumnData(array &$columnData)
    {
        /* Required keys that have to be in column data */
        $requiredKeys = [
            Data::CONFIG_COLUMN_NAME,
            Data::CONFIG_COLUMN_DATA_TYPE,
        ];

        /* Values assumed if key not found */
        $defaultValues = [
            Data::CONFIG_COLUMN_INDEX => Columns::COLUMN_SIMPLE_INDEX,
            Data::CONFIG_COLUMN_ALLOW_NULL => false,
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
    protected function processAndValidateAggregateData(array &$aggregateData)
    {
        foreach ($aggregateData as $aggregateKey => &$aggregateRecord) {
            $requiredKeys = [
                Data::AGGREGATE_INPUT_NAME,
                Data::AGGREGATE_INPUT_FUNCTION,
                Data::AGGREGATE_OUTPUT_FUNCTIONS,
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
     * Function used to validate meta aggregate config data against same aggregate data rules
     * @param array $metaAggregateData
     */
    protected function processAndValidateMetaAggregateData(array &$metaAggregateData)
    {
        $this->processAndValidateAggregateData($metaAggregateData);
    }

    /**
     * Function used to compute and validate allAggregateData array based on aggregateData and metaAggregateData
     * @return array
     */
    protected function computeAndValidateAllAggregateData(): array
    {
        $this->computeAndStoreDataIfNotExists('aggregateData');
        $this->computeAndStoreDataIfNotExists('metaAggregateData');

        return array_merge(
            $this->fetchData('aggregateData'),
            $this->fetchData('metaAggregateData')
        );
    }

    /**
     * Function used to compute column mapping
     * @return array
     */
    protected function computeAndValidateColumnMapping(): array
    {
        $columnMapping = [];

        /* Fetch primary column data */
        $this->computeAndStoreDataIfNotExists('primaryColumnData');

        $columnMapping[] = [
            Data::CONFIG_COLUMN_NAME => $this->_primaryColumnData[Data::CONFIG_COLUMN_NAME],
            Data::CONFIG_COLUMN_TYPE => Columns::COLUMN_PRIMARY,
        ];

        /* Fetch pivot column data */
        $this->computeAndStoreDataIfNotExists('pivotColumnsData');

        foreach ($this->_pivotColumnsData as $pivot) {
            $columnMapping[] = [
                Data::CONFIG_COLUMN_NAME => $pivot[Data::CONFIG_COLUMN_NAME],
                Data::CONFIG_COLUMN_TYPE => Columns::COLUMN_PIVOT,
            ];
        }

        /* Fetch timestamp column data */
        $this->computeAndStoreDataIfNotExists('timestampData');

        $columnMapping[] = [
            Data::CONFIG_COLUMN_NAME => $this->_timestampData[Data::CONFIG_COLUMN_NAME],
            Data::CONFIG_COLUMN_TYPE => Columns::COLUMN_TIMESTAMP,
        ];

        return $columnMapping;
    }

    /**
     * Function used to validate config logger channels
     * @param array $loggerChannels
     * @throws ConfigException
     */
    protected function processAndValidateLoggerChannels(array $loggerChannels)
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
        /* Check if variable has a mapping association */
        if (!in_array($name, array_keys($this->mapping))) {
            throw new ServiceException(
                sprintf(
                    ServiceException::SERVICE_GETTER_INVALID_FUNCTION,
                    $name
                )
            );
        }

        $this->computeAndStoreDataIfNotExists($name);

        return $this->fetchData($name);
    }

    /**
     * Function used to compute and store data if not exists
     * @param string $name
     */
    protected function computeAndStoreDataIfNotExists(string $name)
    {
        if (!$this->checkIfDataExists($name)) {
            $this->computeAndStoreData($name);
        }
    }

    /**
     * Function used to compute and store data
     * @param string $name
     */
    protected function computeAndStoreData(string $name)
    {
        $data = $this->computeData($name);

        $this->storeData($name, $data);
    }

    /**
     * Small function used to store data in name variable
     * @param string $name
     * @param mixed $data
     */
    protected function storeData(string $name, $data)
    {
        $this->{"_{$name}"} = $data;
    }

    /**
     * Small function used to fetch data given a variable name
     * @param string $name
     * @return mixed
     */
    protected function fetchData(string $name)
    {
        return $this->{"_{$name}"};
    }

    /**
     * Small function used to check if value is null
     * @param string $name
     * @return bool
     */
    protected function checkIfDataExists(string $name)
    {
        return !is_null($this->{"_{$name}"});
    }

    /**
     * Function used to compute data based on given variable name
     * @param string $name
     * @return mixed
     * @throws ConfigException
     */
    protected function computeData(string $name)
    {
        $mapping = $this->mapping[$name];

        switch ($mapping[self::TYPE]) {
            case self::CONFIG_TYPE:
                /* Get config value */
                $data = config($mapping[self::CONFIG]);

                /* Compute the function that processes and validates data */
                $function = "processAndValidate" . ucfirst($name);

                /* Call function if defined */
                if (method_exists($this, $function)) {
                    $this->$function($data);
                }

                return $data;
            case self::PROCESS_TYPE:
                /* Compute function that computes and validates data */
                $function = "computeAndValidate" . ucfirst($name);

                if (!method_exists($this, $function)) {
                    throw new ConfigException(sprintf(
                        ConfigException::COMPUTE_FUNCTION_NOT_DEFINED,
                        $name
                    ));
                }

                $data = $this->$function();

                return $data;
                break;
            default:
                throw new ConfigException(sprintf(
                    ConfigException::INVALID_MAPPING_TYPE,
                    $mapping[self::TYPE]
                ));
        }
    }

    /**
     * Function used to retrieve aggregate config by jsonName
     * @param string $jsonName
     * @return array
     * @throws ConfigException
     */
    public function getAggregateConfigByJsonName(string $jsonName): array
    {
        $this->computeAndStoreDataIfNotExists('allAggregateData');

        $allAggregateData = $this->fetchData('allAggregateData');

        if (!array_key_exists($jsonName, $allAggregateData)) {
            throw new ConfigException(
                sprintf(
                    ConfigException::UNKNOWN_AGGREGATE_JSON_NAME,
                    $jsonName
                )
            );
        }

        return array_merge($allAggregateData[$jsonName], [
            Data::AGGREGATE_JSON_NAME => $jsonName,
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
        $this->computeAndStoreDataIfNotExists('pivotColumnsData');

        foreach ($this->fetchData('pivotColumnsData') as $pivotColumnsDatum) {
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