<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StuckRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $url, $data;

    /**
     * Create a new job instance.
     *
     * @param $url
     * @param $data
     */
    public function __construct($url, $data)
    {
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->request('GET', $this->url, ['form_params' => $this->data]);

        //Due to the asynchronous handling we might setup webhooks so users can know the response of their requests
    }
}
