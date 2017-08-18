<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 17/05/17
 * Time: 16:36
 */

namespace App\Jobs;


use App\Definitions\Data;
use App\Definitions\Logger;
use App\Factories\ReportingTableFactory;
use App\Models\ReportingTables\ReportingTable;
use App\Repositories\DataRepository;
use App\Repositories\RedisRepository;
use App\Services\CachingService;
use App\Services\ConfigGetter;
use App\Traits\Common;
use App\Traits\LogHelper;
use App\Traits\OutputFunctions;
use App\Transformers\TransformFetchData;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

class FetchData extends DefaultJob
{
    use OutputFunctions;
    use Common;
    use LogHelper;

    const CONNECTION = "sync";
    const QUEUE_NAME = "insertTheData";

    /**
     * An array containing necessary information used to fetch data from the reporting system
     * @var array
     */
    protected $data;

    /**
     * The Config Getter
     * @var ConfigGetter
     */
    protected $configGetter;

    /**
     * The Caching Service
     * @var CachingService
     */
    protected $cachingService;

    /**
     * The Data Repository
     * @var DataRepository
     */
    protected $dataRepository;

    /**
     * The Redis Repository
     * @var RedisRepository
     */
    protected $redisRepository;

    /**
     * InsertData constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->init($data);
    }

    /**
     * Function used to initialize various fields
     * @param array $data
     */
    protected function init(array $data)
    {
        $this->data = $data;
        $this->configGetter = ConfigGetter::Instance();
        $this->cachingService = CachingService::Instance();

        $this->setChannel(Logger::FETCH_DATA_CHANNEL);
    }

    /**
     * Job runner
     * @param DataRepository $dataRepository
     * @param RedisRepository $redisRepository
     * @return array
     */
    public function handle(
        DataRepository $dataRepository,
        RedisRepository $redisRepository
    ): array {
        $this->debug("Fetching data started ...");

        /* Start timer for performance benchmarks */
        $startTime = microtime(true);

        $this->dataRepository = $dataRepository;
        $this->redisRepository = $redisRepository;

        /* Compute cache key */
        $cacheKey = $this->cachingService->computeCacheKeyFromFetchData($this->data);

        /* Check if cache key exists */
        $cachedData = $this->redisRepository->get($cacheKey);

        if (!is_null($cachedData) && !env('APP_NO_CACHE')) {
            $this->debug("Cache hit");
            $results = $this->cachingService->decodeCacheData($cachedData);
        } else {
            $this->debug("Cache miss");
            /* Compute necessary tables and interval columns */
            $tablesAndIntervals = $this->fetchTablesAndIntervals();

            /* Transform received data, tables and columns into queryData */
            $queryData = $this->transformData($tablesAndIntervals);

            /* Retrieve results using given queryData */
            $results = $this->fetchData($queryData);

            /* Cache the results */
            $encodedData = $this->cachingService->encodeCacheData($results);

            $this->redisRepository->set($cacheKey, $encodedData);
        }

        /* Compute total operations time */
        $endTime = microtime(true);
        $elapsed = $endTime - $startTime;

        $this->debug("Fetch operation time: $elapsed seconds");

        return $results;
    }

    /**
     * Function used to retrieve a list of tables and associated intervals from which to fetch the data
     */
    protected function fetchTablesAndIntervals()
    {
        /* Get data interval from config */
        $dataInterval = $this->configGetter->dataInterval;

        /* Compute necessary information to retrieve periods between startDate and endDate with a step of dataInterval */
        $startDate = new Carbon($this->data[Data::INTERVAL_START]);
        $endDate = new Carbon($this->data[Data::INTERVAL_END]);
        $dateInterval = new DateInterval("PT{$dataInterval}M");

        /* Compute periods */
        $periods = new DatePeriod($startDate, $dateInterval, $endDate);

        /* Compute reporting table model */
        $reportingTableModel = $this->getReportingTableModel($startDate);

        $tablesAndIntervals = [];

        foreach ($periods as $referenceDate) {
            $reportingTableModel->setReferenceDate($referenceDate);
            $tableName = $reportingTableModel->getTableName();

            $intervalColumn = $reportingTableModel->getIntervalColumnByReferenceData($referenceDate);

            $tablesAndIntervals[$tableName][] = $intervalColumn;
        }

        return $tablesAndIntervals;
    }

    /**
     * Function used to compute reportingTableModel based on given reference date
     * @param Carbon $referenceDate
     * @return ReportingTable
     */
    protected function getReportingTableModel(Carbon $referenceDate): ReportingTable
    {
        /* Get config tableInterval */
        $tableInterval = $this->configGetter->tableInterval;
        /* Get config dataInterval */
        $dataInterval = $this->configGetter->dataInterval;

        /* Get class that handles the current tableInterval */
        $reportingTableModel = ReportingTableFactory::build($tableInterval);
        $reportingTableModel->init($referenceDate, $dataInterval);

        return $reportingTableModel;
    }

    /**
     * Function used to transform data
     * @param array $tablesAndIntervals
     * @return array
     */
    protected function transformData(array $tablesAndIntervals): array
    {
        $queryData = (new TransformFetchData())->toReportingData($this->data, $tablesAndIntervals);

        return $queryData;
    }

    /**
     * Function used to fetch data
     * @param array $queryData
     * @return array
     */
    protected function fetchData(array $queryData): array
    {
        /* Start timer for performance benchmarks */
        $startTime = microtime(true);

        $results = $this->dataRepository->fetchData($queryData);

        /* Compute total operations time */
        $endTime = microtime(true);
        $elapsed = $endTime - $startTime;

        $this->debug("Fetched data in $elapsed seconds");

        return $results->toArray();
    }
}