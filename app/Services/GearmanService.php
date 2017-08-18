<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 04/07/17
 * Time: 15:05
 */

namespace App\Services;


use App\Helpers\GearmanServiceHelper;
use App\Traits\LogHelper;

class GearmanService
{

    use LogHelper;

    /**
     * The gearman client
     * @var \GearmanClient
     */
    protected $gearmanServer = null;

    /**
     * An array used to store the data returned by gearman workers
     * @var array
     */
    protected $response = [];

    /**
     * GearmanService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Function used to initialize various variables
     */
    protected function init()
    {
        $this->setChannel('gearman');
    }

    /**
     * Function used to add a new gearman server
     */
    public function addServer()
    {
        $this->gearmanServer = new \GearmanClient();
        $this->gearmanServer->addServer();

        $this->gearmanServer->setCompleteCallback(function (\GearmanTask $task) {
            $this->response[] = GearmanServiceHelper::decodeWorkload($task->data());
        });
    }

    /**
     * Function used to add a task to the gearman server
     * @param string $fetchFunction
     * @param string $queryDataSerialized
     */
    public function addTask(string $fetchFunction, string $queryDataSerialized)
    {
        $this->gearmanServer->addTask($fetchFunction, $queryDataSerialized, 1235);
    }

    /**
     * Function used to start all queued tasks
     */
    public function runTasks()
    {
        $this->debug("Starting node execution");
        $this->gearmanServer->runTasks();
    }

    /**
     * Function used to retrieve the result of the launched gearman tasks
     * @return array
     */
    public function retrieveResponse(): array
    {
        return $this->response;
    }


}