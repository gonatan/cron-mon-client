<?php

namespace Magna\Cronmon\Client;

use Exception;

/**
 * Class CronmonClient
 */
class CronmonClient
{
    /**
     * The API key is used to identify the client.
     *
     * @var string
     */
    private $_apiKey;

    /**
     * The URL the requests will be send to.
     *
     * @var string
     */
    private $_apiUrl;

    /**
     * CronmonClient constructor.
     *
     * @param string $apiKey
     * @param string $apiUrl
     */
    public function __construct(string $apiKey, string $apiUrl)
    {
        $this->_apiKey = $apiKey;
        $this->_apiUrl = $apiUrl;
    }

    /**
     * Tells the monitor that a job has started.
     *
     * @param string $jobName
     *
     * @return array
     */
    public function startJob(string $jobName)
    {
        try {
            $json = $this->_monitor('POST', ['jobName' => $jobName, 'jobStart' => time()]);
        } catch (Exception $e) {
            $json = ['code' => '500', 'text' => $e->getMessage(), 'jobId' => null];
        }

        return $json;
    }

    /**
     * Tells the monitor that a job has stopped.
     *
     * @param int $jobId
     *
     * @return array
     */
    public function stopJob(int $jobId)
    {
        try {
            $json = $this->_monitor('PATCH', ['jobId' => $jobId, 'jobStop' => time()]);
        } catch (Exception $e) {
            $json = ['code' => '500', 'text' => $e->getMessage()];
        }

        return $json;
    }

    /**
     * Tells the monitor that a job has failed.
     *
     * @param int   $jobId
     * @param array $payload
     *
     * @return array
     */
    public function failJob(int $jobId, array $payload)
    {
        try {
            $json = $this->_monitor('PATCH', ['jobId' => $jobId, 'jobFail' => time(), 'payload' => $payload]);
        } catch (Exception $e) {
            $json = ['code' => '500', 'text' => $e->getMessage()];
        }

        return $json;
    }

    /**
     * Tells the monitor something.
     *
     * @param string $method
     * @param array  $data
     *
     * @return array
     * @throws Exception
     */
    private function _monitor(string $method, array $data)
    {
        $data['apiKey'] = $this->_apiKey;

        // build options
        $options = [
            CURLOPT_URL            => $this->_apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ];
        if ('POST' === $method) {
            $options[CURLOPT_POST] = true;
        } elseif ('PATCH' === $method) {
            $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        }

        // send request
        if (false === $curl = curl_init()) {
            throw new Exception('failed to init curl');
        }
        if (false === curl_setopt_array($curl, $options)) {
            throw new Exception('failed to set curl options');
        }
        if (false === $resp = curl_exec($curl)) {
            throw new Exception('failed to execute call');
        }
        curl_close($curl);

        // handle response
        $json          = json_decode($resp, true);
        $jsonLastError = json_last_error();
        if (JSON_ERROR_NONE !== $jsonLastError) {
            throw new Exception($jsonLastError);
        }

        return $json;
    }
}