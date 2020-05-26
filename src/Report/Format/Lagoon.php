<?php

namespace Drutiny\Amazee\Report\Format;

use Drutiny\Profile;
use Drutiny\Report\Format;
use Drutiny\Target\Target;
use Drutiny\Report\Format\JSON;
use Drutiny\Assessment;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

class Lagoon extends JSON
{

    /**
     * The output location or method.
     *
     * @var string
     */
    protected $output;

    protected $endpointUrl = "";

    protected const LAGOON_VARS = [
      'LAGOON_SAFE_PROJECT',
      'LAGOON_PROJECT',
      'LAGOON_ENVIRONMENT',
      'LAGOON_GIT_BRANCH',
      'LAGOON_DRUTINY_PROJECT_NAME',
      'LAGOON_DRUTINY_ENVIRONMENT_NAME',
    ];

    protected $lagoonInfo = [];

    public function __construct($options)
    {
        parent::__construct($options);
        $this->setFormat('lagoon');
        $this->determineEndpointUrl();

        foreach (self::LAGOON_VARS as $varName) {
            if ($val = getenv($varName)) {
                $this->lagoonInfo[$varName] = $val;
            } else {
                $this->lagoonInfo[$varName] = "UNSET";
            }
        }

        if(empty($this->lagoonInfo['LAGOON_DRUTINY_PROJECT_NAME']) || $this->lagoonInfo['LAGOON_DRUTINY_PROJECT_NAME'] == "UNSET") {
            $this->lagoonInfo['LAGOON_DRUTINY_PROJECT_NAME'] = !empty($this->lagoonInfo['LAGOON_PROJECT']) ? $this->lagoonInfo['LAGOON_PROJECT'] : $this->lagoonInfo['LAGOON_SAFE_PROJECT'];
        }

        if(empty($this->lagoonInfo['LAGOON_DRUTINY_ENVIRONMENT_NAME']) || $this->lagoonInfo['LAGOON_DRUTINY_ENVIRONMENT_NAME'] == "UNSET") {
            $this->lagoonInfo['LAGOON_DRUTINY_ENVIRONMENT_NAME'] = $this->lagoonInfo['LAGOON_GIT_BRANCH'];
        }

    }


    protected function determineEndpointUrl()
    {
        $this->endpointUrl = null;
        if ($endpoint = getenv('LAGOON_DRUTINY_WEBHOOK_URL')) {
            $this->endpointUrl = $endpoint;
        }
    }

    protected function preprocessResult(
      Profile $profile,
      Target $target,
      Assessment $assessment
    ) {
        $schema = parent::preprocessResult($profile, $target, $assessment);
        $schema['lagoonInfo'] = $this->lagoonInfo;
        return $schema;
    }

    protected function sendToLagoon($variables)
    {
        if (!empty($this->endpointUrl)) {
            $client = new Client();
            $res = $client->request('POST', $this->endpointUrl, [
              'json' => $variables,
            ]);
        }
    }

    protected function renderResult(array $variables)
    {
        $this->sendToLagoon($variables);
        return json_encode($variables);
    }

    protected function renderMultiResult(array $variables)
    {
        $this->sendToLagoon($variables);
        return $this->renderResult($variables);
    }
}

?>
