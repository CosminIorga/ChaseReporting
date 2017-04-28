<?php

namespace App\Jobs;

use App\Models\ResponseModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class DefaultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Variable used to store the job response
     * @var ResponseModel
     */
    protected $response;


    /**
     * Function used to set job response
     * @param bool $success
     * @param string $message
     * @param null $content
     * @return DefaultJob
     */
    public function setResponse(bool $success, string $message = '', $content = null): self
    {
        $this->response = new ResponseModel($success, $message, $content);

        return $this;
    }

    /**
     * Getter for job response
     * @return ResponseModel
     */
    public function getResponse(): ResponseModel
    {
        return $this->response;
    }
}
