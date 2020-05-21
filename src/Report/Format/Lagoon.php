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
        if ($this->endpointUrl) {
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
