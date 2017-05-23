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
use App\Exceptions\ConfigException;
use App\Exceptions\ServiceException;

/**
 * Class ConfigGetter
 * @package App\Services
 * @property string $tableInterval
 * @property string $dataInterval
 * @property array $primaryColumnData
 * @property array $pivotColumnsData
 * @property array $intervalColumnData
 * @property array $timestampData
 * @property array $aggregateData
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
    ];

    /**
     * Instantiator for
     * @return ConfigGetter|null
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
    protected function validateTableInterval(string $tableInterval)
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
    protected function validateDataInterval(int $dataInterval)
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
    protected function validatePrimaryColumnData(array &$primaryColumnData)
    {
        $this->validateColumnData($primaryColumnData);
    }

    /**
     * Function used to validate pivot columns data
     * @param array $pivotColumnsData
     */
    protected function validatePivotColumnsData(array &$pivotColumnsData)
    {
        foreach ($pivotColumnsData as &$pivotColumnData) {
            $this->validateColumnData($pivotColumnData);
        }
    }

    /**
     * Function used to validate interval column data
     * @param array $intervalColumnData
     */
    protected function validateIntervalColumnData(array &$intervalColumnData)
    {
        $this->validateColumnData($intervalColumnData);
    }

    /**
     * Function used to validate timestamp column data
     * @param array $timestampData
     */
    protected function validateTimestampData(array &$timestampData)
    {
        $this->validateColumnData($timestampData);
    }

    /**
     * Function used to validate generic column data
     * @param array $columnData
     * @throws ConfigException
     */
    protected function validateColumnData(array &$columnData)
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
                    )
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
        //TODO: validate data_type value

        /* Validate index value */
        //TODO: validate index value
    }

    /**
     * Function used to validate aggregate config data
     * @param array $aggregateData
     * @throws ConfigException
     */
    protected function validateAggregateData(array &$aggregateData)
    {
        foreach ($aggregateData as &$aggregateRecord) {
            $requiredKeys = [
                Data::AGGREGATE_NAME,
                Data::AGGREGATE_JSON_NAME,
                Data::AGGREGATE_FUNCTION
            ];

            /* Values assumed if key not found */
            $defaultValues = [
                Data::AGGREGATE_EXTRA => []
            ];

            /* Validate required data */
            foreach ($requiredKeys as $requiredKey) {
                if (!array_key_exists($requiredKey, $aggregateRecord)) {
                    throw new ConfigException(
                        sprintf(
                            ConfigException::AGGREGATE_DATA_INCOMPLETE,
                            $requiredKey
                        )
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
        $function = "validate" . ucfirst($name);

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

        foreach ($this->_aggregateData as $aggregateRecord) {
            if ($aggregateRecord[Data::AGGREGATE_JSON_NAME] == $jsonName) {
                return $aggregateRecord;
            }
        }

        throw new ConfigException(
            sprintf(
                ConfigException::UNKNOWN_AGGREGATE_JSON_NAME,
                $jsonName
            )
        );
    }

}