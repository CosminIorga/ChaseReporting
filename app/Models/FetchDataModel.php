<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 03/07/17
 * Time: 13:03
 */

namespace App\Models;


use App\Definitions\Data;
use App\Definitions\Functions;
use App\Exceptions\FetchDataException;
use App\Jobs\FetchData;
use App\Services\ConfigGetter;
use Carbon\Carbon;

class FetchDataModel
{
    /**
     * Array used to store the information necessary to fetch the reporting data
     * @var array
     */
    protected $fetchData = [];

    /**
     * The Config Getter
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * FetchDataModel constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Function used to initialize various fields
     */
    protected function init()
    {
        $this->configGetter = ConfigGetter::Instance();
    }

    /**
     * Initialize query builder
     * @return $this
     */
    public function select()
    {
        $this->fetchData = [
            Data::FETCH_INTERVAL_START => null,
            Data::FETCH_INTERVAL_END => null,
            Data::FETCH_COLUMNS => [],
            Data::FETCH_GROUP_CLAUSE => null,
            Data::FETCH_WHERE_CLAUSE => null,
            Data::FETCH_ORDER_CLAUSE => null
        ];

        return $this;
    }

    /**
     * Function used to set a fetch column
     * @param string $column
     * @param string $function
     * @param array $extraParams
     * @return FetchDataModel
     */
    public function column(string $column, string $function, array $extraParams = []): self
    {
        $this->validateColumnName($column)
            ->validateColumnFunction($function);

        $this->fetchData[Data::FETCH_COLUMNS][$column] = [
            $function => $extraParams
        ];

        return $this;
    }

    /**
     * Function used to set the start of fetch interval
     * @param string $startDate
     * @return FetchDataModel
     */
    public function fromInterval(string $startDate): self
    {
        $this->fetchData[Data::FETCH_INTERVAL_START] = $startDate;

        $this->validateIntervalDate($startDate);

        return $this;
    }

    /**
     * Function used to set the end of fetch interval
     * @param string $endDate
     * @return FetchDataModel
     */
    public function toInterval(string $endDate): self
    {
        $this->fetchData[Data::FETCH_INTERVAL_END] = $endDate;

        $this->validateIntervalDate($endDate);

        return $this;
    }


    /**
     * Function used to set a where clause
     * @param array $whereClause
     * @return FetchDataModel
     */
    public function where(array $whereClause): self
    {
        $this->fetchData[Data::FETCH_WHERE_CLAUSE] = $whereClause;

        return $this;
    }

    /**
     * Function used to set the group by clause
     * @param array $groupByClause
     * @return FetchDataModel
     */
    public function groupBy(array $groupByClause): self
    {
        $this->fetchData[Data::FETCH_GROUP_CLAUSE] = $groupByClause;

        $this->validateGroupClause($groupByClause);

        return $this;
    }

    /**
     * Function used to set the order by clause
     * @param array $orderByClause
     * @return FetchDataModel
     */
    public function orderBy(array $orderByClause): self
    {
        $this->fetchData[Data::FETCH_ORDER_CLAUSE] = $orderByClause;

        $this->validateOrderByClause($orderByClause);

        return $this;
    }

    /**
     * Function used to execute the fetch data request
     * @return array
     */
    public function execute(): array
    {
        $this->validateAll();

        $job = (new FetchData($this->fetchData))
            ->onQueue(FetchData::QUEUE_NAME)
            ->onConnection(FetchData::CONNECTION);

        $data = dispatch($job);

        return $data;
    }

    /**
     * Function used to check if all necessary information is set before firing the FetchData job
     * @throws FetchDataException
     */
    protected function validateAll()
    {
        /* Not-null keys */
        $notNull = [
            Data::FETCH_INTERVAL_START,
            Data::FETCH_INTERVAL_END,
            Data::FETCH_GROUP_CLAUSE
        ];

        $notEmpty = [
            Data::FETCH_COLUMNS,
        ];

        foreach ($notNull as $column) {
            if (is_null($this->fetchData[$column])) {
                throw new FetchDataException(
                    sprintf(
                        FetchDataException::COLUMN_MUST_NOT_BE_NULL,
                        $column
                    )
                );
            }
        }

        foreach ($notEmpty as $column) {
            if (empty($this->fetchData[$column])) {
                throw new FetchDataException(
                    sprintf(
                        FetchDataException::COLUMN_MUST_NOT_BE_EMPTY,
                        $column
                    )
                );
            }
        }
    }


