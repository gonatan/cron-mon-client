# cron-mon-client
Client to send REST requests to CronMon.

## Usage
In order to send requests to the CronMon server we first need to instantiate the client.
To do so we require the servers base URL and an API-key identifying us as authorized user of this service.

    use Magna\Cronmon\Client\CronmonClient;
    $apiKey = ''; // will be provided by the CronMon server admin
    $apiUrl = ''; // will be provided by the CronMon server admin
    $client = CronmonClient($apiKey, $apiUrl);

When starting a cronjob we should let CronMon know about it.

    $jobName  = ''; // arbitrary value which will identify the executed job
    $response = $client->startJob($jobName);

As soon as the job has ended successfully we will send another request to the server.

    $client->stopJob($response['jobId']);

In the (unlikely) case of a failure we send another request. This request should include some
additional information as payload which should help to identify the reason.
You should never send sensitive data though!

    $payload = [];
    $client->failJob($response['jobId'], $payload);
