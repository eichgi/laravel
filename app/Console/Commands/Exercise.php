<?php

namespace App\Console\Commands;

use App\Jobs\StuckRequests;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Http\Traits\Notifiable;

class Exercise extends Command
{
    use Notifiable;

    /**
     * I think this could be a possible solution for step 4 & 5.
     *
     * @var string
     */
    protected $signature = 'exercise';

    /**
     * This is the exercise command
     *
     * @var string
     */
    protected $description = 'Interview Exercise Command';

    protected $url = 'https://atomic.incfile.com/fakepost';
    protected $data = [
        'field_name' => 'abc',
        'other_field' => '123',
    ];
    protected $secondsToTryAgain = 10;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();

        try {
            if (!Redis::get('SERVICE_DOWN')) {
                $response = $client->request('GET', $this->url, ['form_params' => $this->data]);

                // Successful response
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    return $response->getBody();
                }
            } else {
                dispatch((new StuckRequests($this->url, $this->data))->onQueue('stucked_requests'));
            }
        } catch (\Exception $exception) {
            // If the service is unavailable we reduce the number of requests, notify the admin then save sent requests for further process
            Redis::set('SERVICE_DOWN', 1, 'EX', $this->secondsToTryAgain);
            $this->notifyToAdmin($exception->getMessage());
            app('log')->info($exception->getMessage());

            if ($exception->getCode() >= 500) {
                //This queue should be triggered once the service is up and running again
                dispatch((new StuckRequests($this->url, $this->data))->onQueue('stucked_requests'));
            }
        }

        $this->info("Your request has been received");
        //return response()->json(['status' => 'Your request has been received'], 200);
    }
}