    /**
     * Function used to validate column name
     * @param string $column
     * @return FetchDataModel
     * @throws FetchDataException
     */
    protected function validateColumnName(string $column): self
    {
        $aggregateNames = array_keys($this->configGetter->aggregateData);

        /* Check if column is allowed */
        if (!in_array($column, $aggregateNames)) {
            throw new FetchDataException(
                sprintf(
                    FetchDataException::INVALID_COLUMN_VALUE,
                    $column
                )
            );
        }

        return $this;
    }

    /**
     * Function used to validate column function
     * @param string $function
     * @return FetchDataModel
     * @throws FetchDataException
     */
    protected function validateColumnFunction(string $function): self
    {
        /* Check if function is allowed */
        if (!in_array($function, Functions::ALLOWED_FUNCTIONS)) {
            throw new FetchDataException(
                sprintf(
                    FetchDataException::INVALID_FUNCTION_VALUE,
                    $function
                )
            );
        }

        return $this;
    }

    /**
     * Function used to validate an interval date
     * @param string $date
     * @return FetchDataModel
     * @throws FetchDataException
     */
    protected function validateIntervalDate(string $date): self
    {
        try {
            /* Check if date is valid. It will throw an error if it is invalid */
            new Carbon($date);

            /* Check if end date is lower than start date if both defined */
            $startDate = $this->fetchData[Data::FETCH_INTERVAL_START] ?? null;
            $endDate = $this->fetchData[Data::FETCH_INTERVAL_END] ?? null;

            /* Do not validate both dates if one is not defined */
            if (is_null($startDate) || is_null($endDate)) {
                return $this;
            }

            /* Check if end date is lower than start date */
            if ($endDate < $startDate) {
                throw new FetchDataException(
                    FetchDataException::END_DATE_LOWER_THAN_START_DATE,
                    [
                        Data::FETCH_INTERVAL_START => $startDate,
                        Data::FETCH_INTERVAL_END => $endDate
                    ]
                );
            }

            return $this;
        } catch (FetchDataException $exception) {
            throw new FetchDataException(
                $exception->getMessage(),
                $exception->getContext()
            );
        } catch (\Exception $exception) {
            throw new FetchDataException(
                FetchDataException::INVALID_INTERVAL_FORMAT,
                $date
            );
        }
    }

    /**
     * Function used to validate the group columns
     * @param array $groupByClause
     * @return FetchDataModel
     * @throws FetchDataException
     */
    protected function validateGroupClause(array $groupByClause): self
    {
        $pivotNames = array_column($this->configGetter->pivotColumnsData, Data::CONFIG_COLUMN_NAME);

        foreach ($groupByClause as $pivot) {
            if (!in_array($pivot, $pivotNames)) {
                throw new FetchDataException(
                    sprintf(
                        FetchDataException::INVALID_PIVOT_VALUE,
                        $pivot
                    )
                );
            }
        }

        return $this;
    }


    /**
     * Function used to validate the order by clause
     * @param array $orderByClause
     * @return FetchDataModel
     * @throws FetchDataException
     */
    protected function validateOrderByClause(array $orderByClause): self
    {
        $columns = array_merge(
            array_keys($this->configGetter->aggregateData),
            array_column($this->configGetter->pivotColumnsData, Data::CONFIG_COLUMN_NAME)
        );

        foreach ($orderByClause as $orderItem) {
            list($orderColumn, $orderDirection) = $orderItem;

            if (!in_array(strtolower($orderDirection), ['asc', 'desc'])) {
                throw new FetchDataException(
                    FetchDataException::INVALID_ORDER_BY_COLUMN_DIRECTION
                );
            }

            if (!in_array($orderColumn, $columns)) {
                throw new FetchDataException(
                    FetchDataException::INVALID_ORDER_BY_COLUMN_NAME
                );
            }
        }

        return $this;
    }

}